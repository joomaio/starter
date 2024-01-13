<?php

namespace App\devtool\starter\registers;

use SPT\Application\IApp;

class Cli
{
    public static function registerCommands()
    {
        return [
            'install' => [
                'description' => "Install solution. Example: php cli.php install solution-name",
                'fnc' => 'starter.install.install'
            ],
            'uninstall' => [
                'description' => "Uninstall solution. Example: php cli.php uninstall solution-name",
                'fnc' => 'starter.uninstall.uninstall'
            ],
            'solution-list' => [
                'description' => "Show solution list",
                'fnc' => 'starter.install.list'
            ],
            'data-minimum' => [
                'description' => "Install data minimum.",
                'fnc' => 'starter.database.generatedata'
            ],
        ];
    }
}
