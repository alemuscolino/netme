var netdata = null;
var loading = false;
var searchid = 0;
var hidden_nodes = [];
$(document).ready(function(){
	$("#rho").on("input", function(){
		$("#rho_val").html($(this).val());
	});
	
	$("#minweight").on("input", function(){
		$("#minweight_val").html($(this).val());
	});
	
	$("#minweight").on("change", function(){
		var _this = this;
		createGraph();
	});

	//Kewords search item
/* 		$('#terms').keypress(function(event){
		var keycode = (event.keyCode ? event.keyCode : event.which);
		if(keycode == '13'){
				searchTerms();
		}
	}); */
	
	$("#send").click(function(){
		if($('#keywords-search').hasClass('show') && $("#terms").val()){ //Keywords search
			//searchTerms();
			annotate();
		}else{
			if($("#text").val()){
/* 				var abstracts = {"no_article": $("#text").val()};
 */				annotate();
			}else{
				alert("Insert text or search terms!");
			}
		}
	});
	
	$('body').on('click', '#annotations-list #hidenode', function(){
		var _this = this;
		var word = $(_this).data('word');
		var i = hidden_nodes.indexOf(word);
		if(i >= 0){
			$(_this).removeClass("fa-eye-slash").addClass("fa-eye");
			hidden_nodes.splice(i, 1);
		}else{
			$(_this).addClass("fa-eye-slash").removeClass("fa-eye");
			hidden_nodes.push(word);
		}
		createGraph();
	});
})


function annotate(){
	var terms = $("#terms").val();
	var retmax = $('#retmax').val();
	var sort = $('#sort').val();
	var free_text = $("#text").val(); 
	var rho = $('#rho').val();
	var max_items = $('#max_items').val();
	searchid = Math.floor(Date.now() / 1000);
	//showLoader();
	$.ajax({
		url: "xhr/request.php",
		type : 'POST',
		dataType:'json',
		data: {
			'function' : 'get_data',
			'free_text' : free_text,
			'terms' : terms,
			'searchid' : searchid,
			'rho' : rho,
			'retmax' : retmax,
			'max_items' : max_items,
			'sort': sort,
		},
		success : function(data) {
			/* hideLoader();
			netdata = data;
			createTable();
			createTablePub();
			$('html,body').animate({scrollTop: $('#graph').offset().top},'slow');
			if(netdata.response){
				createGraph();
			} */
			$(".results-message").html("Netme batch elaboration in progress. Your ID is "+searchid+". Visit <a href='results.php?id="+searchid+"'>this link</a> to see results.");
			$(".results-message").show();
		},
		error : function(e){
			//hideLoader();
		}
	});
	//log
	//$("#log").html("Starting parsing operation");
	//checkLog();
}

function getDump(id){
	$.getJSON("py/dumps/"+id, function(data){
		netdata = {}
		netdata.response = data;
		createTable();
		createTablePub();
		$('html,body').animate({scrollTop: $('#graph').offset().top},'slow');
		if(netdata.response && netdata.response.articles){
			$(".results-container").show();
			createGraph();
		}else{
			$(".results-message").html("Netme elaboration in progress for the request id. Try again later!");
			$(".results-message").show();
		}
	}).fail(function(){
		$(".results-message").html("Netme elaboration in progress for the request id. Try again later!");
		$(".results-message").show();
	});
}

/* function checkLog(){
	$.ajax({
		url:'py/logs/'+searchid,
		success: function (data){
			lines = data.split("\n");
			log = lines[lines.length-2];
			$("#log").html(log);
			if(loading){
				setTimeout(checkLog, 1000);
			}
		},
		error: function (error){
			if(loading){
				setTimeout(checkLog, 1000);
			}
		}
	});
} */

function createTablePub(){
	var tablePub = $("#pub");
	var tbodyPub = $("#pub tbody")
	tbodyPub.html('');
	$.each(netdata.response.articles, function(index, value){
		var row = '<tr><td>'+value+'</td><td><a href="https://www.ncbi.nlm.nih.gov/pmc/articles/PMC'+value+'">https://www.ncbi.nlm.nih.gov/pmc/articles/PMC'+value+'</a></td></tr>';
		tbodyPub.append(row);
	});
	tablePub.show();
}

function createTable(){
	var tableAnnotationsList = $("#annotations-list");
	var tbodyAnnotationsList = $("#annotations-list tbody")
	var tableResults = $("#results");
	var tbodyResults = $("#results tbody")
	var tableNet = $("#net");
	var tbodyNet = $("#net tbody")
	tbodyAnnotationsList.html('');
	$.each(netdata.response.results, function(index, value){
		var row = '<tr><td>'+value['Word']+'</td><td><i data-word="'+value['Word']+'" id="hidenode" class="fa fa-eye"></i></td></tr>';
		tbodyAnnotationsList.append(row);
	});
	tableAnnotationsList.show();
	tbodyResults.html('');
	$.each(netdata.response.results, function(index, value){
		//var row = '<tr><td>'+value['Word']+'</td><td>'+value['rho']+'</td><td>'+value['wid']+'</td><td>'+value['spot']+'</td><td><i data-word="'+value['Word']+'" id="hidenode" class="fa fa-eye"></i></td></tr>';
		var row = '<tr><td>'+value['Word']+'</td><td>'+value['rho']+'</td><td>'+value['wid']+'</td><td>'+value['spot']+'</td></tr>';
		tbodyResults.append(row);
	});
	tableResults.show();
	tbodyNet.html('');
	$.each(netdata.response.net, function(index, value){
		if('source' in value.data){
			var row = '<tr><td>'+value['data']['source']+'</td><td>'+value['data']['target']+'</td><td>'+value['data']['label']+'</td><td>'+value['data']['weight']+'</td></tr>';
			tbodyNet.append(row);
		}
	});
	tableNet.show();
}

