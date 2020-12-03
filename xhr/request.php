<?php
include_once('../config.php'); 
if(!empty($_POST['function'])){
	$error = 1;
	$data = null;
	$response = '';
	$error = '';
	$status = 200;
	switch($_POST['function']){
		case 'get_data':		
			$file = $base_dir.'py/abstracts/'.$_POST['searchid'];
			if(isset($_POST['free_text']) and !empty($_POST['free_text'])){
				$content = $_POST['free_text'];
				file_put_contents($file, $content);
			}
			$command_string = 'python3 '.$base_dir.'py/netbuilder.py '.$_POST['rho'].' '.$_POST['max_items'].' '.$_POST['retmax'].' '.$_POST['sort'].' '.$_POST['searchid'].' "'.$_POST['terms'].'"';
			$command = escapeshellcmd($command_string);
			/*$response = shell_exec($command); 
			$response = json_decode($response);On the fly*/
			shell_exec($command. "> /dev/null 2>/dev/null &");
		break;
	}
}else{
	$status = 400;
	$error = 'Missing parameters';
}
$data = array(
	'status' => $status,
	'response' => $response,
	'error' => $error,
);
header("Content-type: application/json");
echo json_encode($data);
exit();
?>
