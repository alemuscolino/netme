var netdata = null;
var loading = false;
var searchid = 0;
var hiddenNodes = [];
var manuallyHiddenNodes = [];
var update_log = null;
var annotationsList = [];
var articleList = [];
var cy = null;

$(document).ready(function(){
	
	$('[data-toggle="tooltip"]').tooltip();
	
	$(".select-source .source").hover(function() {
			$(this).addClass('my-shadow'); 
		}, 
		function() {
			$(this).removeClass('my-shadow');
		}
	);
	
	$(document).on("click", ".select-source .source", function(){
		var _this = this;
		var source = $(_this).data("source");
		$(".collapse.source-content").collapse('hide');
		$(".select-source .button.text-success").addClass("text-primary").removeClass("text-success");
		$(_this).find(".button").addClass("text-success");
		switch(source){
			case "db":
				$("#source_db").collapse('toggle');
			break;
			case "text":
				$("#source_text").collapse('toggle');
			break;
			case "pdf":
				$("#source_pdf").collapse('toggle');
			break;
		}
	}); 
	
	
	$(document).on("change", "[name='db_source']", function(){
		var _this = this;
		$(".collapse.db-source").collapse('hide');
		source = $("[name='db_source']:checked").attr('id');
		switch(source){
			case "db_id":
				$("#db_id_options").collapse('toggle');
				$("input[name='db_terms']").prop('required',false);
				$("input[name='db_id']").prop('required',true);
			break;
			case "db_query":
				$("#db_query_options").collapse('toggle');
				$("input[name='db_terms']").prop('required',true);
				$("input[name='db_id']").prop('required',false);
			break;
		}
	});
	
	$(".page.netme form.netme_par").submit(function(e){
		e.preventDefault();
		e.stopPropagation();
		var _this = this;
		var source = $(_this).data("source");
		var fdata = new FormData();
		var totalfiles = 0;
		if(source == "pdf"){
			totalfiles = document.getElementById('pdf_files').files.length;
			for (var index = 0; index < totalfiles; index++) {
				fdata.append("files[]", document.getElementById('pdf_files').files[index]);
			}
		} 
		var serialized_data = $(_this).serializeAssoc();
		console.log(serialized_data)
		fdata.append('data', JSON.stringify(serialized_data));
		console.log(source);
		if(source == "pdf" && totalfiles > 0 || (source == "db" && serialized_data["db_terms"] != '') || (source == "db" && serialized_data["db_id"] != '') || (source == "text" && serialized_data["freetext"] != '')){
			fdata.append("function",  "send_data");					
			showLoader();
			$.ajax({
				url: "xhr/request.php",
				type : 'POST',
				dataType:'json',
				data: fdata,
				processData: false, 
				contentType: false, 
				success : function(data) {
					hideLoader();
					if(data.error != ''){
						//reloadPage();
						showError("Error during processing. "+data.error);
					}else{
						goToPage("results.php?id="+data.response);
						showMessage("Netme batch elaboration in progress. Your ID is "+data.response+". You will be redirected to the results page at <a href='results.php?id="+data.response+"'>this link</a> in 5 seconds.");
					}
				},
				error : function(e){
					hideLoader();
					reloadPage();
					showError("Error during processing. Incorrect data input. This page will be reloaded in 5 seconds.");
				}
			});
		}else{
			showError("Insert search parameters, free text or PDF documents");
		}
	});
	

	//Min rho controller
	$(".page.results #minrho").on("input", function(){
		$("#minrho_val").val($(this).val());
		updateVariables();
		
	});
	
	$(".page.results #minrho_val").on("change", function(){
		$("#minrho").val($(this).val());
		updateVariables();
	});
	
	//Max items controller
	$(".page.results #maxitems").on("input", function(){
		$("#maxitems_val").val($(this).val());
		createGraph();
	});
	
	$(".page.results #maxitems_val").on("change", function(){
		$("#maxitems").val($(this).val());
		createGraph();
	});
	
	//Min weight controller
	$(".page.results #minweight").on("input", function(){
		$("#minweight_val").val($(this).val());
		createGraph();
	});
	
	$(".page.results #minweight_val").on("change", function(){
		$("#minweight").val($(this).val());
		createGraph();
	});
	
	//Min bio controller
	$(".page.results #minbio").on("input", function(){
		$("#minbio_val").val($(this).val());
		createGraph();
	});
	
	$(".page.results #minbio_val").on("change", function(){
		$("#minbio").val($(this).val());
		createGraph();
	});
	
	//Reload Netme data
	$(".page.results #reload_netme").click(function(){
		var _this = this;
		var fdata = new FormData();
		fdata.append("searchid",  dump_id); 
		fdata.append("function",  "reload_netme");					
		$.ajax({
			url: "xhr/request.php",
			type : 'POST',
			dataType:'json',
			data: fdata,
			processData: false, 
			contentType: false, 
			success : function(data) {
				reloadPage();	
			},
			error : function(e){
				reloadPage();
			}
		});
	});
	
	//Listener for annotations-list
	$('body .page.results').on('click', '#annotations-list .hidenode', function(){
		var _this = this;
		if($(_this).data('word') == '.ALLNODES'){
			if($(_this).hasClass("fa-eye-slash")){
				$.each(annotationsList, function(index, value){
					value.show = 1;
				});
			}else{
				$.each(annotationsList, function(index, value){
					value.show = 0;
				});
			}
		}else{
			var word = $(_this).data('word');
			if($(_this).hasClass("fa-eye-slash")){
				$.each(annotationsList, function(index, value){
					if(value.word == word)
						value.show = 1;
				});
			}else{
				$.each(annotationsList, function(index, value){
					if(value.word == word)
						value.show = 0;
				});
			}
		}
		createTableAnnotationsList();
		createGraph();
	});
	
	//Listener for categories label
	$('body .page.results').on('click', '.legend-labels .hidecat', function(){
		var _this = this;
		var action = '';
		var cat = $(_this).data('cat');
		var nodes = getNodesFromCat(cat);
		if($(_this).hasClass("fa-eye-slash")){
			action = 'add'
			$(_this).removeClass("fa-eye-slash").addClass("fa-eye");
		}else{
			action = 'remove'
			$(_this).addClass("fa-eye-slash").removeClass("fa-eye");
		}
		$.each(annotationsList, function(index, value){
			if(nodes.includes(value.word)){
				if(action == 'add'){
					value.show = 1;
				}else{
					value.show = 0;
				}
			}
		});
		createTableAnnotationsList();
		createGraph();
	});
	
	
	
	$.fn.serializeAssoc = function() {
		var data = {};
		$.each( this.serializeArray(), function( key, obj ) {
			var a = obj.name.match(/(.*?)\[(.*?)\]/);
			if(a !== null)
			{
				var subName = new String(a[1]);
				var subKey = new String(a[2]);
				if( !data[subName] ) {
				  data[subName] = { };
				  data[subName].length = 0;
				};
				if (!subKey.length) {
					subKey = data[subName].length;
				}
				if( data[subName][subKey] ) {
				  if( $.isArray( data[subName][subKey] ) ) {
					data[subName][subKey].push( obj.value );
				  } else {
					data[subName][subKey] = { };
					data[subName][subKey].push( obj.value );
				  };
				} else {
					data[subName][subKey] = obj.value;
				};
				data[subName].length++;
			} else {
				var keyName = new String(obj.name);
				if( data[keyName] ) {
					if( $.isArray( data[keyName] ) ) {
						data[keyName].push( obj.value );
					} else {
						data[keyName] = { };
						data[keyName].push( obj.value );
					};
				} else {
					data[keyName] = obj.value;
				};
			};
		});
		return data;
	};
	
	//If click on edges in more info panel, show sentences
	$('body').on('click', '#graph-list .label, #edges-list .label', function(e){
		e.preventDefault();
		e.stopPropagation();
		var _this = this;
		var id = $(_this).data("id");
		var label = $(_this)[0].innerText;
		var html = "Nothing to show";
		$("#modal-sentences .modal-title").html("Sentences list for label <b>"+label+"</b>");
		$.each(netdata.response.edges, function(index, value){
			if(value.data['id'] == id){
				html = "";
				$.each(value.data["sentences"], function(index, sentences){
					$.each(sentences, function(i, sentence){
						html+= "<b>Article "+index+"</b><br><br>";
						var regex = new RegExp('('+label+')', 'ig');
						sentence = sentence.replace(regex, '<span class="highlight">$1</span>');
						html+="<i>"+sentence+"</i><br><hr><br>";		
					});
				});
			}
		});
		$("#modal-sentences .modal-body").html(html);
		$('#modal-sentences').modal('toggle');
	});
})

