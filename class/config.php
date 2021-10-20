<?
$base_url = 'https://www.netme.tk/';
$base_dir = "/var/www/html/netme/";

include_once($base_dir.'class/database.php');
include_once($base_dir.'class/session.php');

$maintenance_mode = false;

$categories_OLD = [
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

$palette_OLD = [
"#ff4500", 
"#ffd700", 
"#00ff00",
"#dc143c",
"#00ffff",
"#00bfff",
"#0000ff",
"#adff2f",
"#ff7f50",
"#ff00ff",
"#1e90ff",
"#f0e68c",
"#90ee90",
"#add8e6",
"#7b68ee",
"#ee82ee",
"#ffc0cb",
"#808080",
"#556b2f",
"#228b22",
"#7f0000",
"#483d8b",
"#b8860b",
"#008b8b",
"#00008b",
"#8fbc8f",
"#8b008b",
"#b03060"];

$categories = [
"mRNA",
"miRNA",
"lncRNA",
"tRNA",
"miscRNA",
"rRNA",
"snRNA",
"snoRNA",
"variant",
"haplotype",
"disease",
"disorder",
"pathway",
"biological process",
"molecular function",
"role",
"subatomic particle",
"chemical entity",
"small molecule",
"cellular component",
"biotech",
"drug",
"enzyme",
"transporter",
"carrier",
"endogenous retrovirus",
"anatomical entity",
"tissue",
"line cell",
"cell",
"other",
/* "immunoglobulin pseudogene",
"unknown",
"fragile site",
"pseudogene",
"protocadherin",
"T cell receptor gene",
"organ",
"RNA, vault",
"readthrough",
"organismal entity",
"Y RNA",
"complex locus constituent",
"region",
"T cell receptor pseudogene",
"entity",
"virus integration site",
"food",
"processual entity",
"immunoglobulin gene",
"anatomical structure", */
];



$palette = [
"#ff4500", //mRNA
"#ffd700", //miRNA
"#00ff00", //lncRNA
"#dc143c", //tRNA
"#00ffff", //miscRNA
"#00ffff", //rRNA
"#00ffff", //snRNA
"#00ffff", //snoRNA
"#00bfff", //variant
"#0000ff", //haplotype
"#adff2f", //disease
"#adff2f", //disorder
"#ff7f50", //pathway
"#ff00ff", //biological process
"#1e90ff", //molecular function
"#f0e68c", //role
"#90ee90", //subatomic particle
"#add8e6", //chemical entity
"#7b68ee", //small molecule
"#ee82ee", //cellular component
"#ffc0cb", //biotech
"#808080", //drug
"#556b2f", //enzyme
"#228b22", //transporter
"#7f0000", //carrier
"#483d8b", //endogenous retrovirus
"#b8860b", //anatomical entity
"#008b8b", //tissue
"#00008b", //line cell
"#8fbc8f", //cell
"#b03060" //other
];

$usr = isset($_GET['usr']) ? $_GET['usr'] : 0;

?>