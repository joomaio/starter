<?php echo $this->renderWidget('core::notification'); ?>
<div class="container">
	<div class="row">
		<div class="col-lg-10 col-12">
			
		</div>
	</div>
</div>
<div class="main">
	<main class="content p-0 ">
		<div class="container-fluid p-0">
			<div class="row justify-content-center mx-auto">
				<div class="col-12 p-0">
					<div class="card border-0 shadow-none">
						<div class="card-body">
							<div class="row align-items-center">
								<?php echo $this->render('starter.list.filter', []); ?>
							</div>
						</div>
					</div>
				</div>
			</div>

		</div>
	</main>
</div>
<form class="hidden" method="POST" id="form_delete">
    <input type="hidden" value="<?php echo $this->token ?>" name="token">
    <input type="hidden" value="DELETE" name="_method">
</form>
<?php echo $this->render('starter.list.javascript', []); ?>
