<?php
/**
 * SPT software - ViewModel
 * 
 * @project: https://github.com/smpleader/spt-boilerplate
 * @author: Pham Minh - smpleader
 * @description: Just a basic viewmodel
 * 
 */
namespace App\plugins\report\viewmodels; 

use SPT\Web\Gui\Form;
use SPT\Web\Gui\Listing;
use SPT\Web\ViewModel;

class AdminDiagrams extends ViewModel
{
    public static function register()
    {
        return [
            'layout'=>[
                'backend.report.list',
                'backend.report.list.row',
                'backend.report.list.filter'
            ]
        ];
    }
    
    public function list()
    {
        $filter = $this->filter()['form'];

        $limit  = $filter->getField('limit')->value;
        $sort   = $filter->getField('sort')->value;
        $search = trim($filter->getField('search')->value);
        $status = $filter->getField('status')->value;
        $page = $this->state('page', 1, 'int', 'get', 'report.page');
        if ($page <= 0) $page = 1;

        $where = [];
        

        if( !empty($search) )
        {
            $where[] = "(`title` LIKE '%". $search ."%') ";
        }
        
        if(is_numeric($status))
        {
            $where[] = '`status`='. $status;
        }

        $start  = ($page-1) * $limit;
        $sort = $sort ? $sort : 'title asc';

        $result = $this->ReportEntity->list( $start, $limit, $where, $sort);
        $total = $this->ReportEntity->getListTotal();
        if (!$result)
        {
            $result = [];
            $total = 0;
            if( !empty($search) )
            {
                $this->session->set('flashMsg', 'Not Found Report');
            }
        }

        $types = $this->ReportModel->getTypes();

        foreach($result as &$item)
        {
            $item['type'] = isset($types[$item['type']]) ? $types[$item['type']] : $item['type'];
            $user_tmp = $this->UserEntity->findByPK($item['created_by']);
            $item['auth'] = $user_tmp ? $user_tmp['name'] : '';
            $item['created_at'] = $item['created_at'] && $item['created_at'] != '0000-00-00 00:00:00' ? date('d-m-Y', strtotime($item['created_at'])) : '';
            
            $assigns = $item['assignment'] ? json_decode($item['assignment']) : [];
            $assign_tmp = [];
            $selected_tmp = [];
            foreach($assigns as $assign)
            {
                $user_tmp = $this->UserEntity->findByPK($assign);
                if ($user_tmp)
                {
                    $assign_tmp[] = $user_tmp['name'];
                    $selected_tmp[] = [
                        'id' => $assign,
                        'name' => $user_tmp['name'],
                    ];
                }
            }
            $item['assign'] = implode(', ', $assign_tmp);
            $item['assignment'] = json_encode($selected_tmp);
        }

        $limit = $limit == 0 ? $total : $limit;
        $list   = new Listing($result, $total, $limit, $this->getColumns() );
        return [
            'list' => $list,
            'page' => $page,
            'start' => $start,
            'types' => $types,
            'sort' => $sort,
            'user_id' => $this->user->get('id'),
            'url' => $this->router->url(),
            'link_list' => $this->router->url('reports'),
            'link_new_form' => $this->router->url('new-report'),
            'link_form' => $this->router->url('report/detail'),
            'title_page' => 'Report',
            'token' => $this->token->value(),
        ];
    }

    public function getColumns()
    {
        return [
            'num' => '#',
            'title' => 'Title',
            'status' => 'Status',
            'created_at' => 'Created at',
            'col_last' => ' ',
        ];
    }

    protected $_filter;
    public function filter()
    {
        if( null === $this->_filter):
            $data = [
                'search' => $this->state('search', '', '', 'post', 'report.search'),
                'status' => $this->state('status', '','', 'post', 'report.status'),
                'limit' => $this->state('limit', 20, 'int', 'post', 'report.limit'),
                'sort' => $this->state('sort', '', '', 'post', 'report.sort')
            ];

            $filter = new Form($this->getFilterFields(), $data);

            $this->_filter = $filter;
        endif;

        return ['form' => $this->_filter];
    }

    public function getFilterFields()
    {
        return [
            'search' => ['text',
                'default' => '',
                'showLabel' => false,
                'formClass' => 'form-control h-full input_common w_full_475',
                'placeholder' => 'Search..'
            ],
            'status' => ['option',
                'default' => '1',
                'formClass' => 'form-select',
                'options' => [
                    ['text' => '--', 'value' => ''],
                    ['text' => 'Show', 'value' => '1'],
                    ['text' => 'Hide', 'value' => '0'],
                ],
                'showLabel' => false
            ],
            'limit' => ['option',
                'formClass' => 'form-select',
                'default' => 20,
                'options' => [
                    ['text' => '20', 'value' => 20],
                    ['text' => '50', 'value' => 50],
                    ['text' => 'All', 'value' => 0],
                ],
                'showLabel' => false
            ],
            'sort' => ['option',
                'formClass' => 'form-select',
                'default' => 'title asc',
                'options' => [
                    ['text' => 'Title ascending', 'value' => 'title asc'],
                    ['text' => 'Title descending', 'value' => 'title desc'],
                ],
                'showLabel' => false
            ]
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
