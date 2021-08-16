# NetME

is a framework that builds a biomedical knowledge graph starting from a set of n documents obtained through a query to the PubMed database. Papers can be sorted by relevance (default) or publication date. 
Users can also provide a list of PMCID/PMID or a set of PDF documents. The inferred network contains biological elements (i.e., genes, diseases, drugs, enzymes) as nodes and edges as possible relationships.

The knowledge graph is created as follows:
   1. First, a customized TAGME version, named OntoTAGME , converts the full-text of the input documents into a list of entities using literature databases and ontologies (GeneOntology, DisGeNET, DrugBank, and Obofoundry) as corpus. These entities will be the knowledge graph nodes.
   2. Next, an NLP model based on Python SpaCy, and NLTK libraries, is executed to infer the relations among nodes belonging either to the same sentences or to the adjacent sentences of the same document.

The final network will contain both directed and undirected edges according to the predictions made by the model.

The user interface has been implemented in php, html, css and javascript. In addition, the cytoscape js library has been used to show the network 

## pipeline
![](https://github.com/[username]/[reponame]/blob/[branch]/image.jpg?raw=true)
