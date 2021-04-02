<?
include dirname(__FILE__).'/template/header.php';
if(isset($_GET['id']) && intval($_GET['id']) == $_GET['id']){
	$dump_id = intval($_GET['id']);
}else{
	header("Location: ".$base_url."netme.php");
}
?>		
<div class="container p-4 results page">
	<div class="results-message alert alert-success" role="alert"></div>
	<div class="results-error alert alert-danger" role="alert"></div>
	<div class="log-container" style="white-space: pre-line"></div>
	<div class="results-container container">
		<div class="row card">
			<div class="col-12" id="search-title">
			</div>
		</div>
		<div class="row">
			<div id="accordion-parameters" style="width:100%">
				<div class="card">
					<div class="card-header" id="h-parameters-data">
						<h5 data-toggle="collapse" data-target="#parameters-data" aria-expanded="true" aria-controls="parameters-data">
							Parameters data <i class="float-right fa fa-chevron-down"></i>
						</h5>
					</div>
					<div id="parameters-data" class="small container collapse show" aria-labelledby="h-parameters-data" data-parent="#accordion-parameters">
						<div class="row">
							<div class="col-md-6">
								<div class="row">
									<div class="col-12">
										<h6>NODES parameters</h6>
									</div>
									<div class="col-md-6">
										<label>Min rho</label>
										<div class="slidecontainer row">
											<div class="col-md-4">
												<input type="number" value="0" step="0.01" class="form-control px-1 py-0" id="minrho_val">
											</div>
											<div class="col-md-8">
												<input type="range" min="0" max="1" value="0" step="0.01" class="slider mt-2" id="minrho">
											</div>
										</div>
									</div>
									<div class="col-md-6">
										<label>Max items</label>
										<div class="slidecontainer row">
											<div class="col-md-4">
												<input type="number" value="1" step="1" class="form-control px-1 py-0" id="maxitems_val">
											</div>
											<div class="col-md-8">
												<input type="range" min="1" max="100" value="1" step="1" class="slider mt-2" id="maxitems">
											</div>
										</div>
									</div>
								</div>
							</div>
							<div class="col-md-6">
								<div class="row">
									<div class="col-12">
										<h6>EDGES parameters</h6>
									</div>
									<div class="col-md-6">
										<label>Min weight</label>
										<div class="slidecontainer row">
											<div class="col-md-4">
												<input type="number" value="0" step="0.01" class="form-control px-1 py-0" id="minweight_val">
											</div>
											<div class="col-md-8">
												<input type="range" min="0" max="1" value="0" step="0.01" class="slider mt-2" id="minweight">
											</div>
										</div>
									</div>
									<div class="col-md-6">
										<label>Min bio <small>(values close to 0 are biomedical terms)</small></label>
										<div class="slidecontainer row">
											<div class="col-md-4">
												<input type="number" value="1" step="0.01" class="form-control px-1 py-0" id="minbio_val">
											</div>
											<div class="col-md-8">
												<input type="range" min="0" max="1" value="1" step="0.01" class="slider mt-2" id="minbio">
											</div>
										</div>
									</div>
								</div>
							</div>
						</div>
						<div class="row">
							<div class="col-md-4">
								<div class="mt-2 p-3 card annotations-list">
									<table id="annotations-list" class="table table-striped table-bordered w-100">
										<thead>
												<tr>
													<th scope="col">Word</th>
													<th scope="col">show/hide</th>
												</tr>
										</thead>
										<tbody>
										</tbody>
									</table>
								</div>
							</div>
							<div class="col-md-4">
								<div class="mt-2 p-2 card graph-list">
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
							</div>
							<div class="col-md-4">
								<div class="mt-2 p-2 legend card" >
									<div class='legend-title'>Nodes Legend</div>
									<div class='legend-scale'>
										<div>
											<div class="col-6 legend-labels">
											<?
											for($i = 0; $i <= count($categories)/2; $i++){
											?>
												<div><i data-cat="<?=$categories[$i]?>" class="fa fa-eye hidecat"></i><span style='background:<?=$palette[$i]?>;'></span><?=$categories[$i]?></div>
											<?
											}
											?>
											</div>
											<div class="col-6 legend-labels">
											<?
											for($i = count($categories)/2 + 1; $i < count($categories); $i++){
											?>
												<div><i data-cat="<?=$categories[$i]?>" class="fa fa-eye hidecat"></i><span style='background:<?=$palette[$i]?>;'></span><?=$categories[$i]?></div>
											<?
											}
											?>
											</div>
										</div>	
									</div>
								</div>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>
		<div class="row mt-2">
			<div class="col-md-12 card p-0">
					<div id="graph"></div>
			</div>
		</div>
		<div class="row mt-2">
			<div class="col-md-6">
				<button class="btn btn-primary btn-block p-2 mt-2 form-control" onclick="downloadCanvasAsImage()">Download graph (SVG)<i class="fas fa-project-diagram"></i></button>
			</div>
			<div class="col-md-6">
				<button class="btn btn-primary btn-block p-2 mt-2 form-control" onclick="downloadCsv()">Download CSV <i class="fas fa-file-csv"></i></button>	
			</div>
		</div>
		<div class="row">
			<div id="accordion-results" style="width:100%">
				<div class="card">
					<div class="card-header" id="h-search-data">
						<h5 data-toggle="collapse" data-target="#search-data" aria-expanded="true" aria-controls="search-data">
							Search data <i class="float-right fa fa-chevron-down"></i>
						</h5>
					</div>
					<div id="search-data" class="small collapse show" aria-labelledby="h-search-data" data-parent="#accordion-results">
					</div>
					<div class="btn btn-primary m-2" id="reload_netme">Reload netme</div>
				</div>
				
				<div class="card">
					<div class="card-header" id="h-extracted-articles">
						<h5 data-toggle="collapse" data-target="#extracted-articles" aria-expanded="true" aria-controls="extracted-articles">
							Extracted Articles <i class="float-right fa fa-chevron-down"></i>
						</h5>
					</div>
					<div id="extracted-articles" class="collapse show" aria-labelledby="h-extracted-articles" data-parent="#accordion-results">
						<table id="table-articles" class="table table-striped table-bordered w-100" >
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
					<div class="card-header" id="h-nodes-list-container">
						<h5 data-toggle="collapse" data-target="#nodes-list-container" aria-expanded="true" aria-controls="annotation">
							Network Nodes <i class="float-right fa fa-chevron-down"></i>
						</h5>
					</div>
					<div id="nodes-list-container" class="collapse show" aria-labelledby="h-nodes-list-container" data-parent="#accordion-results">
						<table id="nodes-list" class="table table-striped table-bordered w-100">
							<thead>
								<tr>
									<th scope="col">word</th>
									<th scope="col">categories</th>
									<th scope="col">rho</th>
									<th scope="col">spot</th>
									<th scope="col">occurrences</th>
								</tr>
							</thead>
							<tbody>
							</tbody>
						</table>
					</div>
				</div>
				
				<div class="card">
					<div class="card-header" id="h-edges-list-containte">
						<h5 data-toggle="collapse" data-target="#edges-list-container" aria-expanded="true" aria-controls="edges-list-container">
							Network Edges <i class="float-right fa fa-chevron-down"></i>
						</h5>
					</div>
					<div id="edges-list-container" class="collapse show" aria-labelledby="h-edges-list-container" data-parent="#accordion-results">
						<table id="edges-list" class="table table-striped table-bordered w-100">
							<thead>
								<tr>
									<th scope="col">source</th>
									<th scope="col">edge</th>
									<th scope="col">target</th>
									<th scope="col">weight</th>
									<th scope="col">mrho</th>
									<th scope="col">bio</th>
									<th scope="col">ref</th>
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
	<div id="log"></div>
</div>
<?
include dirname(__FILE__).'/template/footer.php';
?>	
<script>
showLoader();
var dump_id = <?=$dump_id?>;
if(dump_id > 0){
	getDump();
}
</script>		
