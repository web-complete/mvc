<?php

namespace Asset;

use WebComplete\mvc\assets\AbstractAsset;

class Asset2 extends AbstractAsset
{

    public $publish = false;

    /**
     * @return string
     */
    public function getBasePath(): string
    {
        return '/';
    }

    /**
     * @return array
     */
    public function css(): array
    {
        return [
            'css/style.css',
            'http://www.webcomplete.ru/css/style2.css',
        ];
    }

    /**
     * @return array
     */
    public function js(): array
    {
        return [
            'js/script.js',
            '//www.webcomplete.ru/js/script2.js',
        ];
    }
}
