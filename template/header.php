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

		<link rel="stylesheet" href="<?=$base_url?>css/fontawesome/css/all.css">
		<link rel="stylesheet" href="<?=$base_url?>css/bootstrap.min.css">
		<link rel="stylesheet" href="<?=$base_url?>css/style.css">
		
		<!-- Datatables -->
		<link href="<?=$base_url?>vendor/datatables/dataTables.bootstrap4.min.css" rel="stylesheet" type="text/css">
		<link href="<?=$base_url?>vendor/datatables/responsive.dataTables.min.css" rel="stylesheet" type="text/css">
		<link href="<?=$base_url?>vendor/datatables/rowReorder.dataTables.min.css" rel="stylesheet" type="text/css">
		<link href="<?=$base_url?>vendor/datatables/select2.min.css" rel="stylesheet" type="text/css">
		<script src="<?=$base_url?>vendor/datatables/jquery.dataTables.min.js"></script>
		<script src="<?=$base_url?>vendor/datatables/dataTables.bootstrap4.min.js"></script>
		<script src="<?=$base_url?>vendor/datatables/dataTables.responsive.min.js"></script>
		<script src="<?=$base_url?>vendor/datatables/dataTables.rowReorder.min.js"></script>
		<script src="<?=$base_url?>vendor/datatables/select2.min.js"></script>
		
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
								<a class="nav-link" href="https://link.springer.com/chapter/10.1007/978-3-030-65351-4_31">Publications</a>
							</li>
						</ul>
					</div>
				</div>
			</nav>
