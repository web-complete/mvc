<?php

namespace WebComplete\mvc\assets;

use Symfony\Component\Filesystem\Filesystem;

class AssetManager
{

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

    /**
     * @param Filesystem $filesystem
     * @param string $webRoot
     * @param string $assetDirName
     */
    public function __construct(Filesystem $filesystem, string $webRoot, string $assetDirName)
    {
        $this->filesystem = $filesystem;
        $this->webRoot = \rtrim($webRoot, '/');
        $this->assetDirName = $assetDirName;
    }

    /**
     * @param AbstractAsset $asset
     *
     * @throws \Symfony\Component\Filesystem\Exception\IOException
     */
    public function registerAsset(AbstractAsset $asset)
    {
        if ($asset->publish) {
            $this->publishAsset($asset);
        }
        $this->assets[] = $asset;
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
            $links = $this->getLinks($asset, $asset->js());
            foreach ($links as $link) {
                $result[] = '<script src="' . $link . '"></script>';
            }
        }
        return \implode('', $result);
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
