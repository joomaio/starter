<?php

namespace App\devtool\starter\registers;

use SPT\Application\IApp;

class Routing
{
    public static function registerEndpoints()
    {
        return [
            'starter' => [
                'fnc' => [
                    'get' => 'starter.starter.list',
                    'post' => 'starter.starter.list',
                ],
            ],
            'starter/login' => [
                'fnc' => [
                    'get' => 'starter.starter.gate',
                    'post' => 'starter.starter.login',
                ],
            ],
            'starter/install' => [
                'fnc' => [
                    'post' => 'starter.starter.install',
                ],
            ],
            'starter/uninstall' => [
                'fnc' => [
                    'post' => 'starter.starter.uninstall',
                ],
            ],
        ];
    }
}
