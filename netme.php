<?
include dirname(__FILE__).'/template/header.php';
?>			
<div class="container p-4 netme page">
	<div class="results-message alert alert-success" role="alert"></div>
	<div class="results-error alert alert-danger" role="alert"></div>
	
	<h2 class="text-center text-primary"> Create your Network from<br></h2>
	<div class="row select-source">
		<div data-source="db" class="col-4 text-center source" data-toggle="tooltip" data-placement="top" title="Pubmed query-based network annotation">
			<div class="button display-4  mx-auto text-success"><i class="fas fa-database p-2"></i><h6> Pubmed </h6></div>
		</div>
		<div data-source="text" class="col-4 text-center source" data-toggle="tooltip" data-placement="top" title="user-provided free-text network annotation">
			<div class="button display-4   mx-auto text-primary"><i class="fas fa-keyboard p-2"></i><h6> TEXT input </h6></div>
		</div>
		<div data-source="pdf" class="col-4 text-center source" data-toggle="tooltip" data-placement="top" title="user-provided PDF documents network annotation">
			<div class="button display-4   mx-auto text-primary"><i class="fas fa-file-pdf p-2"></i><h6> PDF files </h6></div>
		</div>
	</div>
	<form class="netme_par" data-source="db">
		<div class="collapse source-content show" id="source_db">
			<div class="card card-body">		
				<div class="row">
					<div class="col-md-12">
						<div class="collapse db-source show" id="db_query_options">
							<div class="row">
								<div class="col-12">
									<label>Query parameters <i class="far fa-question-circle" data-toggle="tooltip" data-placement="top" title="The query for the search engine"></i></label>
									<div class="input-group mb-2">
										<input type="text" name="db_terms" class="form-control" placeholder="Example - PTEN AND SRC OR RPE" required>
									</div>	
								</div>			
							</div>
							
						</div>
						<div class="row collapse db-source" id="db_id_options">
							<div class="col-md-12">
								<label>Comma separated UIDs <i class="far fa-question-circle" data-toggle="tooltip" data-placement="top" title="A list of UIDs separated by comma"></i></label><br>
								<input type="text" name="db_id" class="form-control" placeholder="Example - 123456, 789123, ...">
							</div>
						</div>
					</div>	
				</div>
				
				<div class="row">
					<div class="col-12">
						<button class="btn btn-primary mt-2 form-control send">NetME</button>
					</div>	
				</div>
				

				<!-- ADVANCED PARAMETERS -->
				<div class="row">
					<div class="col-md-12 px-4">
						<small>
						<a data-toggle="collapse" href="#advanced_db">Advanced search</a>
						<div class="collapse advanced-db" id="advanced_db">
							<div class="row">
								<div class="col-md-3">
									<label>Search on <i class="far fa-question-circle" data-toggle="tooltip" data-placement="top" title="Select database from which netme extracts the reference literature"></i></label>
									<br>
									<div class="form-check form-check-inline">
										<input class="form-check-input" type="radio" name="db_type" id="db_pmc" value="db_pmc" checked>
										<label class="form-check-label" for="db_type1">
											Full Text Article (PMC)
										</label>
									</div>
									<br>
									<div class="form-check form-check-inline">
										<input class="form-check-input" type="radio" name="db_type" id="db_pubmed" value="db_pubmed">
										<label class="form-check-label" for="db_type2">
											Abstract (PubMed)
										</label>
									</div>
								</div>
								
								<div class="col-md-3">
									<label>Search type <i class="far fa-question-circle" data-toggle="tooltip" data-placement="top" title="Choose whether to search the database from a search query or from a list of paper ids"></i></label>
									<br>
									<div class="form-check form-check-inline">
										<input class="form-check-input" type="radio" name="db_source" id="db_query" value="db_query" checked>
										<label class="form-check-label" for="db_type1">
											Search from query terms
										</label>
									</div>
									<br>
									<div class="form-check form-check-inline">
										<input class="form-check-input" type="radio" name="db_source" id="db_id" value="db_id" >
										<label class="form-check-label" for="db_type1">
											Search from specific Paper ID
										</label><br>	
									</div>
								</div>
								<div class="col-md-3">
									<div class="form-group">
										<label>Papers to extract <i class="far fa-question-circle" data-toggle="tooltip" data-placement="top" title="The number of papers that will be analyzed to create the network"></i></label>
										<select class="form-control" name="db_retmax">
											<option value="10">10</option>
											<option value="20">20</option>
											<option value="50">50</option>
											<option value="100">100 (may take longer time)</option>
											<option value="500">500 (may take longer time)</option>
										</select>
									</div>
								</div>
								<div class="col-md-3">
									<div class="form-group">
										<label>Sort by <i class="far fa-question-circle" data-toggle="tooltip" data-placement="top" title="Specifies the sort order for the resulting list of papers"></i></label>
										<select class="form-control" name="db_sort">
											<option value="relevance">relevance</option>
											<option value="pub+date">date</option>
										</select>
									</div>
								</div>
							</div>
							
							<div class="row">
								<div class="col-12">
									<label>Name your network</label>
									<div class="input-group">
										<input type="text" name="description" class="form-control" placeholder="Network Generated on <?=date("Y-m-d H:i:s")?>">
									</div>
								</div>	
							</div>
						
						</div>
						</small>
					</div>
				</div>
			</div>
		</div>
	</form>
	<form class="netme_par" data-source="text">	
		<div class="collapse source-content" id="source_text">
			<div class="card card-body">
				<div class="row">
					<div class="col-12">
						<label>Input free text</label>
						<textarea name="freetext" rows="10" class="form-control"></textarea>
					</div>
				</div>
				<div class="row">
					<div class="col-12">
						<label>Name your network</label>
						<div class="input-group">
							<input type="text" name="description" class="form-control" placeholder="Network Generated on <?=date("Y-m-d H:i:s")?>">
						</div>
						<button class="btn btn-primary mt-2 form-control send">NetME</button>
					</div>	
				</div>
			</div>
		</div>
	</form>
	<form class="netme_par" data-source="pdf">	
		<div class="collapse source-content" id="source_pdf">
			<div class="card card-body">
				<div class="row">
					<div class="col-12">
						<label>Select one or more pdf files (files larger than 8MB will be discarded)</label><br><br>
						<input type="file" id="pdf_files" name="pdf_files" multiple accept = "application/pdf">
					</div>
				</div>
				<div class="row">
					<div class="col-12">
						<label>Name your network</label>
						<div class="input-group">
							<input type="text" name="description" class="form-control" placeholder="Network Generated on <?=date("Y-m-d H:i:s")?>">
						</div>
						<button class="btn btn-primary mt-2 form-control send">NetME</button>
					</div>	
				</div>
			</div>
		</div>
	</form>
	
	<hr>
	<div class="container">
		<a class="text-center text-primary" href="<?=$base_url?>latest.php"> Your latest networks  </a>
	</div>
	
</div>
<div class="loader">
	<div id="log">Wait ...</div>
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
