<?php
namespace App\plugins\devtool\starter\controllers;

use SPT\Response;
use SPT\Web\ControllerMVVM;

class starter extends ControllerMVVM
{
    public function list()
    {
        $access_key = $this->request->get->get('access_key', '', 'string');
        $user = $this->session->get('user_starter', '');
        if ($user)
        {
            return $this->app->redirect(
                $this->router->url('starter/login?access_key='. $access_key)
            );
        }

        $this->app->set('page', 'backend-full');
        $this->app->set('format', 'html');
        $this->app->set('layout', 'start.list');
    }

    public function login()
    {
        if ($user)
        {
            return $this->app->redirect(
                $this->router->url('starter/login?access_key='. $access_key)
            );
        }

        $this->app->set('page', 'backend-full');
        $this->app->set('format', 'html');
        $this->app->set('layout', 'start.list');
    }
}