<?php
namespace App\devtool\starter\viewmodels;

use SPT\Web\Gui\Form;
use SPT\Web\Gui\Listing;
use SPT\Web\ViewModel;

class Starter extends ViewModel
{
    public static function register()
    {
        return [
            'layout' => [
                'starter.list',
                'starter.list.row',
                'starter.list.filter',
                'starter.login'
            ],
        ];
    }

    public function list()
    {
        $filter = $this->filter()['form'];
        $search = trim($filter->getField('search')->value);

        $solutions = $this->StarterModel->getSolutions();
        $tmp = [];
        if ($search) {
            foreach ($solutions as $item) 
            {
                if (strpos($item['name'], $search) !== false || strpos($item['description'], $search) !== false) 
                {
                    $tmp[] = $item;
                }
            }

            $solutions = $tmp;
        }

        if (!$tmp && $search) {
            $this->session->set('flashMsg', 'Solution not found');
        }
        $buttons = $this->StarterModel->loadButton();

        $list = new Listing(array_values($solutions), count($solutions), 0, $this->getColumns());

        return [
            'url' => $this->router->url(),
            'list' => $list,
            'buttons' => $buttons,
            'link_list' => $this->router->url('starter'),
            'title_page' => 'Starter',
            'link_install' => $this->router->url('starter/install'),
            'link_uninstall' => $this->router->url('starter/uninstall'),
            'link_prepare_install' => $this->router->url('starter/prepare-install'),
            'link_prepare_uninstall' => $this->router->url('starter/prepare-uninstall'),
            'link_download_solution' => $this->router->url('starter/download-solution'),
            'link_unzip_solution' => $this->router->url('starter/unzip-solution'),
            'link_install_plugins' => $this->router->url('starter/install-plugins'),
            'link_uninstall_plugins' => $this->router->url('starter/uninstall-plugins'),
            'link_generate_data_structure' => $this->router->url('starter/generate-data-structure'),
            'link_composer_update' => $this->router->url('starter/composer-update'),
            'token' => $this->token->value(),
        ];
    }

    public function getColumns()
    {
        return [
            'name' => '#',
            'name' => 'Title',
            'col_last' => ' ',
        ];
    }

    protected $_filter;
    public function filter()
    {
        if (null === $this->_filter):
            $data = [
                'search' => $this->state('search', '', '', 'post', 'filter.search'),
                'limit' => $this->state('limit', 10, 'int', 'post', 'filter.limit'),
                'sort' => $this->state('sort', '', '', 'post', 'filter.sort')
            ];
            $filter = new Form($this->getFilterFields(), $data);

            $this->_filter = $filter;
        endif;

        return ['form' => $this->_filter];
    }

    public function getFilterFields()
    {
        return [
            'search' => [
                'text',
                'default' => '',
                'showLabel' => false,
                'formClass' => 'form-control h-full input_common w_full_475',
                'placeholder' => 'Search..'
            ],
        ];
    }

    public function row($layoutData, $viewData)
    {
        $row = $viewData['list']->getRow();
        return [
            'item' => $row,
            'index' => $viewData['list']->getIndex(),
        ];
    }

    public function login()
    {
        return [
            'url' => $this->router->url()
        ];
    }

}