function goToPage(page){
	setTimeout(function(){window.location.href = page;}, 5000);
}

function reloadPage(){
	setTimeout(function(){window.location.reload()},5000);
}

function showMessage(message){
	$(".results-message").html(message);
	$(".results-message").show();
}

function showError(message){
	$(".results-error").html(message);
	$(".results-error").show();
}

function hideAlerts(){
	$(".results-message").hide();
	$(".results-error").hide();
}

function writeSearchData(){
	data = netdata.response.search_data;
	html = '';
	if(data["pubmed_terms"] != '' || data["pubmed_id"] != ''){
		html+= "<p><b>PUBMED</b><ul>";
		if(data["pubmed_terms"] != '')
			html+= "<li><b>Terms:</b> "+data["pubmed_terms"]+"</li><li><b>Max number of articles:</b> "+data["pubmed_retmax"]+"</li><li><b>Sorted by:</b> "+data["pubmed_sort"]+"</li>";
		if(data["pubmed_id"] != '')
			html+= "<li><b>Id list:</b> "+data["pubmed_id"]+"</li>";
		html+="</ul></p>";
	}
	if(data["pmc_terms"] != '' || data["pmc_id"] != ''){
		html+= "<p><b>PUBMED CENTRAL</b><ul>";
		if(data["pmc_terms"] != '')
			html+= "<li><b>Terms:</b>"+data["pmc_terms"]+"</li><li><b>Max number of articles:</b> "+data["pmc_retmax"]+"</li><li><b>Sorted by:</b> "+data["pmc_sort"]+"</li>";
		if(data["pmc_id"] != '')
			html+= "<li><b>Id list:</b> "+data["pmc_id"]+"</li>";
		html+="</ul></p>";
	}
	if(data["freetext"] != ''){
		html+= "<p><b>FREETEXT:</b> "+data["freetext"]+"</p>";
	}
	if(data["pdf"] != '' && data["pdf"] != null){
		html+= "<p><b>PDF:</b> "+data["pdf"]+"</p>";
	}
	$('#search-data').html(html);
	var description = data["description"] != '' ? data["description"] : "No description"
	$("#search-title").html("<b>Search description: </b>"+description+" - <b>Launched on: </b>"+data["create_on"]+" - <b>Latest update: </b>"+netdata.response.create_on)
}

