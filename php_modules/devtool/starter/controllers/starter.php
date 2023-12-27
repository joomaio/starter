<?php
namespace App\devtool\starter\controllers;

use SPT\Response;
use SPT\Web\ControllerMVVM;

class starter extends ControllerMVVM
{
    public function list()
    {
        $access_key = $this->request->get->get('access_key', '', 'string');
        $user = $this->StarterAccessModel->user();
        if (!$user)
        {
            return $this->app->redirect(
                $this->router->url('starter/login?access_key='. $access_key)
            );
        }

        // $this->app->set('page', 'backend-full');
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

        $this->app->set('page', 'backend-full');
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
        
        $this->session->set('flashMsg', $try ? 'Install Done!' : 'Error: '. $this->StarterModel->getError());
        
        return $this->app->redirect(
            $this->router->url('starter')
        );
    }

    public function uninstall()
    {
        $urlVars = $this->request->get('urlVars');
        $solution_code = isset($urlVars['solution_code']) ? $urlVars['solution_code'] : '';

        $try = $this->StarterModel->uninstall($solution_code);
        $this->session->set('flashMsg', $try ? 'Install Done!' : 'Error: '. $this->StarterModel->getError());
        
        return $this->app->redirect(
            $this->router->url('starter')
        );
    }
}