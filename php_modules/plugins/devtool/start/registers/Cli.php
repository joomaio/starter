<?php

namespace App\plugins\devtool\start\registers;

use SPT\Application\IApp;

class Cli
{
    public static function registerCommands()
    {
        return [
            'install' => [
                'description' => "Install solution. Example: php cli.php install solution-name",
                'fnc' => 'start.install.install'
            ],
            // Todo
            // 'uninstall' => [
            //     'description' => "Uninstall solution. Example: php cli.php uninstall solution-name",
            //     'fnc' => 'start.uninstall.uninstall'
            // ],
            'solution-list' => [
                'description' => "Show solution list",
                'fnc' => 'start.install.list'
            ],
            'data-minium' => [
                'description' => "Install data minium.",
                'fnc' => 'start.database.generatedata'
            ],
        ];
    }
}
