		</div>
		<script src="<?=$base_url?>js/script.js"></script>
		<footer class="pt-4 my-md-5 pt-md-5 border-top">
      <div class="container">
        <p class="float-right">
          <a href="#">Back to top</a>
        </p>
        <div class="row">
					<div class="col-md-5">
						<div class="row">
							<div class="col-md-4">
								<img src="img/logo-unict.png" height="100px">
							</div>
							<div class="col-md-8">
								<p><small><a href="https://www.dfa.unict.it/" target="_blank">University of Catania, Catania, Italy, Dept. Physics and Astronomy</small></p>
								<p><small><a href="http://www.medclin.unict.it/" target="_blank">University of Catania, Catania, Italy, Dept. of Clinical and Experimental Medicine</a></small></p>
							</div>
						</div>
					</div>
					<div class="col-md-5">
						<div class="row">
							<div class="col-md-4">
								<img src="img/logo-unipi.png" height="100px">
							</div>
							<div class="col-md-8">
								<p><small><a href="https://www.unipi.it/" target="_blank">University of Pisa, Pisa, Italy, Dept. Computer Science</a></small><p>
							</div>
						</div>
					</div>
				</div>
      </div>
    </footer>
		<div class="modal fade" id="modal-sentences" tabindex="-1" role="dialog" aria-labelledby="modal-sentencesLabel" aria-hidden="true">
			<div class="modal-dialog modal-lg" role="document">
				<div class="modal-content">
					<div class="modal-header">
						<h5 class="modal-title" id="modal-sentencesLabel"></h5>
						<button type="button" class="close" data-dismiss="modal" aria-label="Close">
							<span aria-hidden="true">&times;</span>
						</button>
					</div>
					<div class="modal-body">
					</div>
					<div class="modal-footer">
						<button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
					</div>
				</div>
			</div>
		</div>
		<div class="modal fade" id="modal-message" tabindex="-1" role="dialog" aria-hidden="true">
			<div class="modal-dialog modal-lg" role="document">
				<div class="modal-content">
					<div class="modal-header">
						<h5 class="modal-title"></h5>
						<button type="button" class="close" data-dismiss="modal" aria-label="Close">
							<span aria-hidden="true">&times;</span>
						</button>
					</div>
					<div class="modal-body">
					</div>
					<div class="modal-footer">
						<button type="button" class="btn btn-secondary" data-dismiss="modal">Ok</button>
					</div>
				</div>
			</div>
		</div>
			<?
			if($maintenance_mode){
				echo "<script type='text/javascript'>
								$(document).ready(function(){
									$('#modal-message').find('.modal-title').html('Warning!');
									$('#modal-message').find('.modal-body').html('Netme is under maintenance!');
									$('#modal-message').modal('show');
								});
							</script>";
			}
			?>
			
	</body>
</html>
