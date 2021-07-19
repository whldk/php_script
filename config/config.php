<?php
return [
    'access' => [
        '*' => []
    ],
    'components' => [
        'core_db',  'db', 'route', 'request', 'response', 'Logger'
    ],
    'core_db' => [
        'class' => '\vendor\db\Db',
        'dsn' => 'mysql:host=127.0.0.1;dbname=otherpool;charset=UTF8',
        'username' => 'root',
        'passwd' => '123456',
        'logger' => [
            'enable_log' => false,
            'logFile' => DIR_LOG . DIRECTORY_SEPARATOR . 'core.log'
        ],
    ],
    'db' => [
        'class' => '\vendor\db\Db',
        'dsn' => 'mysql:host=127.0.0.1;dbname=log;charset=UTF8',
        'username' => 'root',
        'passwd' => '123456',
        'logger' => [
            'enable_log' => false,
            'logFile' => DIR_LOG . DIRECTORY_SEPARATOR . 'log.log'
        ],
    ],
    'request' => [
        'enableCookieValidation' => true,
        'cookieValidationKey' => 'kjiefJNKK:_(*&^%@!&_{+?:!',
        'validateCookies' => ['php_script']
    ]
];
