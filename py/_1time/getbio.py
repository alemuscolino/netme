import requests 

def write_res(text):
	filepath = 'res.txt'
	file = open(filepath, 'a') 
	file.write(text) 
	file.close() 
	

_continue = 'start'
while len(_continue) > 0:
	if _continue == 'start':
		_continue = ''
	url = "https://en.wiktionary.org/w/api.php"
	par = {
		'action': 'query',
		'list': 'categorymembers',
		'cmtitle': 'Category:en:Biology',
		'cmprop': 'title',
		'cmlimit': '500',
		'cmcontinue': _continue,
		'formatversion': 2,
		'format': 'json'
	}
	r = requests.get(url = url, params = par) 
	data = r.json()
	text = ''
	for e in data["query"]["categorymembers"]:
		text+=e["title"]+'\n'
	write_res(text)
	_continue = data["continue"]["cmcontinue"]
	
	
