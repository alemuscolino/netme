<?php
include_once(dirname(__FILE__) .'/../class/config.php'); 
$session = new Session();
$session->start();
$db = new Database();
if(!empty($_POST['function']) ){
	$error = 1;
	$data = null;
	$data_obj = null;
	$response = '';
	$error = '';
	$status = 200;
	switch($_POST['function']){
		case 'send_data':	
			if(!empty($_POST['data'])){
				$data_obj = $db->DecodeData(json_decode($_POST['data']));
				if((is_array($data_obj) && count($data_obj) > 1) || isset($_FILES['files'])){
					$data_obj["session_token"] = $session->request();
					if($data_obj["session_token"]){
						//Save pdf files
						$data_obj["id"] = time();
						if(isset($_FILES['files'])){
							$data_obj["pdf"] = [];
							$countfiles = count($_FILES['files']['name']);
							for($index = 0;$index < $countfiles; $index++){
								if(intval($_FILES['files']['size'][$index]) <= 8000000){
									$path = $base_dir.'py/pdf/'.$data_obj["id"].'_'.$_FILES['files']['name'][$index];
									$data_obj["pdf"][] = $data_obj["id"].'_'.$_FILES['files']['name'][$index];
									move_uploaded_file($_FILES['files']['tmp_name'][$index],$path);
								}
							}
							$data_obj["pdf"] = implode(",", $data_obj["pdf"]);
						}
						//Save data file
						$keys = array_keys($data_obj);
						$result = $db->query("INSERT INTO requests (".implode(',', $keys).") VALUES (:".implode(', :', $keys).")", $data_obj);
						//Launch py script
						$command_string = 'python3 '.$base_dir.'py/netbuilder.py '.$data_obj["id"];
						$command = escapeshellcmd($command_string);
						shell_exec($command. "> /dev/null 2>/dev/null &");
						$response = $data_obj["id"];
					}else{
						$error = 'Exceed request quota. You can launch another request after '.$session->expire().' minutes';
					}
				}else{
					$error = 'Missing parameters or malformed data input';
				}
			}
		break;
		case 'reload_netme':	
			if(!empty($_POST['searchid'])){
				$command_string = 'python3 '.$base_dir.'py/netbuilder.py '.$_POST['searchid'];
				$command = escapeshellcmd($command_string);
				shell_exec($command. "> /dev/null 2>/dev/null &");
			}else{
				$error = 'Missing parameters';
			}	
		break;
		case 'get_data':
			if(!empty($_POST['dump_id'])){
				$id = $_POST['dump_id'];
				$dump = $db->query("SELECT * FROM dumps WHERE id = :id", array("id"=>$id));
				$data = $db->query("SELECT * FROM requests WHERE id = :id", array("id"=>$id));
				$log = $base_dir."py/logs/".$id;
				if(is_file($log)){
					$log_data = file_get_contents($base_dir."py/logs/".$id);
					if ($log_data) {
						$log_data = (object) array('log' => $log_data);
						$response = $log_data;
					}
				}else{
					if(!empty($dump) && !empty($data)){
						$dump_data = json_decode($dump[0]["data"], true);
						if ($dump_data) {
							$dump_data['create_on'] = $dump[0]["create_on"];
							if ($data) {
								$dump_data['search_data'] = $data[0];
								$response = $dump_data;
							}
						}
					}else{
						$error = 'Invalid ID';
					}
				}
			}else{
				$error = 'Missing parameters';
			}	
		break;
		case 'latest_searches':	
			$latest_searches = $db->query("SELECT requests.*, dumps.update_on, dumps.create_on as end_on FROM requests LEFT JOIN dumps on dumps.id = requests.id WHERE requests.session_token = :session_token", array("session_token"=>$_SESSION['token']));
			$response = [];
			foreach($latest_searches as $search){
					$data['id'] = $search['id'];
					$search['description'] = !empty($search['description']) ? $search['description'] : "No description";
					$search['sources'] = "Search on ".(!empty($search['pubmed_terms']) ? " PUBMED '".substr($search['pubmed_terms'], 0, 50)."'" : "")." ".
					(!empty($search['pubmed_id']) ? " PUBMED '".substr($search['pubmed_id'], 0, 50)."'" : "")." ".
					(!empty($search['pmc_terms']) ? " PMC '".substr($search['pmc_terms'], 0, 50)."'" : "")." ".
					(!empty($search['pmc_id']) ? " PMC '".substr($search['pmc_id'], 0, 50)."'" : "")." ".
					(!empty($search['freetext']) ? " FREETEXT '".substr($search['freetext'], 0, 50)."'" : "")." ".
					(!empty($search['pdf']) ? " PDF '".substr($search['pdf'], 0, 50)."'" : ""); 
					$search['update_on'] = $search['update_on'] != '' ? $search['update_on'] : $search['end_on'];
					array_push($response, $search);
			}
		break;
	}
}else{
	$status = 400;
	$response = '';
	$error = 'Missing parameters';
}
ob_clean();
$data = array(
	'status' => $status,
	'response' => $response,
	'error' => $error,
);
header("Content-type: application/json");
echo json_encode($data);
exit();
?>
