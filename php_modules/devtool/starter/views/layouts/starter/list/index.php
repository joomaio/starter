<div class="container">
	<div class="row justify-content-center">
		<div class="col-lg-10 col-12">
			<h2 class="text-center mt-4 mb-2">Solutions</h2>
			<div class="mb-4">
				<?php echo $this->renderWidget('core::notification'); ?>
			</div>
			<?php echo $this->render('starter.list.filter', []); ?>
		</div>
		<div class="col-lg-10 col-12">
			<div class="solution-list row">
				<?php while ($this->list->hasRow()) echo $this->render('starter.list.row'); ?>
			</div>
		</div>
	</div>
</div>
<form class="hidden" method="POST"  id="form_install">
    <input type="hidden" value="<?php echo $this->token ?>" name="token">
</form>
<form class="hidden" method="POST"  id="form_uninstall">
    <input type="hidden" value="<?php echo $this->token ?>" name="token">
</form>
<?php echo $this->render('starter.list.javascript', []); ?>