function getDump(){
	var _this = this;
	var fdata = new FormData();
	fdata.append("dump_id",  dump_id); 
	fdata.append("function",  "get_data");					
	$.ajax({
		url: "xhr/request.php",
		type : 'POST',
		dataType:'json',
		data: fdata,
		processData: false, 
		contentType: false, 
		success : function(data) {
			if(data.status == 200  && data.response ){
				if(data.response.articles){
					//Data dump was generated
					netdata = {}
					netdata.response = data.response;
					hideAlerts();
					initVariable();
					writeSearchData();
					createTables();
					$('html,body').animate({scrollTop: $('#graph').offset().top},'slow');
					$(".results-container").show();
					$('.log-container').hide();
					clearInterval(update_log);
					createGraph();
				}else{
					//Show log
					showMessage("Netme elaboration in progress for ID "+dump_id+". Show progress below.");
					if(data.response.log != $('.log-container').html()){
						$('.log-container').html(data.response.log);
						$('.log-container').show();
						$('.log-container').scrollTop(Math.pow(10,9));
					}
					if(update_log == null){
						update_log = setInterval(getDump,5000);
					}
				}
			}else{
				showMessage("Netme elaboration in progress for ID "+dump_id+". Show progress below.");
			}
			hideLoader();	
		},
		error : function(e){
			showWaitMessage(dump_id);
			hideLoader();
		}
	});
}

