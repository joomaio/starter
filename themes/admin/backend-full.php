<?php defined( 'APP_PATH' ) or die('');
$this->theme->prepareAssets([
    'jquery',
    'fontawesome-css',
    'bootstrap-css',
    'admin-css',
    'js-bootstrap'
]);
$content = $this->render($this->mainLayout);
?> 
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pnote</title>
    <?php $this->theme->echo('css', $this->url()) ?> 
    <?php $this->theme->echo('topJs', $this->url()) ?>
    <?php $this->theme->echo('inlineCss', $this->url()) ?>
</head>
<body>
    <div class="wrap" fluid="xl">
        <div class="content-wrapper m-0">
            <?php echo $content ?>
        </div>
    </div>
<?php $this->theme->echo('js', $this->url()); ?>
<?php $this->theme->echo('inlineJs', $this->url()); ?>
</body>
</html>