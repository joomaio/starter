<?php echo $this->renderWidget('core::notification'); ?>
<div class="container">
	<div class="row justify-content-center">
		<div class="col-lg-10 col-12">
			<h2 class="text-center my-4">Solutions</h2>
			<?php echo $this->render('starter.list.filter', []); ?>
		</div>
		<div class="col-lg-10 col-12">
			<div class="solution-list row">
				<?php while ($this->list->hasRow()) echo $this->render('starter.list.row'); ?>
			</div>
		</div>
	</div>
</div>
<form class="hidden" method="POST" id="form_delete">
    <input type="hidden" value="<?php echo $this->token ?>" name="token">
    <input type="hidden" value="DELETE" name="_method">
</form>
<?php echo $this->render('starter.list.javascript', []); ?>