function initVariable(){
	maxitems_val = netdata.response.nodes.length > 50 ? 50 : netdata.response.nodes.length;
	$("#maxitems").attr({"max" : netdata.response.nodes.length ,"min" : 0, "value" : maxitems_val});
	$("#maxitems_val").val(maxitems_val);
}

function updateVariables(){
	var minRho = parseFloat($('#minrho_val').val());
	var maxItems = parseFloat($('#maxitems_val').val());
	var minWeight = parseFloat($('#minweight_val').val());
	$.each(annotationsList, function(index, value){
		if(typeof(netdata.response.annotations.word_list[value.word]) != "undefined"){
			if(parseFloat(netdata.response.annotations.word_list[value.word].rho) < parseFloat(minRho)){
				value.show = 0;
			}else{
				value.show = 1;
			}
		}	
	});
	createTableAnnotationsList();
	createGraph();
}

function createTableLatestSearches(){
	var fdata = new FormData();
	var latest_searches = [];
	fdata.append("function",  "latest_searches");
	fdata.append("usr",  usr);		
	$.ajax({
		url: "xhr/request.php",
		type : 'POST',
		dataType:'json',
		data: fdata,
		processData: false, 
		contentType: false, 
		success : function(data) {
			latest_searches = data["response"];	
			$("#table-latest-searches").DataTable().destroy();
			$("#table-latest-searches").removeAttr('width').DataTable({
				data: latest_searches,
				responsive: true,				
				columns: [
					{ title: "search ID", data: "id", "width": "200", "render": function(data, type, full, meta){return '<a href="'+baseUrl+'results.php?id='+full['id']+'" >'+full['id']+'</a>';}},
					{ title: "generated on", data: "create_on", "width": "200"},
					{ title: "updated on", data: "update_on", "width": "200", "render": function(data, type, full, meta){return typeof(full["update_on"]) != "undefined" ? full["update_on"] : "";}},
					{ title: "description", data: "description", "width": "200"},
					{ title: "sources", data: "sources", "width": "200"},
					{ title: "pubmed search", responsivePriority: 10001, data: "pubmed_terms", "width": "10", "render": function(data, type, full, meta){
						if((full["pubmed_terms"] && full["pubmed_terms"]!= '') || (full["pubmed_id"] && full["pubmed_id"].length > 0)){ 
							return "query: <b>"+full["pubmed_terms"]+"</b><br>number of papers: <b>"+full["pubmed_retmax"]+"</b><br>order by: <b>"+full["pubmed_sort"]+"</b><br>id list: <b>"+full["pubmed_id"]+"</b>"; } return '';}},
					{ title: "pmc search", responsivePriority: 10002, data: "pmc_terms", "width": "10", "render": function(data, type, full, meta){
						if((full["pmc_terms"] && full["pmc_terms"]!= '') || (full["pubmed_id"] && full["pmc_id"].length > 0)){ return "query: <b>"+full["pmc_terms"]+"</b><br>number of papers: <b>"+full["pmc_retmax"]+"</b><br>order by: <b>"+full["pmc_sort"]+"</b><br>id list: <b>"+full["pmc_id"]+"</b>"; } return '';}},
					{ title: "freetext search", responsivePriority: 10003, data: "freetext", "width": "10"},
					{ title: "pdf search", responsivePriority: 10004, data: "pdf", "width": "10", "render": function(data, type, full, meta){return full["pdf"];}},
				],
				language: {
					"lengthMenu": "_MENU_",
					"zeroRecords": "Nothing found - sorry",
					"info": "Page _PAGE_ / _PAGES_",
					"infoEmpty": "No records available",
					"infoFiltered": "(from _MAX_ total records)"
				},
				pageLength: 100,
				paging: true,
				fixedColumns: true,
				order: [[ 0, "DESC" ]],
			});
		},
		error : function(e){
			
		}
	});
	
}

