<?php defined('APP_PATH') or die('');
$this->theme->prepareAssets([
    'jquery',
    'bootstrap-css',
    'fontawesome-css',
    'admin-css',
    'style-css',
    // 'js-bootstrap',
    'js-backend',
]);
$content = $this->render($this->mainLayout);
$sidebar = $this->renderWidget('theme_advance::sidebar');
$ajax_load = isset($_GET['ajax_load']) ? $_GET['ajax_load'] : '';
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>SDM</title>

    <?php $this->theme->echo('css', $this->url()) ?>
    <?php $this->theme->echo('topJs', $this->url()) ?>
    <?php $this->theme->echo('inlineCss', $this->url()) ?>
</head>

<body data-theme="default" data-layout="fluid" data-sidebar-position="left" data-sidebar-layout="default">
    <?php if($ajax_load): ?>
        <div class="main">
            <?php echo $this->renderWidget('core::backend.header'); ?>
            <?php echo $content; ?>
        </div>
    <?php else : ?>
    <?php echo $sidebar ?>
    <div class="container d-none">
        <div class="row">
            <div class="col-12">
                <div class="main">
                    <?php echo $this->renderWidget('core::backend.header'); ?>
                    <?php echo $content; ?>
                </div>
            </div>
        </div>
    </div>
    <?php endif; ?>
    <?php $this->theme->echo('js', $this->url()) ?>
    <?php $this->theme->echo('inlineJs', $this->url()) ?>
    <script>
        if ( window !== window.parent ) 
        {
            $('body .container>.row>.col-12:first').removeClass('col-12');
            $('body .container>.row:first').removeClass('row');
            $('body .container:first').removeClass('d-none');
            $('body .container:first').removeClass('container');
        }
        else
        {
            $('body .container:first').removeClass('d-none');
        }
    </script>
</body>

</html>