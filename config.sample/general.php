<?php  defined('APP_PATH') or die('Invalid config');

return [ 
    'subpath' => '',
    'defaultTheme' => 'blue',
    'adminTheme' => 'admin',
    'secrect' => 'sid',
    'expireSessionDuration' => 60,
    'homeEndpoint' => [
        'fnc' => [
            'get' => 'pnote.pnote.dashboard'
        ]
    ],
    'redirectAfterLogin' => '',
];