function createTableArticles(){
	if(articleList.length == 0){
		$.each(netdata.response.articles, function(index, value){
			articleList.push(getArticleObj(value));
		});
	}
	$("#table-articles").DataTable().destroy();
	$("#table-articles").DataTable({
		data: articleList,
		columns: [
			{ title: "id", data: "id"},
			{ title: "link", data: "link", "render": function(data, type, full, meta){return '<a href="'+full['link']+'" target="_blank">'+full['id']+'</a>';}}
		],
		language: {
			"lengthMenu": "_MENU_",
			"zeroRecords": "Nothing found - sorry",
			"info": "Page _PAGE_ / _PAGES_",
			"infoEmpty": "No records available",
			"infoFiltered": "(from _MAX_ total records)"
		},
		paging: true
	});
}

function createTableAnnotationsList(){
	if(annotationsList.length == 0){
		row = {"word": ".ALLNODES", "show": 1};
		annotationsList.push(row);
		$.each(netdata.response.nodes, function(index, value){
			row = {"word": value.data.label, "show": 1};
			annotationsList.push(row);
		});
	}
	$("#annotations-list").DataTable().destroy();
	$("#annotations-list").DataTable({
		data: annotationsList,
		columns: [
			{ title: "word", data: "word"},
			{ title: "show/hide", data: "show", "render": function(data, type, full, meta){return full["show"] == 1 ? '<i data-word="'+full['word']+'" class="hidenode fa fa-eye"></i>' : '<i data-word="'+full['word']+'" class="hidenode fa fa-eye-slash"></i>';}}
		],
		language: {
			"lengthMenu": "_MENU_",
			"zeroRecords": "Nothing found - sorry",
			"infoEmpty": "No records available",
			"infoFiltered": "(from _MAX_ total records)"
		},
		paging: false
	});
}

function createTableNodesList(){
	var nodesList = [];
	$.each(netdata.response.nodes, function(index, value){
		row = netdata.response.annotations.word_list[value.data.label]
		nodesList.push(row);
	});
	$("#nodes-list").DataTable().destroy();
	$("#nodes-list").DataTable({
		data: nodesList,
		columns: [
			{ title: "word", data: "Word"},
			{ title: "categories", data: "categories", "render": function(data, type, full, meta){return full['categories'].join(", ");}},
			{ title: "rho", data: "rho", "render": function(data, type, full, meta){return parseFloat(full['rho']).toFixed(4);}},
			{ title: "spot", data: "spot"},
			{ title: "count", data: "count"},
		],
		language: {
			"lengthMenu": "_MENU_",
			"zeroRecords": "Nothing found - sorry",
			"info": "Page _PAGE_ / _PAGES_",
			"infoEmpty": "No records available",
			"infoFiltered": "(from _MAX_ total records)"
		},
		paging: true
	});
}

function createTableEdgesList(){
	var edgesList = [];
	$.each(netdata.response.edges, function(index, value){
		edgesList.push(value.data);
	});
	$("#edges-list").DataTable().destroy();
	$("#edges-list").DataTable({
		data: edgesList,
		columns: [
			{ title: "source", data: "source"},
			{ title: "edge", data: "label", "render": function(data, type, full, meta){return '<a class="label" href="#" data-id="'+full['id']+'">'+full['label']+'</a>';}},
			{ title: "target", data: "target"},
			{ title: "weight", data: "weight", "render": function(data, type, full, meta){return parseFloat(full['weight']).toFixed(4);}},
			{ title: "mrho", data: "mrho", "render": function(data, type, full, meta){return parseFloat(full['mrho']).toFixed(4);}},
			{ title: "bio", data: "bio", "render": function(data, type, full, meta){return parseFloat(full['bio']).toFixed(4);}},
			{ title: "ref", data: "aid", "render": function(data, type, full, meta){return full['aid'].join(", ");}},
		],
		language: {
			"lengthMenu": "_MENU_",
			"zeroRecords": "Nothing found - sorry",
			"info": "Page _PAGE_ / _PAGES_",
			"infoEmpty": "No records available",
			"infoFiltered": "(from _MAX_ total records)"
		},
		paging: true
	});
}

