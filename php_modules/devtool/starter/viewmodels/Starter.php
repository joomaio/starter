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
        $themes = $this->ThemeModel->getThemes();
        $themes = new Listing(array_values($themes), count($themes), 0, $this->getColumns());
        $form = new Form($this->getFormFields(), [
            'admin_theme' => $this->OptionModel->get('admin_theme', $this->app->cf('adminTheme')),
            'default_theme' => $this->OptionModel->get('default_theme', $this->app->cf('defaultTheme')),
        ]);

        return [
            'url' => $this->router->url(),
            'list' => $list,
            'form' => $form,
            'themes' => $themes,
            'buttons' => $buttons,
            'link_list' => $this->router->url('starter'),
            'title_page' => 'Starter',
            'link_config' => $this->router->url('starter/config'),
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
            'link_install_theme' => $this->router->url('starter/theme/install'),
            'link_uninstall_theme' => $this->router->url('starter/theme/uninstall'),
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

    public function getFormFields()
    {
        $themes = $this->ThemeModel->getThemes();
        $optional = [
            // ['text' => 'Select theme', 'value' => ''],
        ];
        foreach($themes as $key => $value)
        {
            $optional[] = [
                'text' => $value['title'],
                'value' => $key,
            ];
        }

        $fields = [
            'admin_theme' => ['option',
                'default' => '',
                'formClass' => 'form-select',
                'options' => $optional,
            ],
            'default_theme' => ['option',
                'default' => '',
                'formClass' => 'form-select',
                'options' => $optional,
            ],
            'token' => ['hidden',
                'default' => $this->token->value(),
            ],
        ];

        return $fields;
    }
}
