<?php

namespace Asset;

use WebComplete\mvc\assets\AbstractAsset;

class Asset3 extends AbstractAsset
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
        ];
    }

    /**
     * @return array
     */
    public function js(): array
    {
        return [
        ];
    }
}
