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
                'parameters' => ['solution_code'],
                'restApi' => true,
                'format' => 'json',
            ],
            'starter/uninstall' => [
                'fnc' => [
                    'post' => 'starter.starter.uninstall',
                ],
                'parameters' => ['solution_code'],
                'restApi' => true,
                'format' => 'json',
            ],
            'starter/prepare_install' => [
                'fnc' => [
                    'post' => 'starter.starter.prepare_install',
                ],
                'parameters' => ['solution_code'],
                'restApi' => true,
                'format' => 'json',
            ],
            'starter/prepare_uninstall' => [
                'fnc' => [
                    'post' => 'starter.starter.prepare_uninstall',
                ],
                'parameters' => ['solution_code'],
                'restApi' => true,
                'format' => 'json',
            ],
            'starter/download_solution' => [
                'fnc' => [
                    'post' => 'starter.starter.download_solution',
                ],
                'restApi' => true,
                'format' => 'json',
            ],
            'starter/unzip_solution' => [
                'fnc' => [
                    'post' => 'starter.starter.unzip_solution',
                ],
                'restApi' => true,
                'format' => 'json',
            ],
            'starter/install_plugins' => [
                'fnc' => [
                    'post' => 'starter.starter.install_plugins',
                ],
                'restApi' => true,
                'format' => 'json',
            ],
            'starter/uninstall_plugins' => [
                'fnc' => [
                    'post' => 'starter.starter.uninstall_plugins',
                ],
                'restApi' => true,
                'format' => 'json',
            ],
            'starter/generate_data_structure' => [
                'fnc' => [
                    'post' => 'starter.starter.generate_data_structure',
                ],
                'restApi' => true,
                'format' => 'json',
            ],
            'starter/composer_update' => [
                'fnc' => [
                    'post' => 'starter.starter.composer_update',
                ],
                'restApi' => true,
                'format' => 'json',
            ],
        ];
    }
}
