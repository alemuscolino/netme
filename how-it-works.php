<?
include dirname(__FILE__).'/template/header.php';
?>			
<div class="container p-4 netme page">
	<div class="results-message alert alert-success" role="alert"></div>
	<div class="results-error alert alert-danger" role="alert"></div>
	
	<h1 class="text-center text-primary">How it works</h1>
	<h2>Search interface</h2>
	<p class="text-justify">NETME offers a front-end developed in PHP and Javascript, in which the network rendering is performed using the CytoscapeJS library. 
	Its back-end, integrating Tagme, is written in Java, with the support of the Python NLTK and SpaCy libraries for the NLP module.
	Pubmed search is performed with the Entrez Programming Utilities, 
	a set of server-side programs providing a stable interface to the Entrez database and to the query system at the 
	National Center for Biotechnology Information (NCBI).</p>

	<div class="row mb-3 d-flex justify-content-center">
		<div class="col-md-6 text-center img-container-70">
			<img src="<?=$base_url?>img/tutorial/homepage1.png" width="100%" class="text-center img-shadow">
		</div>
	</div>

	<p>From the homepage, clicking on the blue <b>NETME</b> button sends the user to the network creation page.</p>

	<hr>
	<h2>Create your network from</h2>
	<p class="text-justify">The creation page offers three easy-to-use main interfaces to build a biological network:</p>
	<ul class="mb-5">
		<li>Pubmed query-based network annotation;</li> 
		<li>User-provided free-text network annotation;</li> 
		<li>User-provided PDF documents network annotation.</li> 
	</ul>

	<h3>Pubmed</h3>
	<div class="row mb-5 d-flex justify-content-center">
		<div class="col-md-6 text-center img-container">
			<img src="<?=$base_url?>img/tutorial/search1.PNG" width="100%" class="text-center mb-3 img-shadow">
			<p class="text-justify">Pubmed queries require the user to provide a list of keywords, which will will be used to perform a query on Pubmed to retrieve the most relevant articles, which will be given as input to the network inference procedure. Additionally, users can customize their queries if they wish to by clicking on the advanced search tab. NETME allows the user to chose wether to use full texts or only abstracts, wether to search using keywords or selecting specific articles (by using their id). Users can also specify the number of papers to extract and the criteria used to sort papers (relevance or date).</p>
		</div>
	</div>

	<h3>Text input</h3>
	<div class="row mb-5">
		<div class="col-md-6 text-center img-container">
			<img src="<?=$base_url?>img/tutorial/search2.PNG" width="100%" class="text-center mb-3 img-shadow">
			<p class="text-justify">Textual queries require the user to give a free text, which is then provided as an input to the network inference procedure.</p>
		</div>
	</div>

	<h3>PDF files</h3>
	<div class="row mb-5">
		<div class="col-md-6 text-center img-container">
			<img src="<?=$base_url?>img/tutorial/search3.PNG" width="100%" class="text-center mb-3 img-shadow">
			<p class="text-justify">In the PDF provided queries, users provide a set of PDF documents which are then fiven to the network inference procedure.</p>
		</div>
	</div>

	<hr>

	<h3>Console</h3>
	<div class="row mb-5">
		<div class="col-md-6 text-center">
			<img src="<?=$base_url?>img/tutorial/console.png" width="100%" class="text-center img-shadow">
		</div>
		<div class="col-md-6">
				<p class="text-justify">The console shows the elaboration state. In addition it reports the biology elements and their relationships for each annotated document. At the end of elaboration you will be redirect to the page results.</p>
		</div>
	</div>

	<hr>

	<h2>Generated network</h2>
	<h3>Visualization settings</h3>

	<h3>Parameters</h3>
	<div class="row mb-5">
		<div class="col-md-6 text-center">
			<img src="<?=$base_url?>img/tutorial/parameters.png" width="100%" class="text-center img-shadow">
		</div>
		<div class="col-md-6">
			<p class="text-justify">NETME allows the user to fine-tune the network visualization through some customizable parameters. Those are:
				<ul>
				<li><b>Min rho</b>: minimun "confidence" required for annotations, varies between 0 and 1. A low value shows many annotations accepting more noise, while a high one shows only high confidence one, hiding more nodes</li> 
				<li><b>Max items</b>: puts a cap on the maximum number of nodes that get displayed</li> 
				<li><b>Min weight</b>: filters out edges which have a "score" lower than specified. A higher value tends to filter out many edges which are deemed "unsure" or "less important" by the system. Lowering this threshold introduces more of those "risky" edges. </li> 
				<li><b>Min bio</b>: This parameter is high when the verb expressing the relationship is very common in the biological literature. Lowering this threshold shows more relationships between elements using "common-language" verbs. Increasing it instead allows to only show highly specialized language.</li> 
				</ul>
			</p>
		</div>
	</div>

	<h3>Nodes table</h3>
	<div class="row mb-5">
		<div class="col-md-6 text-center">
			<img src="<?=$base_url?>img/tutorial/nodes.png" width="100%" class="text-center img-shadow">
		</div>
		<div class="col-md-6">
			<p class="text-justify">
				In addition to those parameters, NETME offers a table containing all the nodes of the network. It allows to easy access to the option to hide them/visualize them.
			</p>
		</div>
	</div>

	<h3>Edges table</h3>
	<div class="row mb-5">
		<div class="col-md-6 text-center">
			<img src="<?=$base_url?>img/tutorial/edges.png" width="100%" class="text-center img-shadow">
		</div>
	
		<div class="col-md-6">
			<p class="text-justify">
				Edges are also visualized in a table where the system shows the reference paper/snippet of text where such relation was found. Green edges indicate important biological relationship, while red ones are less key.
			</p>
		</div>

	</div>

	<h3>Legend table</h3>
	<div class="row mb-5">
		<div class="col-md-6 text-center">
			<img src="<?=$base_url?>img/tutorial/legend.png" width="100%" class="text-center img-shadow">
		</div>
		
		<div class="col-md-6">
			<p class="text-justify">
				NETME shows on the right a legend with several entity types and their respective visualization color. This menu allows to show/hide only specific biological categories.	
			</p>
		</div>
	</div>

	<h3>Network</h3>
	<div class="row mb-5">
		<div class="col-md-6 text-center">
			<img src="<?=$base_url?>img/tutorial/graph.png" width="100%" class="text-center img-shadow">
		</div>
		<div class="col-md-6">
				<p class="text-justify">
					This section shows the final network based on the user's query. The user may click on a node of the network to view all incoming and outgoing connections, or she may click on an edge to display its type and the verbal relation between the nodes it connects. 
					Note that, the node's name has been truncated to 8 characters, therefore, the entire name is shown only when the mouse pointer is over the node.
				</p>
		</div>
	</div>

	<h3>Pubmed documents set</h3>
	<div class="row mb-5">
		<div class="col-md-6 text-center">
			<img src="<?=$base_url?>img/tutorial/network_3_1.png" width="100%" class="text-center img-shadow">
		</div>
		<div class="col-md-6">
				<p class="text-justify">
					This table lists the Pubmed documents that have been downloaded for building the final NETME network. 
					If you need more informations about a document, you may click on the PMCID (full-text) or PMID (abstract).    
				</p>
		</div>
	</div>

	<h3>OntoTAGME annotations</h3>
	<div class="row mb-5">
		<div class="col-md-6 text-center">
			<img src="<?=$base_url?>img/tutorial/network_3_2.png" width="100%" class="text-center img-shadow">
		</div>
		<div class="col-md-6">
				<p class="text-justify">
					This table lists all the annotations (nodes) extracted by OntoTAGME.
					The <b>spot</b> column contains the entity within a sentence of a document. 
					Instead the <b>word</b> column contains the real name of the entity in OntoTAGME. 
					The <b>category</b> column contains the type of the entity.
					If the <b>rho</b> is small, then the entity is not so relavant into the corpus of the documents set.
				</p>
		</div>
	</div>

	<h3>Relationships table</h3>
	<div class="row mb-5">
		<div class="col-md-6 text-center">
			<img src="<?=$base_url?>img/tutorial/network_3_3.png" width="100%" class="text-center img-shadow">
		</div>
		<div class="col-md-6">
				<p class="text-justify">
					This table contains all the relationships' properties.
					More informations about mrho, bio, etc. have been explained in the <b>Parameters</b> section.
				</p>
		</div>
	</div>		