function createTables(){
	createTableAnnotationsList();
	createTableArticles();
	createTableNodesList();
	createTableEdgesList();
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
	var net = []
	var edgeslist = {};
	var maxItems = parseFloat($('#maxitems_val').val());
	var minWeight = parseFloat($('#minweight_val').val());
	var minBio = parseFloat($('#minbio_val').val());
	var showed = 0;
	//Filter nodes based on maxItems and minRho
	$.each(netdata.response.nodes, function(index, value){
		if(showed >= maxItems || annotationsList.find( annotationsList => annotationsList['word'] === value.data.label ).show == 0 ){
			hideNode(value.data.label);
		}else{
			showed++;
			showNode(value.data.label);
		}
	});
	//Filter nodes
	$.each(netdata.response.nodes, function(index, value){
		if(!hiddenNodes.includes(value.data.id)){//NODE
			net.push(JSON.parse(JSON.stringify(value)));
		}
	});
	//Filter edges
	$.each(netdata.response.edges, function(index, value){
		edge_hash = hashEdge(value.data.source, value.data.target); 
		if(!hiddenNodes.includes(value.data.source) && !hiddenNodes.includes(value.data.target)){
			if(parseFloat(value.data.weight) >= minWeight && parseFloat(value.data.bio) <= minBio){ //write into edgeslist
				if(!(edge_hash in edgeslist)){ //graph edge 
					net.push(JSON.parse(JSON.stringify(value)));
					edgeslist[edge_hash] = [];
				}
				edgeslist[edge_hash].push(value);
			}
		}
	});
	for(n in net){
		if('size' in net[n].data){
			net[n].data.size = Math.sqrt(parseFloat(net[n].data.size))*3;
			var k = categories.indexOf(netdata.response.annotations['word_list'][net[n].data.label].categories[netdata.response.annotations['word_list'][net[n].data.label].categories.length - 1]);
			if(k==-1){
				var k = categories.indexOf("other");
			}
			net[n].data.color = palette[k];
			net[n].data.name = net[n].data.id;
			net[n].data.name_t = net[n].data.id;
			if(net[n].data.id.length > 5){
				net[n].data.name_t = net[n].data.id.substring(0, 5) + '...';
			}
		}
	}
	return {"net": net, "edgeslist": edgeslist};
}

