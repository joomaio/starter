<?php

namespace App\devtool\starter\registers;

use SPT\Application\IApp;

class Cli
{
    public static function registerCommands()
    {
        return [
            'install' => [
                'description' => "Install solution. Example: php cli.php install solution-link(solution-path)",
                'fnc' => 'starter.cli.install'
            ],
            'uninstall' => [
                'description' => "Uninstall solution. Example: php cli.php uninstall solution-link(solution-path)",
                'fnc' => 'starter.cli.uninstall'
            ],
            'solution-list' => [
                'description' => "Show solution list",
                'fnc' => 'starter.cli.list'
            ],
            'data-minimum' => [
                'description' => "Install data minimum.",
                'fnc' => 'starter.cli.generatedata'
            ],
        ];
    }
}
