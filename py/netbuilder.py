import warnings
warnings.simplefilter(action='ignore', category=FutureWarning)
import treetaggerwrapper
import nltk
from nltk.corpus import stopwords
from nltk.tokenize import word_tokenize
import re, regex, string, random, json, requests, sys, os, hashlib, time, datetime
from collections import OrderedDict 
from operator import itemgetter
import mysql.connector
from xml.etree import ElementTree
import fitz
from scipy import spatial
import math 
import nltk.data
import spacy
from spacy.symbols import VERB
from spacy.matcher import Matcher
from   lxml        import etree
from   itertools   import chain


class NetBuilder:
	stop_words = set(stopwords.words('english')) 
	sent_detector = nltk.data.load('tokenizers/punkt/english.pickle')
	nlp = spacy.load("en_core_web_sm")
	nlp.add_pipe("merge_entities")
	nlp.add_pipe("merge_noun_chunks")
	#nlp.add_pipe(nlp.create_pipe("merge_entities"))
	#nlp.add_pipe(nlp.create_pipe("merge_noun_chunks"))
	negation = None
	conjunction = None
	bioterms = None
	net = None
	annotations = None
	classifier = None;
	webserver_url = "http://131.114.50.197/tagme_string";
	#webserver_url = "http://161.97.160.81/tagme_string";
	articles = {}
	path = "/var/www/html/netme/py/";
	tagger = None
	cleaned_text = {}
	searchid = None
	debug = False
	post_data = None
	tagger = None
	articles_id = []
	apikey = "9fa42ec62c582485fb7e6c69148eaf940308"
	
	def __init__(self):	
		if(len(sys.argv) < 1):
			print('')
			exit()
		self.db_connect()
		self.tagger = treetaggerwrapper.TreeTagger(TAGLANG='en')
		self.searchid = sys.argv[1]
		## get searchid, data information are stored in request table
		try:
			if sys.argv[2]: self.debug = True 
		except IndexError:
			self.debug = False

		self.bioterms = self.get_bioterms()
		self.conjunction = self.get_conjunctionterms()
		self.negation = self.get_negationterms()
		self.reset_log()
		self.write_log("Starting parsing operation")
		
		#GET POST_DATA
		self.post_data = self.get_request()
		#self.post_data = json.loads(self.post_data['data'])
		
		print(self.post_data)
		
		if self.post_data:
			#FREETEXT
			if self.post_data['freetext'] is not None and self.post_data['freetext']:
				self.articles = json.loads('{"freetext": "'+self.json_string(self.post_data['freetext'])+'"}')
				self.articles_id.append("freetext")
			
			#PMC
			if self.post_data['pmc_terms'] is not None and len(self.post_data['pmc_terms']) > 0:
				#split query terms by ;
				query = self.post_data['pmc_terms'].split(";")
				for q in query:
					self.articles_fetch(self.terms_search(q, "pmc"), "pmc")
				
			#PMC IDLIST
			if self.post_data['pmc_id'] is not None and len(self.post_data['pmc_id'].split(",")) > 0:
				self.post_data['pmc_retmax'] = len(self.post_data['pmc_id'].split(","))
				self.articles_fetch(self.post_data['pmc_id'].split(","), "pmc")
			
			#PM
			if self.post_data['pubmed_terms'] is not None and len(self.post_data['pubmed_terms']) > 0:
				#split query terms by ;
				query = self.post_data['pubmed_terms'].split(";")
				for q in query:
					self.articles_fetch(self.terms_search(q, "pubmed"), "pubmed")
				
			#PM IDLIST
			if self.post_data['pubmed_id'] is not None and len(self.post_data['pubmed_id'].split(",")) > 0:
				self.post_data['pubmed_retmax'] = len(self.post_data['pubmed_id'].split(","))
				self.articles_fetch(self.post_data['pubmed_id'].split(","), "pubmed")
			
			#PDF
			pdf_path = self.path+"pdf/"
			for filename in os.listdir(pdf_path):
				if filename.startswith(self.searchid):
					self.articles["pdf|"+filename.replace(self.searchid+'_', '')] = self.parse_pdf(pdf_path+filename)
					self.articles_id.append("pdf|"+filename.replace(self.searchid+'_', ''))
			
			# self.articles array contains articles fulltext, self.articles_id contains id list (format {ORIGIN_TYPE}|{id} example pdf|5.pdf, pmc|142341234, etc)
			
			if (self.articles) :
				self.net = {'edges': [], 'nodes': []}
				self.annotations = {'article': {}, 'spot_list':{}, 'word_list':{}}
				self.make_net()
			else:
				print('')
		else:
			print('')

		
	################# PMC SEARCH #################
	
	def terms_search(self, terms, dbtype = "pmc"):
		try:
			idlist = []
			terms = terms+"+AND+free+fulltext[filter]" if (dbtype == "pmc") else terms
			retmax = int(self.post_data[dbtype+'_retmax'])
			sort = self.post_data[dbtype+'_sort']
			url_search  = "https://eutils.ncbi.nlm.nih.gov/entrez/eutils/esearch.fcgi?apikey="+self.apikey+"&db="+dbtype+"&term="+terms+"&retmax="+str(retmax*4)+"&sort="+sort
			r = requests.get(url = url_search, params = '') 
			r = ElementTree.fromstring(r.content)
			for id_list_tag in r.findall('IdList'):
				for id_article_tag in id_list_tag.findall('Id'):
					idlist.append(id_article_tag.text)
			return idlist
		except Exception as e:
			print(e)
			self.write_log("Error in terms_search: "+str(e))
			return ''
			
	def articles_fetch_old(self, idlist, dbtype = "pmc"):
		try:
			count = 0
			url_fetch  = "https://eutils.ncbi.nlm.nih.gov/entrez/eutils/efetch.fcgi"
			key = dbtype+'_obj'
			retmax = int(self.post_data[dbtype+'_retmax'])
			n_cycle = (len(idlist) // 100) + 1
			for n in range(n_cycle):
				_idlist = ','.join(idlist[(n*100):((n+1)*100)])
				par = {
					'db': dbtype,
					'id': _idlist,
					'retmode' : 'xml',
					'apikey' : self.apikey,
				}
				r = requests.post(url = url_fetch, params = par) 
				r = ElementTree.fromstring(r.content)
				
				
				if(dbtype == "pmc"):
					for article_tag in r.findall('article'):
						for body_tag in article_tag.findall('body'):
							content = ''						
							for p_tag in body_tag.iter('p'):
								if p_tag.text:
									content+= p_tag.text
							article_id = article_tag.find(".//article-id[@pub-id-type='pmc']")
							if article_id.text and len(content) > 100 and "pmc|"+article_id.text not in self.articles_id:
								self.articles["pmc|"+article_id.text] = content.replace('\n', '')
								self.articles_id.append("pmc|"+article_id.text)
								count+=1
								if count >= retmax:
									return
				else:
					for article_tag in r.findall('PubmedArticle'):
						content = ''
						for abstract_tag in article_tag.findall('.//AbstractText'):
							if abstract_tag.text:
								content+= abstract_tag.text
						article_id = article_tag.find(".//PMID")
						if article_id.text and len(content) > 100 and "pubmed|"+article_id.text not in self.articles_id:
							self.articles["pubmed|"+article_id.text] = content.replace('\n', '')
							self.articles_id.append("pubmed|"+article_id.text)
							count+=1
							if count >= retmax: 
								return
			return
		except Exception as e:
			print(e)
			self.write_log("Error in articles_fetch: "+str(e))
			return
	
	def stringify_children(self, node):
		"""
		Filters and removes possible Nones in texts and tails
		"""
		if node is not None:
			parts = (
				[node.text]
				+ list(chain(*([c.text, c.tail] for c in node.getchildren())))
				+ [node.tail]
			)
			return "".join(filter(None, parts))
		return ""
	
	def pubmed_parser(self, response):
		for article_tag in response.findall('PubmedArticle'):
			article_id = article_tag.find(".//PMID")
			if not article_id.text: continue
			content = ''
			for abstract_tag in article_tag.findall('.//AbstractText'):
				if abstract_tag.text:
					content += abstract_tag.text
			if not (len(content) > 100 and article_id.text not in self.articles) : continue
			self.articles[article_id.text] = content.replace('\n', '')
			self.articles_id.append("pubmed|"+article_id.text)

	def pubmed_central_parser(self, response):
		for article_tag in response.findall('article'):
			article_meta = article_tag.find(".//article-meta")
			article_id   = article_meta.find('article-id[@pub-id-type="pmc"]')
			if not article_id.text: continue
			content = self.parse_pubmed_paragraph(article_tag)
			if not (len(content) > 100 and article_id.text not in self.articles): continue
			self.articles[article_id.text] = content.replace('\n', '')
			self.articles_id.append("pmc|"+article_id.text)
	
	def parse_pubmed_paragraph(self, article_tag):
		dict_pars = list()
		for paragraph in article_tag.findall(".//body//p"):
			paragraph_text = self.stringify_children(paragraph)
			if paragraph_text != '':
				dict_pars.append(paragraph_text)

		txt = "".join(dict_pars)
		txt = re.sub('\\s+', ' ', txt)
		return txt
	
	def sub_list(self, idlist, n, retmax, sup, len_id_list):
		return idlist[n * retmax: sup] if sup < len_id_list else idlist[n * retmax:]

	def articles_fetch_OLD2(self, idlist, apikey, retmax=20, dbtype="pmc"):
		#self.articles = dict()
		try:
			url_fetch = "https://eutils.ncbi.nlm.nih.gov/entrez/eutils/efetch.fcgi"
			len_id_list = len(idlist)
			for n in range((len_id_list // retmax) + 1):
				sup = (n + 1) * retmax
				par = {
					'db'     : dbtype,
					'id'     : ','.join(self.sub_list(idlist, n, retmax, sup, len_id_list)),
					'retmode': 'xml',
					'apikey' : apikey,
				}
				r = requests.post(url=url_fetch, params=par)
				if dbtype == "pmc":
					r = etree.fromstring(r.content,  parser=etree.XMLParser(huge_tree=True))
				else:
					r = ElementTree.fromstring(r.content)
				self.pubmed_central_parser(r) if dbtype == "pmc" else self.pubmed_parser(r)
		except Exception as e:
			print(e)
			self.write_log("Error in articles_fetch: "+str(e))
			return None
	
	def articles_fetch(self, idlist, dbtype="pmc", retmax=100):
		try:
			url_fetch = "https://eutils.ncbi.nlm.nih.gov/entrez/eutils/efetch.fcgi"
			len_id_list = len(idlist)
			for n in range((len_id_list // retmax) + 1):
				sup = (n + 1) * retmax
				par = {
					'db'     : dbtype,
					'id'     : ','.join(self.sub_list(idlist, n, retmax, sup, len_id_list)),
					'retmode': 'xml',
					'apikey' : self.apikey,
				}
				r = requests.post(url=url_fetch, params=par)
				if dbtype == "pmc":
					r = etree.fromstring(r.content,  parser=etree.XMLParser(huge_tree=True))
					self.pubmed_central_parser(r)
				else:
					r = ElementTree.fromstring(r.content)
					self.pubmed_parser(r)
		except Exception as e:
			print(e)
			self.write_log("Error in articles_fetch: "+str(e))
			return None
			
	################# PDF PARSER #################
	
	def parse_pdf(self, file):
		try:
			article = '';
			doc        = fitz.open(file)
			page_count = doc.pageCount
			metadata   = doc.metadata
			
			# page creation
			pages_blocks  = dict()
			duplicate_blk = dict()
			for page in range(0, page_count):
				page_i = doc.loadPage(page)
				for block in page_i.getText("blocks"):
					block_coords = block[0:4]
					if block_coords not in pages_blocks:
						pages_blocks[block_coords] = dict()
						pages_blocks[block_coords][page] = block[4:6]
					else:
						if block_coords not in duplicate_blk:
							duplicate_blk[block_coords] = {"count": 0, "testo": block[4:6]}
						duplicate_blk[block_coords]["count"] += 1

			for key in duplicate_blk:
				if duplicate_blk[key]["count"] > 2:
					pages_blocks.pop(key)

			for block in pages_blocks:
				for text in pages_blocks[block]:
					#article+= pages_blocks[block][text][0].replace('\n', ' ')
					article+= pages_blocks[block][text][0].replace('\n', ' ').strip().replace('  ', ' ')+'. '
			#os.remove(file)
			return article
		except Exception as e:
			self.write_log("Error in parse_pdf: "+str(e))
			return article
			
	def json_string(self, string):
		string = string.replace('\r\n', ' ')
		string = string.replace('\r', '')
		string = string.replace('\n', '')
		#string = string.replace("'", "\'")
		string = string.replace('\"', '')
		return string		
	
	######### NETWORK ################
	
	def make_net(self):
		sentences_list = {}
		for index, id_article in enumerate(self.articles):
			self.write_log("ANNOTATING "+str(index+1)+" of "+str(len(self.articles))+" articles")
			#Tokenize article by period and re-join (Remove extra space between period)
			sentences_list[id_article] = self.sent_detector.tokenize(self.articles[id_article])
			self.articles[id_article] = ''.join(sentences_list[id_article])
			self.request_data(id_article, self.articles[id_article])
		for index_article, id_article in enumerate(self.annotations["article"]):
			sentence_start = 0
			total_words = len(re.findall(r'\w+', self.articles[id_article]))
			for sentence in sentences_list[id_article]:
				sentence_end = sentence_start + len(sentence) -1
				spot_list = []
				for index_annotation, annotation in enumerate(self.annotations["article"][id_article]):
					if annotation >= sentence_start and annotation < sentence_end:
						spot_list.append(self.annotations["article"][id_article][annotation])
				self.find_edges(id_article, sentence, spot_list, total_words)
				sentence_start = sentence_end
		for e in range(len(self.net['edges'])):
			bio = self.check_bio_edge(self.net['edges'][e]['data']['label'].replace("not ", ""))
			self.net['edges'][e]['data']['bio'] = bio
			self.net['edges'][e]['data']['aid'] = list(self.net['edges'][e]['data']['aid'])
			#calc weight
			tf_medium = 0
			for index, id_article in enumerate(self.net['edges'][e]['data']['tf']):
				tf_medium+= self.net['edges'][e]['data']['tf'][id_article]['tf']/self.net['edges'][e]['data']['tf'][id_article]['total_words']
			tf = tf_medium/len(self.net['edges'][e]['data']['tf'])
			idf = math.log(len(self.articles)/len(self.net['edges'][e]['data']['tf']))
			self.net['edges'][e]['data']['weight'] = tf*idf if idf > 0 else tf
		self.net['nodes'] = sorted(self.net['nodes'],  key=lambda x: x['data']['size'], reverse=True)
		self.reset_log()
		dump = {'annotations':  self.annotations, 'nodes': self.net['nodes'], 'edges': self.net['edges'], 'articles': self.articles_id}
		dump = json.dumps(dump, default=self.set_default)
		print(dump)
		self.save_dump(dump)
		
	def set_default(self, obj):
		if isinstance(obj, set):
			return list(obj)
		raise TypeError
	
	################# ANNOTATION ########################
	
	def response_replace(rp):
		return rp.replace('\n', " ")   \
        .replace("\\'", "'")       \
        .replace('\"',  '"')       \
        .replace('\\', "")
	
	def request_data(self, id_article, article):
		try:
			data = None
			annotations = self.get_annotations(id_article)
			if annotations:
				try:
					data = json.loads(annotations['data'])
					if data:
						self.save_annotations(id_article, data)
						return
				except Exception as e:
						data = None
			par = {'name': article}
			r = requests.post(url = self.webserver_url, data = par, timeout=1800)
			#data = json.loads(response_replace(r.content.decode('utf-8')))
			data = r.json() 
			if data['response']:
				self.save_annotations_db(id_article, json.dumps(data['response']))
				self.save_annotations(id_article, data['response'])
			return
		except Exception as e:
			self.write_log("Error in request_data: "+str(e))

	def save_annotations(self, id_article, data):
		self.annotations['article'][id_article] = {}
		for key, annotation in enumerate(data):
			#store in annotation word_list and spot_list
			try:
				if(len(annotation["categories"]) == 0):
					annotation["categories"] = ["other"]
				if(len(annotation["categories"]) > 0 and annotation["categories"][0] == ''):
					annotation["categories"] = ["other"]
				if(annotation["Word"] in self.annotations['word_list']):
					self.annotations['word_list'][annotation["Word"]]['count']+= 1
				else:
					self.annotations['word_list'][annotation["Word"]] = annotation.copy()
					self.annotations['word_list'][annotation["Word"]]['count'] = 1
				#store in annotation spot_list
				if(annotation["spot"] in self.annotations['spot_list']):
					self.annotations['spot_list'][annotation["spot"]]['count']+= 1
				else:
					self.annotations['spot_list'][annotation["spot"]] = annotation.copy()
					self.annotations['spot_list'][annotation["spot"]]['count'] = 1
				#store in annotation list by article
				self.annotations['article'][id_article][annotation["start_pos"]] = annotation.copy();
				self.write_log("Save annotation: "+annotation["Word"])	
			except Exception as e:
				self.write_log("Error in save_annotations: "+str(e))	
	
	def clean_token(self, token):
		token = token.replace('NOT_', 'not ')
		not_alpha = re.findall(r'[^a-zA-Z\d\s:]', token)
		if len(not_alpha) > 0 or len(token) < 3:
			return False
		else:
			return token
	
	############## EDGES ANALYSIS ##################
	
	def find_edges(self, id_article, sentence, spot_list, total_words):
		self.write_log("FINDING edges for: "+sentence)
		doc = self.nlp(sentence)
		verbs = {}
		for w in doc:
			if w.pos == VERB:
				verbs[w.text] = []
				self.recoursive_search(w, w.text, verbs, 0)
		for v in verbs:
			sub = None
			dob = None
			for s in spot_list:
				for e in verbs[v]:
					if s["spot"] in e:
						if sub is not None:
							dob = s
							if self.passive_form(sentence):
								self.save_edge(id_article, dob, sub, v, total_words, sentence)
							else:
								self.save_edge(id_article, sub, dob, v, total_words, sentence)
							dob = None
						if sub is None:
							sub = s
					
	def recoursive_search(self, el, verb, verbs, count):
		count+=1
		if count > 2:
			return
		for child in el.children:
			verbs[verb].append(child.text)
			self.recoursive_search(child, verb, verbs, count)
	
	def passive_form(self, sentence):
		matcher = Matcher(self.nlp.vocab)
		doc = self.nlp(sentence)
		passive_rule = [{'DEP':'nsubjpass'},{'DEP':'aux','OP':'*'},{'DEP':'auxpass'},{'TAG':'VBN'}]
		matcher.add('Passive', [passive_rule])
		matches = matcher(doc)
		if len(matches):
			return True
		return False
		
	
	def check_exception(self, sentence):
		#check parentheses
		is_an_exception = False
		parentheses = ["{", "[", "(", ")", "]", "}"]
		if any(x in sentence for x in parentheses):
			result = regex.search(r'''(?<rec>\((?:[^()]++|(?&rec))*\))''',sentence,flags=regex.VERBOSE)
			if result is not None:
				is_an_exception = False
			else:
				is_an_exception = True
		else:
			is_an_exception = False
		#check ,
		if "," in sentence or ";" in sentence:
			is_an_exception = True
		return is_an_exception
	
	def check_bio_edge(self, edge):
		score = len(edge);
		for e in self.bioterms:
			if nltk.edit_distance(e, edge) < score:
				score = nltk.edit_distance(e, edge)
		try:
			normalized_score = score/len(edge)
		except:
			normalized_score = 999
		return round(normalized_score, 3)
		
		
	def save_edge(self, id_article, spot1, spot2, edge_label, total_words, sentence):
		try:
			foundn1 = False
			foundn2 = False
			# n1 = self.annotations['spot_list'][spot1]['Word']
			# n2 = self.annotations['spot_list'][spot2]['Word']
			n1 = spot1['Word']
			n2 = spot2['Word']
			#self.write_log("Saving edges for: "+sentence+" - "+n1+" "+edge_label+" "+n2)
			index_e = -1
			edge_id = hashlib.md5((n1+n2+edge_label).encode('utf-8')).hexdigest()
			#edge
			for index, edge in enumerate(self.net['edges']):
				if(edge['data']['id'] == edge_id):
					index_e = index		
			bio = 0
			new_edge = {"id": edge_id, "source": n1, "target": n2, "label": edge_label, "weight": 0, "mrho": 0, "bio": bio}
			if index_e == -1: #edge not found ,new insert
				new_edge['aid'] = {id_article}
				#mrho
				#new_edge['mrho'] = (float(self.annotations['spot_list'][spot1]['rho'])+float(self.annotations['spot_list'][spot2]['rho']))/2
				new_edge['mrho'] = (float(spot1['rho'])+float(spot2['rho']))/2
				new_edge['tf'] = {}
				new_edge['tf'][id_article] = {'tf': 1, 'total_words': total_words}
				new_edge['sentences'] = {}
				new_edge['sentences'][id_article] = []
				new_edge['sentences'][id_article].append(sentence)
				self.write_log("INSERT edge : "+sentence+" - "+n1+" "+edge_label+" "+n2)
				self.net['edges'].append({'data': new_edge})
			else:
				new_edge['aid'] = self.net['edges'][index_e]['data']['aid']
				new_edge['aid'].add(id_article)
				new_edge['tf'] = self.net['edges'][index_e]['data']['tf']
				new_edge['sentences'] = self.net['edges'][index_e]['data']['sentences']
				#mrho
				new_edge['mrho'] = ((float(spot1['rho'])+float(spot2['rho']))/2 + self.net['edges'][index_e]['data']['mrho'])/2
				#TFIDF PARAMETERS
				if id_article not in new_edge['tf']:
					new_edge['tf'][id_article] = {'tf': 1, 'total_words': total_words}
				else:
					new_edge['tf'][id_article]['tf']+=1 
				#Sentences
				if id_article not in new_edge['sentences']:
					new_edge['sentences'][id_article] = []
				if sentence not in new_edge['sentences'][id_article]:
					new_edge['sentences'][id_article].append(sentence)
				self.write_log("UPDATE edge : "+sentence+" - "+n1+" "+edge_label+" "+n2)
				self.net['edges'][index_e] = {'data': new_edge}
				
				
			# Node
			if(n1 == n2):
				foundn2 = True
			for node in self.net['nodes']:
				if (node['data']['label'] == n1):
					foundn1 = True
					node['data']['size']+=1
				if (node['data']['label'] == n2):
					foundn2 = True
					node['data']['size']+=1
			if not foundn1:
				self.net['nodes'].append({'data': {"id": n1, "label": n1, "size": 1, "spot": spot1}})
			if not foundn2:
				self.net['nodes'].append({'data': {"id": n2, "label": n2, "size": 1, "spot": spot2}})
		except Exception as e:
			self.write_log("Error in save_edge: "+str(e))
		
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
			self.write_log("Error in get_conjunctionterms: "+str(e))
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
			self.write_log("Error in get_negationterms: "+str(e))
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
			self.write_log("Error in get_bioterms: "+str(e))
		return terms
		
	def get_request(self):
		sql = "SELECT * FROM netme.requests WHERE id = %s"
		sql_data = (self.searchid,)
		try:
			cursor = self.db.cursor(dictionary=True)
			cursor.execute(sql, sql_data)
			res = cursor.fetchall()
			for r in res:
				return r
		except Exception as e:
			self.write_log("Error in get_requests: "+str(e))
		return None
	
	def get_dump(self):
		sql = "SELECT * FROM netme.dumps WHERE id = %s"
		sql_data = (self.searchid,)
		try:
			cursor = self.db.cursor(dictionary=True)
			cursor.execute(sql, sql_data)
			res = cursor.fetchall()
			for r in res:
				return r
		except Exception as e:
			self.write_log("Error in get_dump: "+str(e))
		return None
	
	def save_dump(self, text):
		self.db.reconnect()
		sql = "INSERT INTO netme.dumps (id, data) VALUES (%s, %s) ON DUPLICATE KEY UPDATE data = %s, update_on = CURRENT_TIMESTAMP()"
		sql_data = (self.searchid, text, text)
		try:
			cursor = self.db.cursor(dictionary=True)
			cursor.execute(sql, sql_data)
			self.db.commit()
			self.db.close()
			return True
		except Exception as e:
			self.write_log("Error in save_dump: "+str(e))
		return False
	
	def save_annotations_db(self, id_article, text):
		sql = "INSERT INTO netme.annotations (id, id_article, data) VALUES (%s, %s, %s)"
		sql_data = (self.searchid, id_article, text)
		try:
			cursor = self.db.cursor(dictionary=True)
			cursor.execute(sql, sql_data)
			self.db.commit()
			return True
		except Exception as e:
			self.write_log("Error in save_annotations_db: "+str(e))
		return False
		
	def get_annotations(self, id_article):
		if "pmc|" in id_article or "pubmed|" in id_article:
			#I can search between id_article directly
			sql = "SELECT * FROM netme.annotations WHERE id_article = %s"
			sql_data = (id_article,)
		else:
			sql = "SELECT * FROM netme.annotations WHERE id = %s AND id_article = %s"
			sql_data = (self.searchid, id_article)
		try:
			cursor = self.db.cursor(dictionary=True)
			cursor.execute(sql, sql_data)
			res = cursor.fetchall()
			for r in res:
				return r
		except Exception as e:
			self.write_log("Error in get_annotations: "+str(e))
		return None
	
	############## LOG ##################
		
	def reset_log(self):
		logfile_path = self.path+"logs/"+self.searchid
		if(os.path.exists(logfile_path)):
			os.remove(logfile_path)
		
	def write_log(self, text):
		try:
			logfile_path = self.path+"logs/"+self.searchid
			file = open(logfile_path, 'a') 
			time = datetime.datetime.now().strftime("%Y-%m-%d %H:%M:%S")
			text = time + ":\t\t" + text + "\n"
			file.write(text) 
			file.close() 
			if(self.debug):
				print(text+ "\n")
		except Exception as e:
			print(str(e))
			
n = NetBuilder();
