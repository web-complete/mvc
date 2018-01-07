<?php

namespace WebComplete\mvc\view;

use WebComplete\core\utils\alias\AliasService;
use WebComplete\core\utils\container\ContainerInterface;
use WebComplete\mvc\assets\AssetManager;
use WebComplete\mvc\controller\AbstractController;

class View implements ViewInterface
{

    protected $layoutPath;
    protected $layoutVars = [];
    protected $templatePath;
    protected $templateVars = [];
    protected $controller;

    /**
     * @var AliasService|null
     */
    private $aliasService;
    /**
     * @var ContainerInterface|null
     */
    private $container;
    /**
     * @var AssetManager
     */
    private $assetManager;

    /**
     * @param AliasService $aliasService
     * @param AssetManager $assetManager
     */
    public function __construct(AliasService $aliasService, AssetManager $assetManager)
    {
        $this->aliasService = $aliasService;
        $this->assetManager = $assetManager;
    }

    /**
     * @param string|null $path
     * @param array $vars
     *
     * @return $this|ViewInterface
     * @throws \WebComplete\core\utils\alias\AliasException
     */
    public function layout(string $path = null, array $vars = []): ViewInterface
    {
        $this->layoutPath = $path ? $this->aliasService->get($path) : null;
        $this->layoutVars = $vars;
        return $this;
    }

    /**
     * @param $path
     * @param array $vars
     *
     * @return string
     * @throws \RuntimeException
     * @throws \InvalidArgumentException
     * @throws \WebComplete\core\utils\alias\AliasException
     */
    public function render($path, array $vars = []): string
    {
        $this->templatePath = $this->aliasService->get($path);
        $this->templateVars = $vars;
        $result = $this->eval($this->templatePath, $this->templateVars);
        if ($this->layoutPath) {
            $layoutPath = $this->layoutPath;
            $this->layoutPath = null;
            $this->layoutVars['view'] = $this;
            $this->layoutVars['content'] = $result;
            $result = $this->eval($layoutPath, $this->layoutVars);
            $this->layoutVars = [];
        }
        return $result;
    }

    /**
     * @param $path
     * @param $vars
     *
     * @return string
     */
    protected function eval($path, $vars): string
    {
        \extract($vars, \EXTR_SKIP);
        \ob_start();
        require $path;
        return \ob_get_clean();
    }

    /**
     * @param ContainerInterface $container
     */
    public function setContainer(ContainerInterface $container)
    {
        $this->container = $container;
    }

    /**
     * @return ContainerInterface
     */
    public function getContainer(): ContainerInterface
    {
        if (!$this->container) {
            global $application;
            $this->setContainer($application->getContainer());
        }
        return $this->container;
    }

    /**
     * @param AbstractController $controller
     */
    public function setController(AbstractController $controller)
    {
        $this->controller = $controller;
    }

    /**
     * @return AssetManager
     */
    public function getAssetManager(): AssetManager
    {
        return $this->assetManager;
    }

    /**
     * @return AbstractController|null
     */
    public function getController()
    {
        return $this->controller;
    }
}
