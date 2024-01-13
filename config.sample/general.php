<?php  defined('APP_PATH') or die('Invalid config');

return [ 
    'subpath' => '',
    'defaultTheme' => '',
    'adminTheme' => 'admin',
    'secrect' => 'sid',
    'expireSessionDuration' => 60,
    'redirectAfterLogin' => '',
    'homeEndpoint' => [
        'fnc' => [
            'get' => 'pnote.pnote.dashboard'
        ],
    ],
];
