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
        $package = isset($urlVars['code']) ? $urlVars['code'] : '';
        $type = $this->request->post->get('type', '', 'string');
        $solution = $this->request->post->get('solution', '', 'string');
        $require = $this->request->post->get('require', '', 'string');
        $data = [
            'solution' => $solution ? $solution : $package,
            'type' => $type ? $type : 'solution',
            'require' => $require ? $require : '',
            'package' => $package,
            'action' => $this->request->post->get('action', '', 'string'),
        ];

        $start_time = microtime(true);
        $try = $this->StarterModel->prepare_install($data);
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
        $code = isset($urlVars['code']) ? $urlVars['code'] : '';
        $type = $this->request->post->get('type', '', 'string');
        $solution = $this->request->post->get('solution', '', 'string');

        $start_time = microtime(true);
        $try = $this->StarterModel->prepare_uninstall($code, $type, $solution);
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
        $action = $this->request->post->get('action', '', 'string');
        if ($action && $action == 'upload_file')
        {
            $package = $_FILES['package'];
            $file_tmp = $_FILES['package']['tmp_name'];
            $tmp = explode('.',$_FILES['package']['name']);
            $file_ext = strtolower(end($tmp));
            $expensions = array('zip');
        
            if(in_array($file_ext, $expensions) === false){
                $this->set('status', 'failed');
                $this->set('message', 'Only .zip files are allowed.');
                return;
            }
            
            if($_FILES['package']['size'] > 20 * 1024 * 1024) {
                $this->set('status', 'failed');
                $this->set('message', 'File size should be less than 20MB.');
                return;
            }

            move_uploaded_file($file_tmp, SPT_STORAGE_PATH. "solution.zip");

            $package_path = SPT_STORAGE_PATH. "solution.zip";
            $upload = true;
        } else {
            $package_path = $this->request->post->get('package', '', 'string');
            $upload = false;
        }

        $start_time = microtime(true);
        $try = $this->StarterModel->unzip_solution($package_path, $upload);
        $end_time = microtime(true);
        $status = $try['success'] ? 'success' : 'failed';

        $this->set('status', $status);
        $this->set('data', $try['data']);
        $this->set('message', $try['message']);
        $this->set('info', $try['info']);
        $this->set('time', $end_time - $start_time);
        return;
    }

    public function install_plugins()
    {
        // get input data
        $data = [
            'package_path' => $this->request->post->get('package_path', '', 'string'),
            'solution' => $this->request->post->get('solution', '', 'string'),
            'type' => $this->request->post->get('type', '', 'string'),
            'package' => $this->request->post->get('package', '', 'string'),
            'action' => $this->request->post->get('action', '', 'string'),
        ];

        $start_time = microtime(true);
        $try = $this->StarterModel->install_plugins($data);
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
            'type' => $this->request->post->get('type', '', 'string'),
            'package' => $this->request->post->get('package', '', 'string'),
            'solution' => $this->request->post->get('solution', '', 'string')
        ];

        $start_time = microtime(true);
        $try = $this->StarterModel->uninstall_plugins($data);
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
        $upload = $this->request->post->get('upload', '', 'boolean');
        $start_time = microtime(true);
        $try = $this->StarterModel->generate_data_structure($upload);
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