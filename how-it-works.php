<?
include dirname(__FILE__).'/template/header.php';
?>			
<div class="container p-4 netme page">
	<div class="results-message alert alert-success" role="alert"></div>
	<div class="results-error alert alert-danger" role="alert"></div>
	
	<h1 class="text-center text-primary">How it works</h1>
	<h2>Search interface</h2>
	<p class="text-justify">Netme offers a front-end developed in PHP and Javascript, in which the network rendering is performed with the CytoscapeJS library. 
	Its back-end, integrating Tagme, is instead written in Java, with the support of the both Python NLTK and SpaCy libraries for the NLP module.
	Pubmed search is performed with the Entrez Programming Utilities, 
	a set of server-side programs providing a stable interface to the Entrez database and to the query system at the 
	National Center for Biotechnology Information (NCBI). 
	Netme is equipped with an easy-to-use web interface providing three major functions: 
	<ul>
	<li>Pubmed query-based network annotation;</li> 
	<li>User-provided free-text network annotation;</li> 
	<li>User-provided PDF documents network annotation.</li> 
	</ul>
	</p>
	<p class="text-justify">
	In the query-based network annotation, the user provides a list of keywords, which are employed to run a query on Pubmed, or a list of article ids. 
	The top resulting papers are retrieved and the network inference procedure is performed. Several parameters can be defined by the user such as: 
	the number of top article to retrieve from Pubmed, and the criteria used to sort papers (relevance or date).

	In the user-provided free-text network annotation, users provide a free text which is then input to the network inference procedure.

	In the user-provided PDF documents network annotation, users provide a set of PDF documents which are then input to the network inference procedure.
	</p>
	<hr>
	<h2>Generated network</h2>
	<h3>Lorem ipsum</h3>
	<div class="row mb-5">
		<div class="col-md-6 text-center">
			<img src="<?=$base_url?>img/network_1.png" width="100%" class="text-center">
		</div>
		<div class="col-md-6">
				<p class="text-justify">Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat. Duis aute irure dolor in reprehenderit in voluptate velit esse cillum dolore eu fugiat nulla pariatur. Excepteur sint occaecat cupidatat non proident, sunt in culpa qui officia deserunt mollit anim id est laborum.</p>
		</div>
	</div>
	<h3>Lorem ipsum</h3>
	<div class="row mb-5">
		<div class="col-md-6 text-center">
			<img src="<?=$base_url?>img/network_2.png" width="100%" class="text-center">
		</div>
		<div class="col-md-6">
				<p class="text-justify">Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat. Duis aute irure dolor in reprehenderit in voluptate velit esse cillum dolore eu fugiat nulla pariatur. Excepteur sint occaecat cupidatat non proident, sunt in culpa qui officia deserunt mollit anim id est laborum.</p>
		</div>
	</div>
	<h3>Lorem ipsum</h3>
	<div class="row mb-5">
		<div class="col-md-6 text-center">
			<img src="<?=$base_url?>img/network_3.png" width="100%" class="text-center">
		</div>
		<div class="col-md-6">
				<p class="text-justify">Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat. Duis aute irure dolor in reprehenderit in voluptate velit esse cillum dolore eu fugiat nulla pariatur. Excepteur sint occaecat cupidatat non proident, sunt in culpa qui officia deserunt mollit anim id est laborum.</p>
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
						showMessage("Netme batch elaboration in progress. Your ID is "+data.response+". You will be redirected to the results page at <a href='results.php?id="+data.response+"'>this link</a> in 5 seconds.");
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
