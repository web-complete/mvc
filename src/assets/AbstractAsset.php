<?php

namespace WebComplete\mvc\assets;

abstract class AbstractAsset
{

    public $useLinks = true;

    public $publish = true;

    /**
     * @return string
     */
    abstract public function getBasePath(): string;

    /**
     * @return array
     */
    abstract public function css(): array;

    /**
     * @return array
     */
    abstract public function js(): array;

    /**
     * @return string
     */
    public function getHash(): string
    {
        return \md5(static::class);
    }
}