</div>

<?
include dirname(__FILE__).'/template/footer.php';
?>	
<script>
var usr = "<?=$usr?>";
createTableLatestSearches();
$(document).ready(function() {

	
	/* $('[data-toggle="tooltip"]').tooltip();
	
	$(".select-source .source").hover(function() {
			$(this).addClass('my-shadow'); 
		}, 
		function() {
			$(this).removeClass('my-shadow');
		}
	); */
	
	/* $(document).on("click", ".select-source .source", function(){
		var _this = this;
		var source = $(_this).data("source");
		$(".collapse.source-content").collapse('hide');
		switch(source){
			case "db":
				$("#source_db").collapse('toggle');
			break;
			case "text":
				$("#source_text").collapse('toggle');
			break;
			case "pdf":
				$("#source_pdf").collapse('toggle');
			break;
		}
	});  */
	
	/* $(document).on("change", "[name='db_source']", function(){
		var _this = this;
		$(".collapse.db-source").collapse('hide');
		source = $("[name='db_source']:checked").attr('id');
		switch(source){
			case "db_id":
				$("#db_id_options").collapse('toggle');
				$("input[name='db_terms']").prop('required',false);
				$("input[name='db_idlist']").prop('required',true);
			break;
			case "db_query":
				$("#db_query_options").collapse('toggle');
				$("input[name='db_terms']").prop('required',true);
				$("input[name='db_idlist']").prop('required',false);
			break;
		}
	}); */

/* 	$(".page.netme form.netme_par").submit(function(e){
		e.preventDefault();
		e.stopPropagation();
		var _this = this;
		var source = $(_this).data("source");
		var fdata = new FormData();
		var totalfiles = 0;
		if(source == "pdf"){
			totalfiles = document.getElementById('pdf_files').files.length;
			for (var index = 0; index < totalfiles; index++) {
				fdata.append("files[]", document.getElementById('pdf_files').files[index]);
			}
		} 
		var serialized_data = $(_this).serializeAssoc();
		console.log(serialized_data)
		fdata.append('data', JSON.stringify(serialized_data));
		console.log(source);
		if(source == "pdf" && totalfiles > 0 || (source == "db" && serialized_data["db_terms"] != '') || (source == "db" && serialized_data["db_idlist"] != '') || (source == "text" && serialized_data["freetext"] != '')){
			fdata.append("function",  "send_data");					
			showLoader();
			$.ajax({
				url: "xhr/request.php",
				type : 'POST',
				dataType:'json',
				data: fdata,
				processData: false, 
				contentType: false, 
				success : function(data) {
					hideLoader();
					if(data.error != ''){
						//reloadPage();
						showError("Error during processing. "+data.error);
					}else{
						goToPage("results.php?id="+data.response);
						showMessage("NETME batch elaboration in progress. Your ID is "+data.response+". You will be redirected to the results page at <a href='results.php?id="+data.response+"'>this link</a> in 5 seconds.");
					}
				},
				error : function(e){
					hideLoader();
					reloadPage();
					showError("Error during processing. Incorrect data input. This page will be reloaded in 5 seconds.");
				}
			});
		}else{
			showError("Insert search parameters, free text or PDF documents");
		}
	}); */
});
</script>
