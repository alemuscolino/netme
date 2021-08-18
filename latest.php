<?
include dirname(__FILE__).'/template/header.php';
?>			
<div class="container p-4 netme page">
	<div class="results-message alert alert-success" role="alert"></div>
	<div class="results-error alert alert-danger" role="alert"></div>
	
	<div class="container">
		<h2 class="text-center text-primary">Your latest networks</h2>
	<table id="table-latest-searches" class="table table-striped table-bordered w-100" style="width:100%">
		<thead>
			<tr>
				<th scope="col">id</th>
				<th scope="col">create on</th>
				<th scope="col">update on</th>
				<th scope="col">description</th>
				<th scope="col">sources</th>
				<th scope="col">pubmed</th>
				<th scope="col">pmc</th>
				<th scope="col">freetext</th>
				<th scope="col">pdf</th>
			</tr>
		</thead>
		<tbody>
		</tbody>
	</table>
	</div>
	
</div>
<div class="loader">
	<div id="log">Wait ...</div>
</div>

<?
include dirname(__FILE__).'/template/footer.php';
?>	
<script>
var usr = "<?=$usr?>";
createTableLatestSearches();
$(document).ready(function() {

	
	/* $('[data-toggle="tooltip"]').tooltip();
	
	$(".select-source .source").hover(function() {
			$(this).addClass('my-shadow'); 
		}, 
		function() {
			$(this).removeClass('my-shadow');
		}
	); */
	
	/* $(document).on("click", ".select-source .source", function(){
		var _this = this;
		var source = $(_this).data("source");
		$(".collapse.source-content").collapse('hide');
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
	});  */
	
	/* $(document).on("change", "[name='db_source']", function(){
		var _this = this;
		$(".collapse.db-source").collapse('hide');
		source = $("[name='db_source']:checked").attr('id');
		switch(source){
			case "db_id":
				$("#db_id_options").collapse('toggle');
				$("input[name='db_terms']").prop('required',false);
				$("input[name='db_idlist']").prop('required',true);
			break;
			case "db_query":
				$("#db_query_options").collapse('toggle');
				$("input[name='db_terms']").prop('required',true);
				$("input[name='db_idlist']").prop('required',false);
			break;
		}
	}); */

/* 	$(".page.netme form.netme_par").submit(function(e){
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
		if(source == "pdf" && totalfiles > 0 || (source == "db" && serialized_data["db_terms"] != '') || (source == "db" && serialized_data["db_idlist"] != '') || (source == "text" && serialized_data["freetext"] != '')){
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
	}); */
});
</script>
