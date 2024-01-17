<?php
namespace App\devtool\starter\controllers;

use SPT\Response;
use SPT\Web\ControllerMVVM;

class starter extends ControllerMVVM
{
    public function list()
    {
        $starter = $this->config->starter;

        if (!$starter || (!isset($starter['access_key']) || $starter['access_key'] == '') || 
        (!isset($starter['username']) || $starter['username'] == '') || (!isset($starter['password']) || $starter['password'] == ''))
        {
            $this->app->raiseError('Invalid request!');  
        } 

        $access_key = $this->request->get->get('access_key', '', 'string');
        $user = $this->StarterAccessModel->user();
        if (!$user)
        {
            return $this->app->redirect(
                $this->router->url('starter/login?access_key='. $access_key)
            );
        }

        $this->app->set('format', 'html');
        $this->app->set('layout', 'starter.list');
    }

    public function gate()
    {
        $access_key = $this->request->get->get('access_key', '', 'string');
        $user = $this->StarterAccessModel->user();
        if ($user)
        {
            return $this->app->redirect(
                $this->router->url('starter')
            );
        }

        $this->app->set('format', 'html');
        $this->app->set('layout', 'starter.login');
    }

    public function login()
    {
        $access_key = $this->request->post->get('access_key', '', 'string');
        $user = $this->StarterAccessModel->user();
        if ($user)
        {
            return $this->app->redirect(
                $this->router->url('starter')
            );
        }

        $username = $this->request->post->get('username', '', 'string');
        $password = $this->request->post->get('password', '', 'string');

        $check = $this->StarterAccessModel->login($username, $password);
        
        $this->session->set('messa'.$access_key, ''); 
    
        return $this->app->redirect(
            $this->router->url($check ? 'starter' : 'starter/login?access_key='.$access_key )
        );
    }

    public function install()
    {
        $urlVars = $this->request->get('urlVars');
        $solution_code = isset($urlVars['solution_code']) ? $urlVars['solution_code'] : '';

        $try = $this->StarterModel->install($solution_code);
        $status = $try['success'] ? 'success' : 'failed';

        $this->set('status', $status);
        $this->set('data', '');
        $this->set('message', $try['message']);
        return;
    }

    public function uninstall()
    {
        $urlVars = $this->request->get('urlVars');
        $solution_code = isset($urlVars['solution_code']) ? $urlVars['solution_code'] : '';

        $try = $this->StarterModel->uninstall($solution_code);
        $status = $try['success'] ? 'success' : 'failed';

        $this->set('status', $status);
        $this->set('data', '');
        $this->set('message', $try['message']);
        return;
    }
}