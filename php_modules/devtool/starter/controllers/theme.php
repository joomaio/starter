<?php
namespace App\devtool\starter\controllers;

use SPT\Response;
use SPT\Web\ControllerMVVM;

class theme extends ControllerMVVM
{
    public function install()
    {
        $data = [
            'file' => $this->request->file->get('file_upload', [], 'array'),
            'url' => $this->request->post->get('url', '', 'string'),
        ];

        $step = $this->request->post->get('step', 0, 'int');
        $totalStep = $this->request->post->get('totalStep', 0, 'int');
        $timestamp = $this->request->post->get('timestamp', '', 'string');

        $try = $this->ThemeModel->install($data, $step, $timestamp);

        $status = $try ? true : false;
        if(!$try || (isset($try['status']) && !$try['status']))
        {
            $status = false;
        }

        $totalStep = isset($try['totalStep']) ? (int) $try['totalStep'] : $totalStep;
        $step = isset($try['step']) ? (int) $try['step'] : $step;

        $title = '';
        $message = '';
        switch ($step) {
            case 1:
                $title = 'Check file install';
                $message = 'Check file done';
                break;
            case 2:
                $title = 'Unzip file install';
                $message = 'Unzip file done';
                break;
            case 3:
                $title = 'Install theme';
                $message = 'Install done!';
                break;
            default:
                # code...
                break;
        }

        $this->set('status', $status);
        $this->set('totalStep', $totalStep);
        $this->set('timestamp', isset($try['timestamp']) ? $try['timestamp'] : $timestamp );
        $this->set('step', $step);
        $this->set('title', $step. '/'. $totalStep . ' '. $title);
        $this->set('message', $status ? $message : $this->ThemeModel->getError());
        return;
    }

    public function uninstall()
    {
        $theme = $this->request->post->get('theme', '', 'string');
        $step = $this->request->post->get('step', 0, 'int');
        $totalStep = $this->request->post->get('totalStep', 0, 'int');
        $timestamp = $this->request->post->get('timestamp', '', 'string');

        $try = $this->ThemeModel->uninstall($theme, $step, $timestamp);

        $status = $try ? true : false;
        if(!$try || (isset($try['status']) && !$try['status']))
        {
            $status = false;
        }

        $totalStep = isset($try['totalStep']) ? (int) $try['totalStep'] : $totalStep;
        $step = isset($try['step']) ? (int) $try['step'] : $step;

        $title = '';
        $message = '';
        switch ($step) {
            case 1:
                $title = 'Check valid uninstall theme';
                $message = 'Check valid uninstall theme done!';
                break;
            case 2:
                $title = 'Uninstall theme';
                $message = 'Uninstall theme done!';
                break;
            default:
                # code...
                break;
        }

        $this->set('status', $status);
        $this->set('totalStep', $totalStep);
        $this->set('timestamp', isset($try['timestamp']) ? $try['timestamp'] : $timestamp );
        $this->set('step', $step);
        $this->set('title', $step. '/'. $totalStep . ' '. $title);
        $this->set('message', $status ? $message : $this->ThemeModel->getError());
        return;
    }
}