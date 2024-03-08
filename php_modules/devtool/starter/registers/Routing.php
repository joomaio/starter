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
            'starter/config' => [
                'fnc' => [
                    'post' => 'starter.config.update',
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
            'starter/prepare-install' => [
                'fnc' => [
                    'post' => 'starter.starter.prepareInstall',
                ],
                'parameters' => ['code'],
                'restApi' => true,
                'format' => 'json',
            ],
            'starter/prepare-uninstall' => [
                'fnc' => [
                    'post' => 'starter.starter.prepareUninstall',
                ],
                'parameters' => ['code'],
                'restApi' => true,
                'format' => 'json',
            ],
            'starter/download-solution' => [
                'fnc' => [
                    'post' => 'starter.starter.downloadZipSolution',
                ],
                'restApi' => true,
                'format' => 'json',
            ],
            'starter/unzip-solution' => [
                'fnc' => [
                    'post' => 'starter.starter.unzipZipSolution',
                ],
                'restApi' => true,
                'format' => 'json',
            ],
            'starter/install-plugins' => [
                'fnc' => [
                    'post' => 'starter.starter.installPlugins',
                ],
                'restApi' => true,
                'format' => 'json',
            ],
            'starter/uninstall-plugins' => [
                'fnc' => [
                    'post' => 'starter.starter.uninstallPlugins',
                ],
                'restApi' => true,
                'format' => 'json',
            ],
            'starter/generate-data-structure' => [
                'fnc' => [
                    'post' => 'starter.starter.generateDataStructure',
                ],
                'restApi' => true,
                'format' => 'json',
            ],
            'starter/composer-update' => [
                'fnc' => [
                    'post' => 'starter.starter.composerUpdate',
                ],
                'restApi' => true,
                'format' => 'json',
            ],
            'starter/theme/install' => [
                'fnc' => [
                    'post' => 'starter.theme.install',
                ],
                'restApi' => true,
                'format' => 'json',
            ],
            'starter/theme/uninstall' => [
                'fnc' => [
                    'post' => 'starter.theme.uninstall',
                ],
                'restApi' => true,
                'format' => 'json',
            ],
        ];
    }
}
