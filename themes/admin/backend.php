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
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>PNote</title>

    <?php $this->theme->echo('css', $this->url()) ?>
    <?php $this->theme->echo('topJs', $this->url()) ?>
    <?php $this->theme->echo('inlineCss', $this->url()) ?>
</head>

<body data-theme="default" class="d-none" data-layout="fluid" data-sidebar-position="left" data-sidebar-layout="default">
    <div class="wrapper">
        <?php echo $this->renderWidget('menu::backend.sidebar'); ?>
        <div class="main">
            <?php echo $this->renderWidget('core::backend.header'); ?>

            <main class="content p-0">
                <?php echo $content; ?>
            </main>

        </div>
    </div>
    <?php $this->theme->echo('js', $this->url()) ?>
    <?php $this->theme->echo('inlineJs', $this->url()) ?>
    <script>
        if ( window !== window.parent ) 
        {
            $('body #sidebar').addClass('d-none');
            $('body .main nav.navbar').addClass('d-none');
            $('#form_submit .widget-tag').addClass('d-none');
            $('#form_submit .widget-assignee').addClass('d-none');
            $('#form_submit .widget-history').addClass('d-none');
            $('body .main nav.navbar .sidebar-toggle').addClass('d-none');
        }
        $('body ').removeClass('d-none');
    </script>
</body>

</html>