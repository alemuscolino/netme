<?
include dirname(__FILE__).'/template/header.php';
?>			
<div class="container p-4">
	<div class="results-message alert alert-danger" role="alert">
	</div>
	<h5> Annotation parameter </h5>
	<div class="row">
		<div class="col-md-6">
			<p class="m-0">Rho <span id="rho_val">0.3</span></p>
			<span class="float-left">Few annotations</span><span class="float-right">Many annotations</span>
			<div class="slidecontainer">
				<input type="range" min="0" max="1" value="0.3" step="0.1" class="slider" id="rho">
			</div>
		</div>
		<div class="col-md-6">
			<label>Max number of nodes</label>
			<select class="form-control" id="max_items">
				<option value="10">10</option>
				<option value="20">20</option>
				<option value="40">40</option>
				<option value="50">50</option>
				<option value="100">100 (may take longer time)</option>
				<option value="500">500 (may take longer time)</option>
			</select>
		</div>
	</div>
	<hr>
	<div id="accordion-search">
		<div class="card">
			<div class="card-header" id="h-keywords-search">
				<h5 data-toggle="collapse" data-target="#keywords-search" aria-expanded="true" aria-controls="keywords-search">
						Search articles by keywords and annotate <i class="float-right fa fa-chevron-down"></i>
				</h5>
			</div>
			<div id="keywords-search" class="collapse show p-2" aria-labelledby="h-keywords-search" data-parent="#accordion-search">
				<div class="row">
					<div class="col-12">
						<label>Query terms</label>
						<div class="input-group mb-2">
							<input type="text" id="terms" class="form-control" placeholder="Example - PTEN, RPE">
						</div>	
					</div>			
				</div>
				<div class="row">
					<div class="col-md-8">
						<div class="form-group">
							<label>Articles to extract</label>
							<select class="form-control" id="retmax">
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
							<label>Sort by</label>
							<select class="form-control" id="sort">
								<option value="relevance">relevance</option>
								<option value="pub+date">date</option>
							</select>
						</div>
					</div>
				</div>
			</div>
		</div>
		<div class="card">
			<div class="card-header" id="h-free-search">
				<h5 data-toggle="collapse" data-target="#free-search" aria-expanded="true" aria-controls="free-search">
						Annotate free text <i class="float-right fa fa-chevron-down"></i>
				</h5>
			</div>
			<div id="free-search" class="collapse p-2" aria-labelledby="h-free-search" data-parent="#accordion-search">
				<div class="row">
					<div class="col-12">
						<label>or input free text</label>
						<textarea id="text" rows="10" class="form-control"></textarea>	
					</div>
				</div>
			</div>
		</div>
	</div>
	<button class="btn btn-primary mt-2 form-control" id="send">NetME</button>	
	<hr>
	<div class="results-container">
		<div class="row">
			<div class="col-12">
				<label>Min weight <span id="minweight_val">0.5</span></label>
				<div class="slidecontainer">
					<input type="range" min="0" max="1" value="0.5" step="0.1" class="slider" id="minweight">
				</div>
			</div>
		</div>
		<div class="row">
			<div class="col-3">
				<div class="card annotations-list">
					<table id="annotations-list" class="table table-striped table-bordered">
						<tbody>
						</tbody>
					</table>
				</div>
				<div class="mt-2 card graph-list">
					<table id="graph-list" class="table table-bordered">
						<thead>
							
						</thead>
						<tbody>
							<tr>
							<td colspan="2">
								<b>Tap on nodes or edges to show more info</b>
							</td>
							</tr>
						</tbody>
					</table>
				</div>
				<div class="mt-2 p-2 legend card" >
					<div class='legend-title'>Nodes Legend</div>
					<div class='legend-scale'>
						<ul class='legend-labels'>
							<li><span style='background:#00ff00;'></span>Gene + Proteine</li>
							<li><span style='background:#ff0000;'></span>Gene</li>
							<li><span style='background:#ffff00;'></span>Proteine</li>
							<li><span style='background:#0000ff;'></span>Disease</li>
							<li><span style='background:#000000;'></span>Drug</li>
							<li><span style='background:#ff00ff;'></span>Other</li>
						</ul>
					</div>
				</div>
			</div>
			<div class="col-9 card">
				<div id="graph"></div>
			</div>
			<button class="btn btn-primary mt-2 form-control" onclick="downloadCanvasAsImage()">Download graph</button>	
		</div>
		<div class="row">
			<div id="accordion-results" style="width:100%">
				<div class="card">
					<div class="card-header" id="h-extracted-articles">
						<h5 data-toggle="collapse" data-target="#extracted-articles" aria-expanded="true" aria-controls="extracted-articles">
							Extracted Articles <i class="float-right fa fa-chevron-down"></i>
						</h5>
					</div>
					<div id="extracted-articles" class="collapse show" aria-labelledby="h-extracted-articles" data-parent="#accordion-results">
						<table id="pub" class="table table-striped table-bordered">
							<thead>
								<tr>
									<th scope="col">ArticleID</th>
									<th scope="col">Link</th>
								</tr>
							</thead>
							<tbody>
							</tbody>
						</table>
					</div>
				</div>
				
				<div class="card">
					<div class="card-header" id="h-annotations">
						<h5 data-toggle="collapse" data-target="#annotations" aria-expanded="true" aria-controls="annotation">
							Annotations <i class="float-right fa fa-chevron-down"></i>
						</h5>
					</div>
					<div id="annotations" class="collapse show" aria-labelledby="h-annotations" data-parent="#accordion-results">
						<table id="results" class="table table-striped table-bordered">
							<thead>
								<tr>
									<th scope="col">Word</th>
									<th scope="col">rho</th>
									<th scope="col">wid</th>
									<th scope="col">spot</th>
								</tr>
							</thead>
							<tbody>
							</tbody>
						</table>
					</div>
				</div>
				
				<div class="card">
					<div class="card-header" id="h-network-edges">
						<h5 data-toggle="collapse" data-target="#network-edges" aria-expanded="true" aria-controls="network-edges">
							Network Edges <i class="float-right fa fa-chevron-down"></i>
						</h5>
					</div>
					<div id="network-edges" class="collapse show" aria-labelledby="h-network-edges" data-parent="#accordion-results">
						<table id="net" class="table table-striped table-bordered">
							<thead>
								<tr>
									<th scope="col">Source</th>
									<th scope="col">Target</th>
									<th scope="col">Edge</th>
									<th scope="col">Weigth</th>
								</tr>
							</thead>
							<tbody>
							</tbody>
						</table>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>
<div class="loader">
	<div id="log">Starting parsing operation</div>
</div>
<?
include dirname(__FILE__).'/template/footer.php';
?>		
