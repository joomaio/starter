<?php
namespace App\devtool\starter\controllers;

use SPT\Response;
use SPT\Web\ControllerMVVM;

class config extends ControllerMVVM
{
    public function update()
    {
        $data = [
            'admin_theme' => $this->request->post->get('admin_theme', '', 'string'),
            'default_theme' => $this->request->post->get('default_theme', '', 'string'),
        ];

        $try = $this->StarterModel->updateConfig($data);
        $this->session->set('flashMsg', $try ? 'Update successfully' : $this->StarterModel->getError());
        
        return  $this->app->redirect(
            $this->router->url('starter')
        );
    }
}