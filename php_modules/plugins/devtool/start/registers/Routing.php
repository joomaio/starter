<?php

namespace App\plugins\devtool\start\registers;

use SPT\Application\IApp;

class Routing
{
    public static function registerEndpoints()
    {
        return [
            '' => [
                'fnc' => [
                    'get' => 'starter.starter.list',
                ],
            ],
        ];
    }
}
