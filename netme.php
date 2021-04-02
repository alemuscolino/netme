<?
include dirname(__FILE__).'/template/header.php';
?>			
<div class="container p-4 netme page">
	<div class="results-message alert alert-success" role="alert"></div>
	<div class="results-error alert alert-danger" role="alert"></div>
	
	<h2 class="text-center text-primary"> Create your Network from<br></h2>
	<div class="row select-source">
		<div data-source="db" class="col-4 text-center source">
			<div class="button display-3 p-4 mx-auto text-primary"><i class="fas fa-database p-2"></i><h4> Database </h4></div>
		</div>
		<div data-source="text" class="col-4 text-center source">
			<div class="button display-3 p-4 mx-auto text-primary"><i class="fas fa-keyboard p-2"></i><h4> TEXT input </h4></div>
		</div>
		<div data-source="pdf" class="col-4 text-center source">
			<div class="button display-3 p-4 mx-auto text-primary"><i class="fas fa-file-pdf p-2"></i><h4> PDF files </h4></div>
		</div>
	</div>
	<form class="netme_par" data-source="db">
		<div class="collapse source-content" id="source_db">
			<div class="card card-body">
				<div class="row">
					<div class="col-md-12">
						<div class="form-check form-check-inline">
							<input class="form-check-input" type="radio" name="db_type" id="db_pmc" value="db_pmc" checked>
							<label class="form-check-label" for="db_type1">
								Full Text Article (PMC)
							</label>
						</div>
						<div class="form-check form-check-inline">
							<input class="form-check-input" type="radio" name="db_type" id="db_pubmed" value="db_pubmed">
							<label class="form-check-label" for="db_type2">
								Abstract (PubMed)
							</label>
						</div>
						<div class="form-check form-check-inline">
							<i class="far fa-question-circle" data-toggle="tooltip" data-placement="top" title="Select database from which netme extracts the reference literature"></i>
						</div>
					</div>
				</div>
				<div class="row">
					<div class="col-md-12">
						<div class="form-check form-check-inline">
							<input class="form-check-input" type="radio" name="db_source" id="db_query" value="db_query" checked>
							<label class="form-check-label" for="db_type1">
								Search from query terms
							</label>
						</div>
						<div class="form-check form-check-inline">
							<input class="form-check-input" type="radio" name="db_source" id="db_id" value="db_id" >
							<label class="form-check-label" for="db_type1">
								Search from specific Paper ID
							</label><br>	
						</div>
						<div class="form-check form-check-inline">
							<i class="far fa-question-circle" data-toggle="tooltip" data-placement="top" title="Choose whether to search the database from a search query or from a list of paper ids"></i>
						</div>
						<div class="pl-4 collapse db-source show" id="db_query_options">
							<div class="row">
								<div class="col-12">
									<label>Query parameters <i class="far fa-question-circle" data-toggle="tooltip" data-placement="top" title="The query for the search engine"></i></label>
									<div class="input-group mb-2">
										<input type="text" name="db_terms" class="form-control" placeholder="Example - PTEN AND SRC OR RPE" required>
									</div>	
								</div>			
							</div>
							<div class="row">
								<div class="col-md-8">
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
								<div class="col-md-4">
									<div class="form-group">
										<label>Sort by <i class="far fa-question-circle" data-toggle="tooltip" data-placement="top" title="Specifies the sort order for the resulting list of papers"></i></label>
										<select class="form-control" name="db_sort">
											<option value="relevance">relevance</option>
											<option value="pub+date">date</option>
										</select>
									</div>
								</div>
							</div>
						</div>
						<div class="row pl-4 collapse db-source" id="db_id_options">
							<div class="col-md-12">
								<label>Comma separated UIDs <i class="far fa-question-circle" data-toggle="tooltip" data-placement="top" title="A list of UIDs separated by comma"></i></label><br>
								<input type="text" name="db_id" class="form-control" placeholder="Example - 123456, 789123, ...">
							</div>
						</div>
					</div>	
				</div>
				<div class="row">
					<div class="col-12">
						<label>Name your network</label>
						<div class="input-group">
							<input type="text" name="description" class="form-control" placeholder="Example - My network sample">
						</div>
						<button class="btn btn-primary mt-2 form-control send">NetME</button>
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
							<input type="text" name="description" class="form-control" placeholder="Example - My network sample">
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
							<input type="text" name="description" class="form-control" placeholder="Example - My network sample">
						</div>
						<button class="btn btn-primary mt-2 form-control send">NetME</button>
					</div>	
				</div>
			</div>
		</div>
	</form>
	
	<hr>
	<div class="container">
		<h3 class="text-center text-primary"> Your latest networks  </h3>
	<table id="table-latest-searches" class="table table-striped table-bordered w-100" style="width:100%">
		<thead>
			<tr>
				<th scope="col">id</th>
				<th scope="col">create on</th>
				<th scope="col">update on</th>
				<th scope="col">description</th>
				<th scope="col">sources</th>
				<th scope="col">pubmed</th>
				<th scope="col">pmc</th>
				<th scope="col">freetext</th>
				<th scope="col">pdf</th>
			</tr>
		</thead>
		<tbody>
		</tbody>
	</table>
	</div>
	
</div>
<div class="loader">
	<div id="log">Wait ...</div>
</div>

<?
include dirname(__FILE__).'/template/footer.php';
?>	
<script>
createTableLatestSearches();
</script>
