<?php

namespace App\plugins\devtool\starter\registers;

use SPT\Application\IApp;

class Routing
{
    public static function registerEndpoints()
    {
        return [
            'starter' => [
                'fnc' => [
                    'get' => 'starter.starter.list',
                ],
            ],
        ];
    }
}