function createGraph(){
	$("#graph").html("");
	net = makeNet();
	cy = cytoscape({
		container: $('#graph'),
		elements: net.net,
		
		style: [ // the stylesheet for the graph
			{
				selector: 'node',
				style: {
					'background-color': 'data(color)',
					'label': 'data(name_t)',
					'width': 'data(size)',
					'height': 'data(size)',
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
			name: 'concentric',
			fit: true, // whether to fit the viewport to the graph
			spacingFactor: 0.5,
			padding: 5, // the padding on fit
			minNodeSpacing: 30,
			/*name: 'cise',
			animate: false,
			refresh: 10, 
			animationDuration: undefined,
			animationEasing: undefined,
			fit: false,
			padding: 5,
			nodeSeparation: 1,
			idealInterClusterEdgeLengthCoefficient: 1.4,
			allowNodesInsideCircle: false,
			maxRatioOfNodesInsideCircle: 0.1,
			springCoeff: 0.05,
			nodeRepulsion: 20000,
			gravity: 0.25,
			gravityRange: 3.8, */
			/* name: 'cose',
			idealEdgeLength: 100,
			nodeOverlap: 20,
			refresh: 20,
			fit: true,
			padding: 30,
			randomize: false,
			componentSpacing: 100,
			nodeRepulsion: 400000,
			edgeElasticity: 100,
			nestingFactor: 5,
			gravity: 80,
			numIter: 1000,
			initialTemp: 200,
			coolingFactor: 0.95,
			minTemp: 1.0 */
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
			if('source' in event.target.data()){ //EDGE
				edge_hash = hashEdge(event.target.data().source, event.target.data().target);
				displayEdges(net.edgeslist[edge_hash]);
			}else{ //NODE
				if(typeof(netdata.response.annotations['word_list'][event.target.data().label]) != 'undefined'){
					displayNodes(event.target.data().label, net.edgeslist);
				}
			}
		}
	});
	
	function makePopper(ele) {
		let ref = ele.popperRef(); // used only for positioning

		ele.tippy = tippy(ref, { // tippy options:
		  content: () => {
			let content = document.createElement('div');
			content.innerHTML = ele.data().label;

			return content;
		  },
		  trigger: 'manual' // probably want manual mode
		});
	}

	cy.ready(function() {
		cy.elements().forEach(function(ele) {
		  makePopper(ele);
		});
	});

	cy.elements().unbind('mouseover');
	cy.elements().bind('mouseover', (event) => event.target.tippy.show());

	cy.elements().unbind('mouseout');
	cy.elements().bind('mouseout', (event) => event.target.tippy.hide());
}

function displayEdges(edgeslist){
	$("#graph-list tbody").html('');
	$("#graph-list thead").html('<tr><th colspan="4">edge from '+edgeslist[0].data['source']+' to '+edgeslist[0].data['target']+' </th></tr><tr><th scope="col">edge</th><th scope="col">weight</th><th scope="col">bio</th><th scope="col">articles</th></tr>')
	$.each(edgeslist, function(index, value){
		var weight = parseFloat(value.data['weight']).toFixed(4)
		var bio = parseFloat(value.data['bio']).toFixed(4)
		var article_text = '';
		$.each(value.data['aid'], function(i, a){
			var article_obj = getArticleObj(a);
			article_text+= '<a href="'+article_obj['link']+'" target="_blank">'+article_obj['id']+'</a> ';
		});
		var row = '<tr style="background-color:'+numberToColorHsl(value.data['bio'])+'"><td><a class="label" href="#" data-id="'+value.data['id']+'">'+value.data['label']+'</a></td><td>'+weight+'</td><td>'+bio+'</td><td>'+article_text+'</td></tr>';

		$("#graph-list tbody").append(row);
	});
}

function numberToColorHsl(i) {
    var h = ((1-i)  * 1.2 * 100) / 360;
    var s = .8;
	var l = .7;
	var r, g, b;
    if(s == 0){
        r = g = b = l;
    }else{
        function hue2rgb(p, q, t){
            if(t < 0) t += 1;
            if(t > 1) t -= 1;
            if(t < 1/6) return p + (q - p) * 6 * t;
            if(t < 1/2) return q;
            if(t < 2/3) return p + (q - p) * (2/3 - t) * 6;
            return p;
        }

        var q = l < 0.5 ? l * (1 + s) : l + s - l * s;
        var p = 2 * l - q;
        r = hue2rgb(p, q, h + 1/3);
        g = hue2rgb(p, q, h);
        b = hue2rgb(p, q, h - 1/3);
    }
    return 'rgb(' + Math.floor(r * 255) + ',' + Math.floor(g * 255) + ',' + Math.floor(b * 255) + ')'; 
}


function displayNodes(node, edgelist){
	$("#graph-list tbody").html('');
	$("#graph-list thead").html('<tr><th colspan="3">'+node+' edges</th></tr><tr><th scope="col">Edge</th><th scope="col">Nodes</th><th scope="col">Articles</th></tr>')
	$.each(edgelist, function(index, source_target){
		$.each(source_target, function(index, value){
			var bio_edge = 1
			if(value.data['bio'] > 0.2) bio_edge = 2
			var article_text = '';
			$.each(value.data['aid'], function(i, a){
				var article_obj = getArticleObj(a);
				article_text+= '<a href="'+article_obj['link']+'" target="_blank">'+article_obj['id']+'</a> ';
			});
			if(value.data['source'] == node){
				var row = '<tr class="bio-edge-'+bio_edge+'"><td>'+value.data["label"]+' <i class="fas fa-arrow-right"></i></td><td>'+value.data["target"]+'</td><td>'+article_text+'</td></tr>';
				$("#graph-list tbody").append(row);
			}
			if(value.data['target'] == node ){
				var row = '<tr class="bio-edge-'+bio_edge+'"><td>'+value.data["label"]+' <i class="fas fa-arrow-left"></i><td>'+value.data["source"]+'</td><td>'+article_text+'</td></tr>';
				$("#graph-list tbody").append(row);
			}
		});
	});
}

function getArticleObj(article){
	article = article.split("|");
	article_obj = {};
	article_obj['type'] = article[0];
	if(article_obj['type'] == "freetext"){
		article_obj['id'] = "freetext";
		article_obj['link'] = '';
	}
	if(article_obj['type'] == "pdf"){
		article_obj['id'] = article[1];
		article_obj['link'] = baseUrl+'py/pdf/'+dump_id+'_'+article[1];
	}
	if(article_obj['type'] == "pmc"){
		article_obj['id'] = article[1];
		article_obj['link'] = 'https://www.ncbi.nlm.nih.gov/pmc/articles/PMC'+article[1];
	}
	if(article_obj['type'] == "pubmed"){
		article_obj['id'] = article[1];
		article_obj['link'] = 'https://pubmed.ncbi.nlm.nih.gov/'+article[1];
	}
	return article_obj;
}

function getNodesFromCat(cat){
	cat = cat.split("|");
	nodes = [];
	$.each(netdata.response.annotations['word_list'], function(index, nodelist){
		for(c in cat){
			if($.inArray(cat[c], nodelist["categories"]) != -1 && $.inArray(nodelist['Word'], nodes) == -1){
				nodes.push(nodelist['Word']);
			}
		}
	});
	return nodes;
}

function showNode(node){
	if(hiddenNodes.indexOf(node) != -1)
		hiddenNodes.splice(hiddenNodes.indexOf(node), 1);
}

function hideNode(node){
	if(!hiddenNodes.includes(node)){
		hiddenNodes.push(node);
	}
}

function downloadCanvasAsImageOld(){
	let downloadLink = document.createElement('a');
	downloadLink.setAttribute('download', 'graph.png');
	let canvas = document.getElementsByTagName('canvas');
	let dataURL = canvas[2].toDataURL('image/png');
	let url = dataURL.replace(/^data:image\/png/,'data:application/octet-stream');
	downloadLink.setAttribute('href', url);
	downloadLink.click();
}

function downloadCanvasAsImage(){
	var svgContent = cy.svg({scale: 1, full: true, bg: '#fff'});
	var blob = new Blob([svgContent], {type:"image/svg+xml;charset=utf-8"});
	var url = URL.createObjectURL(blob);
	var link = document.createElement("a");
	link.download = "graph.svg";
	link.href = url;
	link.click();
}

function downloadCsv(){
	nodesContent = 'data:text/csv;charset=utf-8,';
	edgesContent = 'data:text/csv;charset=utf-8,';
	//Nodes
	nodesContent+= 'node;categories;count;rho\n';
	$.each(netdata.response.annotations.word_list, function(index, nodesList){
		nodesContent+= nodesList.Word+";"+nodesList.categories.join(",")+";"+nodesList.count+";"+nodesList.rho+"\n";
	});
	var encodedNodes = encodeURI(nodesContent);
	var nodesDownloadLink = document.createElement("a");
	nodesDownloadLink.setAttribute("href", encodedNodes);
	nodesDownloadLink.setAttribute("download", "nodes.csv");
	document.body.appendChild(nodesDownloadLink);
	nodesDownloadLink.click();
	//Edges
	edgesContent+= 'source;source_categories;target;target_categories;edge;weight;bio\n';
	$.each(netdata.response.edges, function(index, edgesList){
		if(typeof(edgesList.data.source) != "undefined")
			edgesContent+= edgesList.data.source+";"+netdata.response.annotations.word_list[edgesList.data.source].categories.join(",")+";"+edgesList.data.target+";"+netdata.response.annotations.word_list[edgesList.data.target].categories.join(",")+";"+edgesList.data.label+";"+edgesList.data.weight+";"+edgesList.data.bio+";"+edgesList.data.aid.join(",")+"\n";
	});
	var encodedEdges = encodeURI(edgesContent);
	var edgesDownloadLink = document.createElement("a");
	edgesDownloadLink.setAttribute("href", encodedEdges);
	edgesDownloadLink.setAttribute("download", "edges.csv");
	document.body.appendChild(edgesDownloadLink);
	edgesDownloadLink.click();
}

function showLoader(){
	$('.loader').show();
	$("#search").prop('disabled', true);
	$("#graph").html("");
	//$(".results-container").hide();
	loading = true;
}

function hideLoader(){
	$('.loader').hide();
	$("#search").prop('disabled', false);
	//$(".results-container").show();
	loading = false;
}
