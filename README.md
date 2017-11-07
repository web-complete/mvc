# Micro MVC

[![Build Status](https://travis-ci.org/web-complete/mvc.svg?branch=master)](https://travis-ci.org/web-complete/mvc)
[![Coverage Status](https://coveralls.io/repos/github/web-complete/mvc/badge.svg?branch=master)](https://coveralls.io/github/web-complete/mvc?branch=master)
[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/web-complete/mvc/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/web-complete/mvc/?branch=master)
[![Latest Stable Version](https://poser.pugx.org/web-complete/mvc/version)](https://packagist.org/packages/web-complete/mvc)
[![License](https://poser.pugx.org/web-complete/mvc/license)](https://packagist.org/packages/web-complete/mvc)

Example index.php
```php
<?php

require __DIR__ . '/../../vendor/autoload.php';

defined('ENV')
or define('ENV', in_array(@$_SERVER['REMOTE_ADDR'], ['127.0.0.1', '::1'], false) ? 'dev' : 'prod');

$config = require __DIR__ . '/../../admin/config/config.php';
$application = new \WebComplete\mvc\Application($config);
$front = $application->getContainer()->get(\WebComplete\mvc\front\FrontController::class);
$front->dispatch()->send();
```

Example config.php
```php
<?php

return [
    'aliases' => [
        '@app' => \dirname(__DIR__ . '/../app'),
        '@web' => \dirname(__DIR__ . '/../web'),
    ],
    'routes' => [
        ['GET', '/post/list', [\app\controllers\PostController::class, 'actionList']],
        ['POST', '/post/update', [\app\controllers\PostController::class, 'actionUpdate']],
    ],
    'cubesLocations' => [
        '@app/cubes',
    ],
    'definitions' => [
        'errorController' => \DI\object(\app\controllers\ErrorController::class),
    ]
];
```

Example controller.php
```php
<?php

namespace app\controllers;

class SomeController extends AbstractController
{

    protected $layout = '@app/views/layouts/main.php';

    public function index()
    {
        return $this->responseHtml('@app/views/some/index.php', ['name' => 'World']);
    }
}
```