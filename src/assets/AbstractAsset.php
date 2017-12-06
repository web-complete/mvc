<?php

namespace WebComplete\mvc\assets;

abstract class AbstractAsset
{

    public $useLinks = true;

    public $publish = true;

    /** @var AbstractAsset[] */
    protected $assetsBefore = [];

    /** @var AbstractAsset[] */
    protected $assetsAfter = [];

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

    /**
     * @return array
     */
    public function getAssetsBefore(): array
    {
        return $this->assetsBefore;
    }

    /**
     * @return array
     */
    public function getAssetsAfter(): array
    {
        return $this->assetsAfter;
    }

    /**
     * @param AbstractAsset $asset
     */
    public function addAssetBefore(AbstractAsset $asset)
    {
        $this->assetsBefore[\get_class($asset)] = $asset;
    }

    /**
     * @param AbstractAsset $asset
     */
    public function addAssetAfter(AbstractAsset $asset)
    {
        $this->assetsAfter[\get_class($asset)] = $asset;
    }
}
