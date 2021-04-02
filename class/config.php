<?
$base_url = 'https://www.netme.tk/';
$base_dir = "/var/www/html/netme/";

include_once($base_dir.'class/database.php');
include_once($base_dir.'class/session.php');

$maintenance_mode = false;

$categories = [
"protein",
"enzyme source",
"anatomical entity",
"gene",
"disease",
"pathway",
"drug",
"molecular function",
"cellular component",
"biological process",
"subatomic particle",
"chemical entity",
"role",
"cell",
"sequence feature",
"protein-containing complex",
"glycan",
"material entity",
"biological process",
"other"];

$palette = ["#ff4500","#ffd700","#00ff00","#dc143c","#00ffff","#00bfff","#0000ff","#adff2f","#ff7f50","#ff00ff","#1e90ff","#f0e68c","#90ee90","#add8e6","#7b68ee","#ee82ee","#ffc0cb","#808080","#556b2f","#228b22","#7f0000","#483d8b","#b8860b","#008b8b","#00008b","#8fbc8f","#8b008b","#b03060"];

?>