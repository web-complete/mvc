<?php

namespace WebComplete\mvc\assets;

use Symfony\Component\Filesystem\Filesystem;
use WebComplete\mvc\exception\Exception;

class AssetManager
{

    const PRODUCTION_JS = 'asset.min.js';
    const PRODUCTION_CSS = 'asset.min.css';

    /**
     * @var AbstractAsset[]
     */
    protected $assets = [];
    /**
     * @var string
     */
    protected $webRoot;
    /**
     * @var string
     */
    protected $assetDirName;
    /**
     * @var Filesystem
     */
    protected $filesystem;
    protected $isProduction = false;

    /**
     * @param Filesystem $filesystem
     * @param string $webRoot
     * @param string $assetDirName
     * @param bool $isProduction
     */
    public function __construct(
        Filesystem $filesystem,
        string $webRoot,
        string $assetDirName,
        bool $isProduction = false
    ) {
        $this->filesystem = $filesystem;
        $this->webRoot = \rtrim($webRoot, '/');
        $this->assetDirName = $assetDirName;
        $this->isProduction = $isProduction;
    }

    /**
     * @param AbstractAsset $asset
     *
     * @throws \Symfony\Component\Filesystem\Exception\IOException
     */
    public function registerAsset(AbstractAsset $asset)
    {
        $assetClass = \get_class($asset);
        if (!isset($this->assets[$assetClass])) {
            foreach ($asset->getAssetsBefore() as $assetBefore) {
                $this->registerAsset($assetBefore);
            }

            if ($asset->publish) {
                $this->publishAsset($asset);
            }
            $this->assets[$assetClass] = $asset;
            foreach ($asset->getAssetsAfter() as $assetAfter) {
                $this->registerAsset($assetAfter);
            }
        }
    }

    /**
     * @param string $assetClass
     * @param $file
     *
     * @param bool $absolute
     *
     * @return string
     * @throws Exception
     */
    public function getPath(string $assetClass, $file, bool $absolute = false): string
    {
        if (!isset($this->assets[$assetClass])) {
            throw new Exception('Asset ' . $assetClass . ' is not registered');
        }

        return $absolute
            ? $this->webRoot . $this->getWebDir($this->assets[$assetClass]) . \ltrim($file, '/')
            : $this->getWebDir($this->assets[$assetClass]) . \ltrim($file, '/');
    }

    /**
     * @return string
     */
    public function applyCss(): string
    {
        $result = [];
        foreach ($this->assets as $asset) {
            $links = $this->getLinks($asset, $asset->css());
            foreach ($links as $link) {
                $result[] = '<link rel="stylesheet" href="' . $link . '">';
            }
        }
        return \implode('', $result);
    }

    /**
     * @return string
     */
    public function applyJs(): string
    {
        $result = [];
        foreach ($this->assets as $asset) {
            $productionJs = $this->getWebDir($asset) . self::PRODUCTION_JS;
            $productionJsFile = $this->webRoot . $productionJs;
            if ($this->isProduction() && \file_exists($productionJsFile)) {
                $links = $this->getLinks($asset, $asset->externalJs());
                $links[] = $productionJs . '?' . \filemtime($productionJsFile);
            } else {
                $links = $this->getLinks($asset, $asset->js());
            }
            foreach ($links as $link) {
                $result[] = '<script src="' . $link . '"></script>';
            }
        }
        return \implode('', $result);
    }

    /**
     * @return bool
     */
    public function isProduction(): bool
    {
        return $this->isProduction;
    }

    /**
     * @param bool $isProduction
     */
    public function setIsProduction(bool $isProduction)
    {
        $this->isProduction = $isProduction;
    }

    /**
     * @param AbstractAsset $asset
     *
     * @throws \Symfony\Component\Filesystem\Exception\IOException
     */
    protected function publishAsset(AbstractAsset $asset)
    {
        $hash = $asset->getHash();
        $dir = $this->webRoot . '/' . $this->assetDirName . '/' . $hash;
        if ($asset->useLinks) {
            if (!$this->filesystem->exists($dir)) {
                $this->filesystem->symlink($asset->getBasePath(), $dir, true);
            }
        } else {
            if ($this->filesystem->exists($dir)) {
                $this->filesystem->remove($dir);
            }
            $this->filesystem->mirror($asset->getBasePath(), $dir);
            $this->filesystem->chmod($dir, 0755, 000, true);
        }
    }

    /**
     * @param AbstractAsset $asset
     *
     * @return string
     */
    private function getWebDir(AbstractAsset $asset): string
    {
        return $asset->publish
            ? '/' . $this->assetDirName . '/' . $asset->getHash() . '/'
            : '/';
    }

    /**
     * @param AbstractAsset $asset
     * @param array $files
     *
     * @return array
     */
    private function getLinks(AbstractAsset $asset, array $files): array
    {
        $result = [];
        $dir = $this->getWebDir($asset);
        foreach ($files as $file) {
            $result[] = $this->getLink($file, $dir);
        }
        return $result;
    }

    /**
     * @param $file
     * @param $dir
     *
     * @return string
     */
    private function getLink($file, $dir): string
    {
        $link = $file;
        if (false === \strpos($file, 'http') && false === \strpos($file, '//')) {
            $filePath = $dir . \ltrim($file, '/');
            $timestamp = \filemtime($this->webRoot . $filePath);
            $link = $filePath . '?' . $timestamp;
        }

        return $link;
    }
}
