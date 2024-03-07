<?php
namespace App\devtool\starter\controllers;

use SPT\Response;
use SPT\Web\ControllerMVVM;

class theme extends ControllerMVVM
{
    public function install()
    {
        $starter = $this->config->starter;

        if (
            !$starter || (!isset($starter['access_key']) || $starter['access_key'] == '') ||
            (!isset($starter['username']) || $starter['username'] == '') || (!isset($starter['password']) || $starter['password'] == '')
        ) {
            $this->app->raiseError('Invalid request!');
        }

        $access_key = $this->request->get->get('access_key', '', 'string');
        $user = $this->StarterAccessModel->user();
        if (!$user) {
            return $this->app->redirect(
                $this->router->url('starter/login?access_key=' . $access_key)
            );
        }

        $this->app->set('format', 'html');
        $this->app->set('layout', 'starter.list');
    }

    public function uninstall()
    {
        $try = 
    }
}