import nltk
from nltk.stem.wordnet import WordNetLemmatizer
from nltk.corpus import twitter_samples, stopwords
from nltk.tokenize import word_tokenize
from nltk import FreqDist, classify, NaiveBayesClassifier
from nltk.tag import StanfordPOSTagger
from nltk.tag import pos_tag
import numpy as np
import re, string, random, json, requests, sys, os, hashlib, time, datetime
from collections import OrderedDict 
from operator import getitem
import mysql.connector
from xml.etree import ElementTree

class NetBuilder:
	stop_words = set(stopwords.words('english')) 
	negation = None
	conjunction = None
	bioterms = None
	net = None
	results = None
	classifier = None;
	webserver_url = "http://netme.atlas.dmi.unict.it/tagme_string";
	abstracts = {}
	path = "/var/www/html/netme/py/";
	tagger = None
	cleaned_text = ''
	searchid = None
	debug = False
	terms = None
	max_items = None
	retmax = None
	articles_id = []
	
	def __init__(self):	
		#tagme.GCUBE_TOKEN  =  "a51fcdf6-da3f-4964-b2b6-4fdf861f0df5-843339462"
		#init_sentiment_classifier()
		if(len(sys.argv) < 7):
			print('')
			exit()
		self.db_connect()
		self.rho = float(sys.argv[1])
		self.max_items = int(sys.argv[2])
		self.retmax = int(sys.argv[3])
		self.sort = sys.argv[4]
		self.searchid = sys.argv[5]
		self.terms = sys.argv[6]
		try:
			if sys.argv[7]: self.debug = True 
		except IndexError:
			self.debug = False

		self.bioterms = self.get_bioterms()
		self.conjunction = self.get_conjunctionterms()
		self.negation = self.get_negationterms()
		self.reset_log()
		self.write_log("Starting parsing operation\n")
		if len(self.terms) > 0:
			self.terms_search()
		else:
			filepath = self.path + "abstracts/" + self.searchid
			content = open(filepath, "r")
			self.abstracts = content.read()
			self.abstracts = json.loads(self.abstracts)
		#os.remove(filepath)
		if (self.abstracts) :
			self.make_net()
		else:
			print('')

				
	def make_net(self):
		self.net = {'edges': [], 'nodes': []}
		self.results = {}
		self.analyze_abstracts()
		
	################# PMC SEARCH #################
	
	def terms_search(self):
		try:
			count = 0
			id_list = []
			url_search  = "https://eutils.ncbi.nlm.nih.gov/entrez/eutils/esearch.fcgi?db=pmc&term="+self.terms+"+AND+free+fulltext[filter]&retmax="+str(self.retmax*4)+"&sort="+self.sort
			r = requests.get(url = url_search, params = '') 
			r = ElementTree.fromstring(r.content)
			for id_list_tag in r.findall('IdList'):
				for id_article_tag in id_list_tag.findall('Id'):
					id_list.append(id_article_tag.text)
			
			url_fetch  = "https://eutils.ncbi.nlm.nih.gov/entrez/eutils/efetch.fcgi"
			par = {
				'db': 'pmc',
				'id': ','.join(id_list),
				'retmode' : 'xml',
			}
			r = requests.post(url = url_fetch, params = par) 
			r = ElementTree.fromstring(r.content)	
			for article_tag in r.findall('article'):
				for body_tag in article_tag.findall('body'):
					content = ''						
					for p_tag in body_tag.iter('p'):
						if p_tag.text:
							content+= p_tag.text
					article_id = article_tag.find(".//article-id[@pub-id-type='pmc']")
					if article_id.text:
						self.abstracts[article_id.text] = content
						self.articles_id.append(article_id.text)
						count+=1
						if count >= self.retmax: 
							return
			return
		except Exception as e:
			print(e)
			return
	
	################# ABSTRACTS ANALYSIS #################
	
	def analyze_abstracts(self):
		text = ''
		for index, id_abstract in enumerate(self.abstracts):
			self.write_log("Annotating "+str(index+1)+" of "+str(len(self.abstracts))+" articles\n")
			self.request_data(self.abstracts[id_abstract])
			text+= self.abstracts[id_abstract]
		self.count_spot()
		self.write_dump(json.dumps(self.results))
		self.results = OrderedDict(sorted(self.results.items(), key = lambda x: (getitem(x[1], 'count'), getitem(x[1], 'rho')), reverse=True)) 
		self.results = {k:v for (k,v) in [x for x in self.results.items()][:self.max_items]}
		tokens = word_tokenize(text)
		self.write_log("Starting tokenization process ...\n")
		edges = self.find_edges(tokens)
		i = 1
		for r1 in self.results:
			for r2 in self.results:
				i+=1
				self.write_log("Analyzing "+str(i)+" of "+str(len(self.results)*len(self.results))+" gene combination\n")
				if(r1 != r2 and self.results[r1]['Word'] != self.results[r2]['Word']):			
					pattern = rf'(?=\b{r1}\b(.*?)\b{r2}\b)'
					matches = re.finditer(pattern, self.cleaned_text, re.IGNORECASE)
					words = [match.group(1) for match in matches]
					for word in words:
						period_check = re.search(r"\.", word)
						if not period_check:
							tokens = word_tokenize(word)
							if len(tokens) > 0:
								for t in tokens:
									if t not in self.results and t not in self.negation:
										t = self.clean_token(t)
										if(t):
											self.save_edge(r1, r2, t, 1/len(tokens))
		for e in range(len(self.net['edges'])):
			bio = self.checkBioEdge(self.net['edges'][e]['data']['label'].replace("not ", ""))
			self.net['edges'][e]['data']['bio'] = bio
		self.reset_log()
		print(json.dumps({'results':  self.results, 'net': self.net['nodes']+self.net['edges'], 'articles': self.articles_id}))
		self.write_dump(json.dumps({'results':  self.results, 'net': self.net['nodes']+self.net['edges'], 'articles': self.articles_id}))
		
	def count_spot(self):
		for r in self.results:
			self.results[r]['count'] = 0
			self.results[r]['articles'] = []
			for id_abstract in self.abstracts:
				pattern = rf'(?=[\W*?]{r}[\W*?])'
				matches = re.findall(pattern, self.abstracts[id_abstract])
				if(len(matches) > 0):
					self.results[r]['count']+= len(matches)
					self.results[r]['articles'].append(id_abstract)
	
	################# ANNOTATION ########################
	
	def request_data(self, name):
		# self.results = {'pten': {'wid': '3001606', 'spot': 'PTEN', 'rho': '0.68882173', 'Word': 'PTEN', 'categories': ['gene', 'proteine']}, 'dmi': {'wid':  '372611', 'spot': 'DMI', 'rho':  '0.5', 'Word': 'Desipramine', 'categories': ['drug']}, 'src': {'wid': '3002148', 'spot': 'SRC', 'rho': '0.60260934', 'Word': 'SRC', 'categories': ['disease']}, 'icd': {'wid': '3021135', 'spot': 'ICD', 'rho': '0.25', 'Word': 'icd', 'categories': ['proteine']}, 'rpe': {'wid': '3002757', 'spot': 'RPE', 'rho': '0.58621234', 'Word': 'RPE', 'categories': ['gene']}}
		# return 0
		names = self.split_abstract(name)
		i = 0
		for n in names:
			par = {'name': n}
			i+=1
			try:
				r = requests.post(url = self.webserver_url, params = par) 
				data = r.json() 
				#response {Word: GOword, rho, spot: word in text, wid}
				if data['response']:
					#print("data", data)
					self.save_results(data['response'])
			except:
				data = None
		return 0

	def split_abstract(self, name):
		names = []
		i = 0
		names.append('')
		for sentence in name.split(". "):
			if len(names[i]) < 5000:
				names[i] = names[i] + sentence
			else:
				i = i+1
				names.append(sentence)
		return names
		
	def save_results(self, data):
		for kd, vd in enumerate(data):
			if vd['spot'] in self.results: #set rho to new value if greater than old
				if (float(vd['rho']) > float(self.results[vd['spot']]['rho']) and float(vd['rho']) > float(self.rho)):
					self.results[vd['spot']]['rho'] = vd['rho']
			elif float(vd['rho']) > float(self.rho):
				self.results[vd['spot']] = vd
				
	def clean_token(self, token):
		token = token.replace('NOT_', 'not ')
		not_alpha = re.findall(r'[^a-zA-Z\d\s:]', token)
		if len(not_alpha) > 0 or len(token) < 3:
			return False
		else:
			return token
	
	############## EDGES ANALYSIS ##################
	
	def find_edges(self, tokens):
		cleaned_tokens = {}
		negation = False
		pos_list =  pos_tag(tokens)
		lemmatizer = WordNetLemmatizer()
		for token, tag in pos_list:
			
			if token in self.negation:
				negation = True
			else:
				token = re.sub('http[s]?://(?:[a-zA-Z]|[0-9]|[$-_@.&+#]|[!*\(\),]|'\
							    '(?:%[0-9a-fA-F][0-9a-fA-F]))+','', token)
				token = re.sub("(@[A-Za-z0-9_]+)","", token)
				token = re.sub("[\[\]]","", token)
				pos = ''
				if token in self.results or token == '.':
					self.cleaned_text+= token+" ";
				if tag.startswith('VB'):
					pos = 'v'				
				if pos != '':	
					lemma = lemmatizer.lemmatize(token, pos)
					if negation:
						lemma = 'NOT_'+lemma
						negation = False
					if len(lemma) > 0 and ((lemma not in string.punctuation and lemma.lower() not in  self.stop_words) or (lemma.lower() in self.negation)) and lemma not in self.results:
						#cleaned_tokens.append(token.lower())		
						cleaned_tokens[token] = lemma
						self.cleaned_text+= lemma+" ";
		return cleaned_tokens
	

	def checkBioEdge(self, edge):
		score = len(edge);
		for e in self.bioterms:
			if nltk.edit_distance(e, edge) < score:
				score = nltk.edit_distance(e, edge)
		try:
			normalized_score = score/len(edge)
		except:
			normalized_score = 999
		return normalized_score

	
	def save_edge(self, spot1, spot2, edge_label, weight):
		foundn1 = False
		foundn2 = False
		n1 = self.results[spot1]['Word']
		n2 = self.results[spot2]['Word']
		index_e = -1
		edge_id = hashlib.md5((n1+n2+edge_label).encode('utf-8')).hexdigest()
		#edge
		for index, edge in enumerate(self.net['edges']):
			if (edge['data']["source"] == n1 and edge['data']["target"] == n2 and edge['data']['label'] == edge_label) :
				index_e = index		
		bio = 0
		new_edge = {"id": edge_id, "source": n1, "target": n2, "label": edge_label, "weight": weight, "bio": bio}
		if (index_e > 0 and weight > self.net['edges'][index_e]['data']['weight']): #edge found with lowest weight update weight
			self.net['edges'][index_e] = {'data': new_edge}
		if index_e == -1: #edge not found ,new insert
			self.net['edges'].append({'data': new_edge})
		
		# Node
		for node in self.net['nodes']:
			if (node['data']['label'] == n1):
				foundn1 = True
			if (node['data']['label'] == n2):
				foundn2 = True
		if not foundn1:
			self.net['nodes'].append({'data': {"id": n1, "label": n1, "size": 10, "spot": spot1}})
		if not foundn2:
			self.net['nodes'].append({'data': {"id": n2, "label": n2, "size": 10, "spot": spot2}})
		
	############## DATABASE ##################
			
	def db_connect(self):
		self.db = mysql.connector.connect(host="localhost", user="root", password="tagmetagme85", database="netme", buffered=True, auth_plugin='mysql_native_password')	
	
	def get_conjunctionterms(self):
		sql = "SELECT * FROM netme.conjunctionterms"
		terms = []
		try:
			cursor = self.db.cursor(dictionary=True)
			cursor.execute(sql)
			res = cursor.fetchall()
			for r in res:
				terms.append(r['term']) 
		except Exception as e:
			print(e)
		return terms
	
	def get_negationterms(self):
		sql = "SELECT * FROM netme.negationterms"
		terms = []
		try:
			cursor = self.db.cursor(dictionary=True)
			cursor.execute(sql)
			res = cursor.fetchall()
			for r in res:
				terms.append(r['term']) 
		except Exception as e:
			print(e)
		return terms
		
	def get_bioterms(self):
		sql = "SELECT * FROM netme.bioterms"
		terms = []
		try:
			cursor = self.db.cursor(dictionary=True)
			cursor.execute(sql)
			res = cursor.fetchall()
			for r in res:
				terms.append(r['term']) 
		except Exception as e:
			print(e)
		return terms
	
	############## LOG ##################
		
	def reset_log(self):
		logfile_path = self.path+"logs/"+self.searchid
		if(os.path.exists(logfile_path)):
			os.remove(logfile_path)
		
	def write_log(self, text):
		logfile_path = self.path+"logs/"+self.searchid
		file = open(logfile_path, 'a') 
		file.write(text) 
		file.close() 
		time = datetime.datetime.now().strftime("%Y-%m-%d %H:%M:%S")
		text = time + ":\t\t" + text + "\n"
		if(self.debug):
			print(text)
		
	def write_dump(self, text):
		dumpfile_path = self.path+"dumps/"+self.searchid
		file = open(dumpfile_path, 'w') 
		file.write(text) 
		file.close() 
		
n = NetBuilder();
