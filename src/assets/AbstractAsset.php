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
     * @return array
     */
    final public function internalCss(): array
    {
        return $this->getInternal($this->css());
    }

    /**
     * @return array
     */
    final public function externalCss(): array
    {
        return $this->getExternal($this->css());
    }

    /**
     * @return array
     */
    final public function internalJs(): array
    {
        return $this->getInternal($this->js());
    }

    /**
     * @return array
     */
    final public function externalJs(): array
    {
        return $this->getExternal($this->js());
    }

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

    /**
     * @param array $files
     *
     * @return array
     */
    private function getInternal(array $files): array
    {
        $result = [];
        foreach ($files as $file) {
            if ($this->isInternal($file)) {
                $result[] = $file;
            }
        }
        return $result;
    }

    /**
     * @param array $files
     *
     * @return array
     */
    private function getExternal(array $files): array
    {
        $result = [];
        foreach ($files as $file) {
            if (!$this->isInternal($file)) {
                $result[] = $file;
            }
        }
        return $result;
    }

    /**
     * @param string $file
     *
     * @return bool
     */
    private function isInternal(string $file): bool
    {
        return (false === \strpos($file, 'http') && false === \strpos($file, '//'));
    }
}
