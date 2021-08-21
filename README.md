# NetME

is a framework that builds a biomedical knowledge graph starting from a set of n documents obtained through a query to the PubMed database. Papers can be sorted by relevance (default) or publication date. 
Users can also provide a list of PMCID/PMID or a set of PDF documents. The inferred network contains biological elements (i.e., genes, diseases, drugs, enzymes) as nodes and edges as possible relationships.

The knowledge graph is created as follows:
   1. First, a customized TAGME version, named OntoTAGME , converts the full-text of the input documents into a list of entities using literature databases and ontologies (GeneOntology, DisGeNET, DrugBank, and Obofoundry) as corpus. These entities will be the knowledge graph nodes.
   2. Next, an NLP model based on Python SpaCy, and NLTK libraries, is executed to infer the relations among nodes belonging either to the same sentences or to the adjacent sentences of the same document.

The final network will contain both directed and undirected edges according to the predictions made by the model.

The user interface has been implemented in php, html, css and javascript. In addition, the cytoscape js library has been used to show the network 

## PIPELINE
![](https://github.com/alemuscolino/netme/blob/main/pipeline.jpg?raw=true)

## BUILDING
### 1. prerequisites:
   - MySQL
   - php 7.4
   - server apache 2
   - git

### 2. configuration:
   - clone the netme repository:
   
         cd /var/www/html/
         git clone https://github.com/alemuscolino/netme.git
   
   - create a new site-available configuration file or use the default one:
   
         # default one example
         cd /etc/apache2/sites-available/
         # edit 000-default.conf
         nano 000-default.conf
         # replace DocumentRoot /var/www/html with DocumentRoot /var/www/html/netme
   
   - active the apache 2 mod_rewrite module:
   
         sudo a2enmod rewrite
         sudo service apache2 restart
   
   - active the short_tag in php.ini
   
         cd /etc/php/7.4/apache2
         sudo nano php.ini
         # change Off with On in short_tag parameter
   
   - import netme.sql dump into MySQL
         
         # create netme database in mysql
         mysql -u root -p
         mysql> create database netme;
         # create netme user and assign all privileges for the netme database
         mysql> CREATE USER 'netme'@'mysql_ip' IDENTIFIED BY 'password';
         mysql> GRANT ALL PRIVILEGES ON netme.* TO 'netme'@'mysql_ip';
         mysql> FLUSH PRIVILEGES;
         # import the netme tables
         cd /var/www/html/netme
         mysql -u netme -p netme < netme.sql
         
   - change mysql username and password in database.php
         
         cd /var/www/html/netme/class
         # edit database.phd
         # change the following rows
         protected static $username = 'username'
	     protected static $password = 'password'
	     
   - download javascript libraries
   
         # create the directory vendor under the netme archive
	     cd /var/www/html/netme
	     mkdir vendor
	     # download datatables through the website: https://datatables.net/download/index
	     # you need to select the following options:
	     #   - Step 1. Choose a styling framework:  "DataTables"
	     #   - Step 2. Select packages:             "DataTables"
	     #   - Extension:                           "Responsive; RowReorder"
	     #   - Step 3. Pick a download method:      "Download"
	     #   - finally, click on Download files button
	     # unzip the directory, and then move all the elements of such archve under vendor directory
	     # after that remove the following directory
	     cd /var/www/html/netme/vendor
	     mv DataTables-version datatables
	     mv Responsive-version responsive
	     mv RowReorder-version rowreorder
	     # next download select2 (source code zip)from the following github repo: https://github.com/select2/select2/releases/tag/4.1.0-rc.0
	     # unzip and rename the arche in select2
	     # move this last under the vendor directory
	     # finally, download dropzone from the following repo github in zip format: https://github.com/dropzone/dropzone
	     # unzip the archive and rename in dropzone
	     # move this last under vendor directory

### 3. install libraries to www-data user:
    # create and change owner to the following directories
    sudo mkdir /var/www/.local
    sudo mkdir /var/www/.cache
    sudo mkdir /var/www/nltk_data
    sudo chown www-data.www-data /var/www/.local
    sudo chown www-data.www-data /var/www/.cache
    sudo chown www-data.www-data /var/www/nltk_data
    # install pip3 and python3 for the www-data user
    sudo su -l www-data -s /bin/bash
    pip3 install numpy
    pip3 install nltk
    pip3 install treetaggerwrapper
    pip3 install mysql-connector-python
    pip3 install xml-python
    pip3 install pymupdf
    pip3 install scipy
    pip3 install -U spacy
    pip3 install frontend
    python3 -m spacy download en_core_web_sm
    # download nltk stopword
    python3
    import nltk
    nltk.download('stopwords')
