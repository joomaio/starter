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

    public function prepare_install()
    {
        $urlVars = $this->request->get('urlVars');
        $solution_code = isset($urlVars['solution_code']) ? $urlVars['solution_code'] : '';

        $start_time = microtime(true);
        $try = $this->StarterModel->prepare_install($solution_code);
        $end_time = microtime(true);
        $status = $try['success'] ? 'success' : 'failed';

        $this->set('status', $status);
        $this->set('data', $try['data']);
        $this->set('message', $try['message']);
        $this->set('time', $end_time - $start_time);
        return;
    }

    public function prepare_uninstall()
    {
        $urlVars = $this->request->get('urlVars');
        $solution_code = isset($urlVars['solution_code']) ? $urlVars['solution_code'] : '';

        $start_time = microtime(true);
        $try = $this->StarterModel->prepare_uninstall($solution_code);
        $end_time = microtime(true);
        $status = $try['success'] ? 'success' : 'failed';

        $this->set('status', $status);
        $this->set('data', $try['data']);
        $this->set('message', $try['message']);
        $this->set('time', $end_time - $start_time);
        return;
    }

    public function download_solution()
    {
        // get input data
        $data = [
            'solution' => $this->request->post->get('solution', '', 'string')
        ];

        $start_time = microtime(true);
        $try = $this->StarterModel->download_solution($data['solution']);
        $end_time = microtime(true);
        $status = $try['success'] ? 'success' : 'failed';

        $this->set('status', $status);
        $this->set('data', $try['data']);
        $this->set('message', $try['message']);
        $this->set('time', $end_time - $start_time);
        return;
    }

    public function unzip_solution()
    {
        // get input data
        $data = [
            'solution_path' => $this->request->post->get('solution_path', '', 'string')
        ];

        $start_time = microtime(true);
        $try = $this->StarterModel->unzip_solution($data['solution_path']);
        $end_time = microtime(true);
        $status = $try['success'] ? 'success' : 'failed';

        $this->set('status', $status);
        $this->set('data', $try['data']);
        $this->set('message', $try['message']);
        $this->set('time', $end_time - $start_time);
        return;
    }

    public function install_plugins()
    {
        // get input data
        $data = [
            'solution_path' => $this->request->post->get('solution_path', '', 'string'),
            'solution' => $this->request->post->get('solution', '', 'string'),
        ];

        $start_time = microtime(true);
        $try = $this->StarterModel->install_plugins($data['solution_path'], $data['solution']);
        $end_time = microtime(true);
        $status = $try['success'] ? 'success' : 'failed';

        $this->set('status', $status);
        $this->set('data', '');
        $this->set('message', $try['message']);
        $this->set('time', $end_time - $start_time);
        return;
    }

    public function uninstall_plugins()
    {
        // get input data
        $data = [
            'solution' => $this->request->post->get('solution', '', 'string')
        ];

        $start_time = microtime(true);
        $try = $this->StarterModel->uninstall_plugins($data['solution']);
        $end_time = microtime(true);
        $status = $try['success'] ? 'success' : 'failed';

        $this->set('status', $status);
        $this->set('data', '');
        $this->set('message', $try['message']);
        $this->set('time', $end_time - $start_time);
        return;
    }

    public function generate_data_structure()
    {
        $start_time = microtime(true);
        $try = $this->StarterModel->generate_data_structure();
        $end_time = microtime(true);
        $status = $try['success'] ? 'success' : 'failed';

        $this->set('status', $status);
        $this->set('data', '');
        $this->set('message', $try['message']);
        $this->set('time', $end_time - $start_time);
        return;
    }

    public function composer_update()
    {
        // get input data
        $data = [
            'action' => $this->request->post->get('action', '', 'string')
        ];
        
        $start_time = microtime(true);
        $try = $this->StarterModel->composer_update($data['action']);
        $end_time = microtime(true);
        $status = $try['success'] ? 'success' : 'failed';

        $this->set('status', $status);
        $this->set('data', '');
        $this->set('message', $try['message']);
        $this->set('time', $end_time - $start_time);
        return;
    }
}