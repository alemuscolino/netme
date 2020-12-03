<?
include dirname(__FILE__).'/template/header.php';
$dump_id = isset($_GET['id']) ? $_GET['id'] : 0;
?>		
<div class="container p-4">
	<div class="results-message alert alert-danger" role="alert">
	</div>
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
<script>
var dump_id = <?=$dump_id?>;
getDump(dump_id);
</script>		
