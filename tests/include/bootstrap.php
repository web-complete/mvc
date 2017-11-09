<?php

include_once __DIR__ . '/../../vendor/autoload.php';
include_once 'MvcTestCase.php';
include_once 'SomeController.php';
include_once 'app/controllers/ErrorController.php';
include_once 'app/controllers/SomeController.php';
include_once 'app/assets/Asset1.php';
include_once 'app/assets/Asset2.php';

if (!@mkdir(__DIR__ . '/app/cubes') && !is_dir(__DIR__ . '/app/cubes')) {
    throw new RuntimeException(sprintf('Directory "%s" was not created', __DIR__ . '/app/cubes'));
}