function mapColor(categories){
	if(categories){
		if(categories.includes('gene') && categories.includes('proteine')){
			return '#00ff00'; //green
		}
		if(categories.includes('gene')){
			return '#ff0000'; //red
		}
		if(categories.includes('proteine')){
			return '#ffff00'; //yellow
		}
		if(categories.includes('disease')){
			return '#0000ff';  //blue
		}
		if(categories.includes('drug')){
			return '#000000'; //black
		}
	}
	
	return '#ff00ff'; //grey
}

function hashString(s) {
	var h = 0, l = s.length, i = 0;
	if ( l > 0 )
		while (i < l)
			h = (h << 5) - h + s.charCodeAt(i++) | 0;
	return h;
};

function hashEdge(source, target){ //Return unique edge hash
	var list = [source, target];
	var res = list.join(' ');
	return hashString(res).toString();
}

function makeNet(){		
	var size = [];
	var net = []
	var edgeslist = {};
	var minweight = parseFloat($('#minweight').val());
	$.each(netdata.response.net, function(index, value){
		if('source' in value.data){//EDGE
			if(!size[value.data.source]) size[value.data.source] = 10;
			if(!size[value.data.target]) size[value.data.target] = 10;
			edge_hash = hashEdge(value.data.source, value.data.target); 
			if(!hidden_nodes.includes(value.data.source)  && !hidden_nodes.includes(value.data.target)){
				if(parseFloat(value.data.weight) >= minweight){ //write into edgeslist
					if(!(edge_hash in edgeslist)){ //graph edge 
						net.push(value);
						edgeslist[edge_hash] = [];
					}
					edgeslist[edge_hash].push(value);
					size[value.data.source]++;
					size[value.data.target]++;
				}
			}
		}else{
			if(!hidden_nodes.includes(value.data.id)){//NODE
				net.push(value);
			}
		}
	});
	for(n in net){
		if('size' in net[n].data){
			net[n].data.size = size[net[n].data.id];
			net[n].data.color = mapColor(netdata.response.results[net[n].data.spot].categories);
		}
	}
	return {"net": net, "edgeslist": edgeslist};
}

function createGraph(){
	$("#graph").html("");
	net = makeNet();
	//console.log(net);
	var cy = cytoscape({
		container: $('#graph'),
		elements: net.net,
		
		style: [ // the stylesheet for the graph
			{
				selector: 'node',
				style: {
					'background-color': 'data(color)',
					'label': 'data(id)',
					/* 'width': 'data(size)',
					'height': 'data(size)' */
					'width': 10,
					'height': 10
				}
			},
			{
				selector: 'edge',
				style: {
					'width': 2,
					'line-color': '#ccc',
					'target-arrow-color': '#ccc',
					'target-arrow-shape': 'triangle',
					'curve-style': 'bezier',
				}
			}
		],
		layout: {
			name: 'random',
		} 
	});
	
	// bind tapstart to edges and highlight the connected nodes
	cy.on('tap', function(event) {
		/* var connected = event.target.connectedNodes();
		connected.addClass('highlight'); */
		if(event.target === cy ){
			$("#graph-list thead").html('');
			$("#graph-list tbody").html('<tr><td colspan="2"><b>Tap on nodes or edges to show more info</b></td></tr>');
		}else{//Node or edge
			if('source' in event.target.data()){//EDGE
				edge_hash = hashEdge(event.target.data().source, event.target.data().target);
				displayEdges(net.edgeslist[edge_hash]);
			}else{ //NODE
				if('articles' in netdata.response.results[event.target.data().spot]){
					displayNodes(event.target.data().label, netdata.response.results[event.target.data().spot]['articles']);
				}
			}
		}
	});
}

function displayEdges(edgeslist){
	$("#graph-list tbody").html('');
	$("#graph-list thead").html('<tr><th colspan="2">edge from '+edgeslist[0].data['source']+' to '+edgeslist[0].data['target']+' </th></tr><tr><th scope="col">Edge</th><th scope="col">Weight</th></tr>')
	$.each(edgeslist, function(index, value){
		var bio_edge = 1
		if(value.data['bio'] > 0.2) bio_edge = 2
		var row = '<tr class="bio-edge-'+bio_edge+'"><td>'+value.data['label']+'</td><td>'+value.data['weight']+'</td></tr>';
		$("#graph-list tbody").append(row);
	});
}

function displayNodes(node, articlelist){
	$("#graph-list tbody").html('');
	$("#graph-list thead").html('<tr><th scope="col">Article URL for node '+node+'</th></tr>')
	$.each(articlelist, function(index, value){
		var row = '<tr><td><a href="https://www.ncbi.nlm.nih.gov/pmc/articles/PMC'+value+'" target="_blank">'+value+'</a></td></tr>';
		$("#graph-list tbody").append(row);
	});
}

function downloadCanvasAsImage(){
	let downloadLink = document.createElement('a');
	downloadLink.setAttribute('download', 'graph.png');
	let canvas = document.getElementsByTagName('canvas');
	let dataURL = canvas[2].toDataURL('image/png');
	let url = dataURL.replace(/^data:image\/png/,'data:application/octet-stream');
	downloadLink.setAttribute('href', url);
	downloadLink.click();
}

function showLoader(){
	$('.loader').show();
	$("#search").prop('disabled', true);
	$("#graph").html("");
	$(".results-container").hide();
	loading = true;
}

function hideLoader(){
	$('.loader').hide();
	$("#search").prop('disabled', false);
	$(".results-container").show();
	loading = false;
	$("#log").html("Starting parsing operation");
}
