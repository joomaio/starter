<?php

/**
 * SPT software - ViewModel
 * 
 * @project: https://github.com/smpleader/spt
 * @author: Pham Minh - smpleader
 * @description: A simple View Model
 * 
 */

namespace App\plugins\report\viewmodels;

use SPT\Web\ViewModel;
use SPT\Web\Gui\Form;

class AdminDiagram extends ViewModel
{
    public static function register()
    {
        return [
            'layout'=>'backend.report.form',
        ];
    }
    
    public function form()
    {
        $data = [
            'title' => '',
            'assignment' => [],
        ];
        $form = new Form($this->getFormFields(), $data);

        return [
            'form' => $form,
            'link_form' => $this->router->url('report'),
            'link_search' => $this->router->url('report/find-user'),
            'data' => $data,
        ];
        
    }

    public function getFormFields()
    {
        $fields = [
            'title' => [
                'text',
                'showLabel' => false,
                'placeholder' => 'Title',
                'formClass' => 'form-control mb-3',
                'required' => 'required',
            ],
            'assignment' => [
                'option',
                'type' => 'multiselect',
                'formClass' => 'form-select',
                'options' => [],
                'showLabel' => false,
                'placeholder' => 'Users',
                'formClass' => 'form-control',
            ],
            'token' => ['hidden',
                'default' => $this->token->value(),
            ],
        ];

        return $fields;
    }
}
