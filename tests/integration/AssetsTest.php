<?php

namespace tests\integration;

use Asset\Asset1;
use Asset\Asset2;
use Symfony\Component\Filesystem\Filesystem;
use tests\MvcTestCase;
use WebComplete\mvc\assets\AssetManager;

class AssetsTest extends MvcTestCase
{

    public function tearDown()
    {
        parent::tearDown();
        $filesystem = new Filesystem();
        $webroot = dirname(__DIR__) . '/include/app/web';
        if ($filesystem->exists($webroot . '/assets')) {
            $filesystem->remove($webroot . '/assets');
        }
    }

    public function testAsset1()
    {
        $filesystem = new Filesystem();
        $webroot = dirname(__DIR__) . '/include/app/web';
        if ($filesystem->exists($webroot . '/assets')) {
            $filesystem->remove($webroot . '/assets');
        }

        $assetManager = new AssetManager($filesystem, $webroot, 'assets');
        $asset = new Asset1();
        $assetManager->registerAsset($asset);
        $ts1 = filemtime($webroot . '/assets/e9ce666e3568cae4684577f283a4bc4e/css/style.css');
        $ts2 = filemtime($webroot . '/assets/e9ce666e3568cae4684577f283a4bc4e/js/script.js');
        $expected = '<link rel="stylesheet" href="/assets/e9ce666e3568cae4684577f283a4bc4e/css/style.css?' . $ts1 . '">'
            . '<link rel="stylesheet" href="http://www.webcomplete.ru/css/style2.css">';
        $this->assertEquals($expected, $assetManager->applyCss());
        $expected = '<script src="/assets/e9ce666e3568cae4684577f283a4bc4e/js/script.js?' . $ts2 . '"></script>'
            . '<script src="//www.webcomplete.ru/js/script2.js"></script>';
        $this->assertEquals($expected, $assetManager->applyJs());
        $this->assertEquals(\md5(Asset1::class), (new Asset1())->getHash());
        $this->assertTrue($filesystem->exists($webroot . '/assets/e9ce666e3568cae4684577f283a4bc4e'));

        $this->assertEquals(['http://www.webcomplete.ru/css/style2.css'], $asset->externalCss());
        $this->assertEquals(['/css/style.css'], $asset->internalCss());
        $this->assertEquals(['//www.webcomplete.ru/js/script2.js'], $asset->externalJs());
        $this->assertEquals(['js/script.js'], $asset->internalJs());

        $assetManager->setIsProduction(true);
        $expected = '<link rel="stylesheet" href="/assets/e9ce666e3568cae4684577f283a4bc4e/css/style.css?' . $ts1 . '">'
            . '<link rel="stylesheet" href="http://www.webcomplete.ru/css/style2.css">';
        $this->assertEquals($expected, $assetManager->applyCss());
        $prodJs = $webroot . '/assets/e9ce666e3568cae4684577f283a4bc4e/asset.min.js';
        \file_put_contents($prodJs, '');
        $ts3 = \filemtime($prodJs);
        $expected = '<script src="//www.webcomplete.ru/js/script2.js"></script>'
            . '<script src="/assets/e9ce666e3568cae4684577f283a4bc4e/asset.min.js?' . $ts3 . '"></script>';
        $this->assertEquals($expected, $assetManager->applyJs());
        @\unlink($prodJs);
    }

    public function testAsset2()
    {
        $filesystem = new Filesystem();
        $webroot = dirname(__DIR__) . '/include/app/web';
        if ($filesystem->exists($webroot . '/assets')) {
            $filesystem->remove($webroot . '/assets');
        }

        $assetManager = new AssetManager($filesystem, $webroot, 'assets');
        $assetManager->registerAsset(new Asset2());
        $ts1 = filemtime($webroot . '/css/style.css');
        $ts2 = filemtime($webroot . '/js/script.js');
        $expected = '<link rel="stylesheet" href="/css/style.css?' . $ts1 . '">'
            . '<link rel="stylesheet" href="http://www.webcomplete.ru/css/style2.css">';
        $this->assertEquals($expected, $assetManager->applyCss());
        $expected = '<script src="/js/script.js?' . $ts2 . '"></script>'
            . '<script src="//www.webcomplete.ru/js/script2.js"></script>';
        $this->assertEquals($expected, $assetManager->applyJs());
        $this->assertEquals(\md5(Asset2::class), (new Asset2())->getHash());
        $this->assertFalse($filesystem->exists($webroot . '/assets'));
    }

    public function testAsset1NoSym()
    {
        $filesystem = new Filesystem();
        $webroot = dirname(__DIR__) . '/include/app/web';
        if ($filesystem->exists($webroot . '/assets')) {
            $filesystem->remove($webroot . '/assets');
        }

        $asset = new Asset1();
        $asset->useLinks = false;
        $assetManager = new AssetManager($filesystem, $webroot, 'assets');
        $assetManager->registerAsset($asset);
        $ts1 = filemtime($webroot . '/assets/e9ce666e3568cae4684577f283a4bc4e/css/style.css');
        $ts2 = filemtime($webroot . '/assets/e9ce666e3568cae4684577f283a4bc4e/js/script.js');
        $expected = '<link rel="stylesheet" href="/assets/e9ce666e3568cae4684577f283a4bc4e/css/style.css?' . $ts1 . '">'
            . '<link rel="stylesheet" href="http://www.webcomplete.ru/css/style2.css">';
        $this->assertEquals($expected, $assetManager->applyCss());
        $expected = '<script src="/assets/e9ce666e3568cae4684577f283a4bc4e/js/script.js?' . $ts2 . '"></script>'
            . '<script src="//www.webcomplete.ru/js/script2.js"></script>';
        $this->assertEquals($expected, $assetManager->applyJs());
        $this->assertEquals(\md5(Asset1::class), (new Asset1())->getHash());
        $this->assertTrue($filesystem->exists($webroot . '/assets/e9ce666e3568cae4684577f283a4bc4e'));
    }
}