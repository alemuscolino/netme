<?
include_once(dirname(__FILE__) .'/../class/config.php'); 
$session = new Session();
$session->start();
?>
<html>
	<head>
		<meta charset="utf-8">
		<link rel="icon" href="favicon.ico" type="image/x-icon">
		<meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
		<title>NetME - On-the-fly knowledge network construction from biomedical literature</title>

		<script src="<?=$base_url?>js/jquery-3.5.1.js"></script>
		<script src="<?=$base_url?>js/popper.min.js"></script>
		<script src="<?=$base_url?>js/bootstrap.min.js"></script>
		<script src="<?=$base_url?>js/cytoscape.min.js"></script>
		<script src="<?=$base_url?>js/canvas2svg.js"></script>
		<script src="<?=$base_url?>js/cytoscape-svg.js"></script>
		
		<script src="https://unpkg.com/layout-base@1.0.2/layout-base.js"></script>
		<script src="https://unpkg.com/avsdf-base/avsdf-base.js"></script>
		<script src="https://unpkg.com/cose-base@1.0.3/cose-base.js"></script>
		<script src="https://unpkg.com/cytoscape-graphml/cytoscape-graphml.js"></script>
		<script src="https://raw.githack.com/iVis-at-Bilkent/cytoscape.js-layvo/unstable/cytoscape-layvo.js"></script>
		<script src="<?=$base_url?>vendor/cise.js"></script>

		<link rel="stylesheet" href="<?=$base_url?>css/fontawesome/css/all.css">
		<link rel="stylesheet" href="<?=$base_url?>css/bootstrap.min.css">
		<link rel="stylesheet" href="<?=$base_url?>css/style.css">
		
		<!-- Datatables -->
		<link href="<?=$base_url?>vendor/datatables/css/dataTables.bootstrap4.min.css" rel="stylesheet" type="text/css">
		<link href="<?=$base_url?>vendor/responsive/css/responsive.dataTables.min.css" rel="stylesheet" type="text/css">
		<link href="<?=$base_url?>vendor/rowreorder/css/rowReorder.dataTables.min.css" rel="stylesheet" type="text/css">
		<link href="<?=$base_url?>vendor/select2/dist/css/select2.min.css" rel="stylesheet" type="text/css">
		<script src="<?=$base_url?>vendor/datatables/js/jquery.dataTables.min.js"></script>
		<script src="<?=$base_url?>vendor/datatables/js/dataTables.bootstrap4.min.js"></script>
		<script src="<?=$base_url?>vendor/responsive/js/dataTables.responsive.min.js"></script>
		<script src="<?=$base_url?>vendor/rowreorder/js/dataTables.rowReorder.min.js"></script>
		<script src="<?=$base_url?>vendor/select2/dist/js/select2.min.js"></script>
		
		
		<!-- Dropzone -->
		<script src="<?=$base_url?>vendor/dropzone/dist/dropzone.js"></script>
		
		<script>
		var baseUrl = "<?=$base_url?>";
		var categories = JSON.parse('<?=json_encode($categories)?>');
		var palette = JSON.parse('<?=json_encode($palette)?>');
		</script>
	</head>
	<body>
		<div class="container-fluid p-0">
			<nav class="navbar navbar-expand-lg navbar-light bg-light">
				<div class="container">
					<a class="navbar-brand" href="<?=$base_url?>"><img src="img/logo.png" width="200"></a>
					<button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation">
						<span class="navbar-toggler-icon"></span>
					</button>
					<div class="collapse navbar-collapse" id="navbarSupportedContent">
						<ul class="navbar-nav mr-auto">
							<li class="nav-item">
								<a class="nav-link" href="<?=$base_url?>">Home</a>
							</li>
							<li class="nav-item">
								<a class="nav-link" href="<?=$base_url?>netme.php">NetMe</a>
							</li>
							<li class="nav-item">
								<a class="nav-link" href="<?=$base_url?>latest.php">Your latest networks</a>
							</li>
							<li class="nav-item">
								<a class="nav-link" href="<?=$base_url?>how-it-works.php">How it works</a>
							</li>
							<li class="nav-item">
								<a class="nav-link" href="https://link.springer.com/chapter/10.1007/978-3-030-65351-4_31">Publications</a>
							</li>
							
						</ul>
					</div>
				</div>
			</nav>
