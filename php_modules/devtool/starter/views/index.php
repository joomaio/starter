<?php defined( 'APP_PATH' ) or die('');
$this->theme->add( $this->url.'assets/js/jquery-3.6.0.min.js', '', 'jquery-3.6.0.min', 'top');
$this->theme->add( $this->url.'assets/font/fontawesome/css/fontawesome.min.css', '' , 'fontawesome-css');
$this->theme->add( $this->url.'assets/css/bootstrap5.min.css', '' , 'bootstrap-css');
$this->theme->add( $this->url.'assets/css/app.css', '', 'admin-css');
$this->theme->add( $this->url.'assets/js/bootstrap.bundle.min.js', [], 'bootstrap');

$content = $this->render($this->mainLayout);
?> 
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Starter</title>
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