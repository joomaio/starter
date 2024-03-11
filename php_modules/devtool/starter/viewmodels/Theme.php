<?php
namespace App\devtool\starter\viewmodels;

use SPT\Web\Gui\Form;
use SPT\Web\Gui\Listing;
use SPT\Web\ViewModel;

class Theme extends ViewModel
{
    public static function register()
    {
        return [
            'layout' => [
                'starter.theme.row',
            ],
        ];
    }

    public function row($layoutData, $viewData)
    {
        $row = $viewData['themes']->getRow();
        return [
            'item' => $row,
            'index' => $viewData['themes']->getIndex(),
        ];
    }
}
