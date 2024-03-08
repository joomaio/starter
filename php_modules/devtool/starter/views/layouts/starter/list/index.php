<div class="container">
	<h2 class="text-center mt-4 mb-2">Starter</h2>
	<div class="row justify-content-center">
		<div class="col-lg-10 col-12">
			<ul class="nav nav-tabs" id="myTab" role="tablist">
				<li class="nav-item" role="presentation">
					<button class="nav-link active" id="solutions-tab" data-bs-toggle="tab" data-bs-target="#solutions" type="button" role="tab" aria-controls="solutions" aria-selected="true">Solutions</button>
				</li>
				<li class="nav-item" role="presentation">
					<button class="nav-link" id="themes-tab" data-bs-toggle="tab" data-bs-target="#themes" type="button" role="tab" aria-controls="themes" aria-selected="false">Themes</button>
				</li>
				<li class="nav-item" role="presentation">
					<button class="nav-link" id="config-tab" data-bs-toggle="tab" data-bs-target="#config" type="button" role="tab" aria-controls="config" aria-selected="false">Configuration</button>
				</li>
			</ul>
			<div class="tab-content" id="myTabContent">
				<div class="tab-pane fade show active" id="solutions" role="tabpanel" aria-labelledby="solutions-tab">
					<div class="mb-4">
						<?php echo $this->renderWidget('core::notification'); ?>
					</div>
					<div class="d-flex">
						<div class="w-75">
							<?php echo $this->render('starter.list.filter', []); ?>
						</div>
						<div class="w-25 text-end">
							<button class="btn btn-primary" id="install-theme" data-bs-toggle="modal" data-bs-target="#upload_package">Upload Package</button>
						</div>
					</div>
					<div class="solution-list row">
						<?php while ($this->list->hasRow()) echo $this->render('starter.list.row'); ?>
					</div>
				</div>
				<div class="tab-pane fade" id="themes" role="tabpanel" aria-labelledby="themes-tab">
					<div class="d-flex justify-content-end mt-3">
						<div class="text-end">
							<button class="btn btn-primary" id="install-theme" data-bs-toggle="modal" data-bs-target="#uploadTheme">Upload Theme</button>
						</div>
					</div>
					<div class="theme-list row mt-2">
						<?php while ($this->themes->hasRow()) echo $this->render('starter.theme.row'); ?>
					</div>
				</div>
				<div class="tab-pane fade" id="config" role="tabpanel" aria-labelledby="config-tab">
					<form action="<?php echo $this->link_config ?>" method="POST">
						<div class="row mt-3">
							<div class="col-6">
								<?php $this->ui->field('admin_theme'); ?>
							</div>
							<div class="col-6">
								<?php $this->ui->field('default_theme'); ?>
							</div>
						</div>
						<div class="row mt-4">
							<div class="col-12 text-center">
								<a href="<?php echo $this->url('starter'); ?>" class="btn btn-secondary me-3">Cancel</a>
								<button class="btn btn-primary">Save</button>
							</div>
						</div>
						<?php $this->ui->field('token'); ?>
					</form>
				</div>
			</div>
		</div>
	</div>
	<!-- Modal -->
	<div class="modal fade" id="staticBackdrop" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="staticBackdropLabel" aria-hidden="true">
		<div class="modal-dialog">
			<div class="modal-content">
			<div class="modal-header">
				<h2 class="modal-title" id="staticBackdropLabel"></h2>
				<!-- <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button> -->
			</div>
			<div class="progress">
				<div class="progress-bar progress-bar-striped progress-bar-animated" role="progressbar" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100" style="width: 0%"></div>
			</div>
			<div class="progess-status justify-content-center">
				<span id="progess-status"></span>
			</div>
			<div class="modal-body">
				<div id="modal-text"></div>
			</div>
			<div class="modal-footer">
				
			</div>
			</div>
		</div>
	</div>

	<div class="modal fade" id="upload_package" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="staticBackdropLabel" aria-hidden="true" style="text-align: left;">
		<div class="modal-dialog modal-dialog-centered " style="max-width: 600px;">
			<div class="modal-content container px-3">
				<div class="modal-header">
					<h4 class="modal-title fw-bold" id="popupNoteTypeLabel">Install Package</h4>
					<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
				</div>
				<div class="modal-body">
					<p id="error-text" style="color: red;"></p>
					<div class="row px-0">
						<div class="mb-3 col-12 mx-auto">
							<label for="package_upload" class="form-label fw-bold">Choose package file (.zip)</label>
							<input class="form-control" type="file" id="package_upload" name="package_upload">
						</div>
						<div class="col-12 mx-auto text-center">
							<label for="package_upload" class="mb-0 form-label fw-bold">OR</label>
						</div>
						<div class="mb-3 col-12 mx-auto">
							<label for="package_upload" class="form-label fw-bold">Enter URL package (.zip)</label>
							<input class="form-control" type="text" id="package_url" name="package_url">
						</div>
					</div>
					<div class="row g-3 align-items-center m-0">
						<div class="modal-footer">
							<div class="row">
								<div class="col-6 text-end pe-0">
									<button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
								</div>
								<div class="col-6 text-end pe-0">
									<button type="button" id="submit-upload" class="btn btn-outline-success">Install</button>
								</div>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
	<div class="modal fade" id="uploadTheme" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="staticBackdropLabel" aria-hidden="true" style="text-align: left;">
		<div class="modal-dialog modal-dialog-centered " style="max-width: 600px;">
			<div class="modal-content container px-3">
				<div class="modal-header">
					<h4 class="modal-title fw-bold" id="popupNoteTypeLabel">Install Theme</h4>
					<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
				</div>
				<div class="modal-body">
					<p id="error-theme-text" style="color: red;"></p>
					<div class="row px-0">
						<div class="mb-3 col-12 mx-auto">
							<label for="theme_upload" class="form-label fw-bold">Choose theme file (.zip)</label>
							<input class="form-control" type="file" id="theme_upload" name="theme_upload">
						</div>
						<div class="col-12 mx-auto text-center">
							<label for="theme_upload" class="mb-0 form-label fw-bold">OR</label>
						</div>
						<div class="mb-3 col-12 mx-auto">
							<label for="theme_uplaod" class="form-label fw-bold">Enter URL theme (.zip)</label>
							<input class="form-control" type="text" id="theme_url" name="theme_url">
						</div>
					</div>
					<div class="row g-3 align-items-center m-0">
						<div class="modal-footer">
							<div class="row">
								<div class="col-6 text-end pe-0">
									<button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
								</div>
								<div class="col-6 text-end pe-0">
									<button type="button" id="submit-theme-upload" class="btn btn-outline-success">Install</button>
								</div>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
	<!-- Toast Message -->
	<div class="position-fixed" style="z-index: 11; position: absolute;top:120px;left:156px;">
		<div class="toast message-toast toast-notification">
			<div class="toast-message d-flex message-body">
				<div class="toast-body">
					
				</div>
				<button type="button" class="btn-close me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
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
<?php echo $this->render('starter.theme.javascript', []); ?>
<?php echo $this->render('starter.list.css', []); ?>
