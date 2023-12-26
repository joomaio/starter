<?php
namespace App\plugins\devtool\starter\controllers;

use SPT\Response;
use SPT\Web\ControllerMVVM;

class start extends ControllerMVVM
{
    public function list()
    {
        $this->app->set('page', 'backend');
        $this->app->set('format', 'html');
        $this->app->set('layout', 'start.list');
    }
}