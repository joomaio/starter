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
            'layout'=>[
                'starter.list',
                'starter.list.row',
                'starter.list.filter'
            ],
        ];
    }

    public function list()
    {
        $filter = $this->filter()['form'];
        $search = trim($filter->getField('search')->value);

        $solutions = $this->StarterModel->getSolutions();
        $list   = new Listing($solutions, count($solutions), 0, $this->getColumns());
        
        return [
            'url' => $this->router->url(),
            'link_list' =>  $this->router->url('starter'),
            'title_page' => 'Starter',
            'link_install' => $this->router->url('starter/install'),
            'link_uninstall' => $this->router->url('starter/uninstall'),
            'token' => $this->token->value(),
        ];
    }

    public function getColumns()
    {
        return [
            'num' => '#',
            'title' => 'Title',
            'created_at' => 'Created at',
            'col_last' => ' ',
        ];
    }

    protected $_filter;
    public function filter()
    {
        if (null === $this->_filter) :
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

}
