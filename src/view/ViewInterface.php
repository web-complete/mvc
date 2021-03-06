<?php

namespace WebComplete\mvc\view;

use WebComplete\core\utils\container\ContainerInterface;
use WebComplete\mvc\assets\AssetManager;
use WebComplete\mvc\controller\AbstractController;

interface ViewInterface
{

    /**
     * @param string|null $path
     * @param array $vars
     *
     * @return ViewInterface
     * @throws \Exception
     */
    public function layout(string $path = null, array $vars = []): ViewInterface;

    /**
     * @param $path
     * @param array $vars
     *
     * @return string
     * @throws \Exception
     */
    public function render($path, array $vars = []): string;

    /**
     * @param ContainerInterface $container
     */
    public function setContainer(ContainerInterface $container);

    /**
     * @return ContainerInterface
     */
    public function getContainer(): ContainerInterface;

    /**
     * @param AbstractController $controller
     */
    public function setController(AbstractController $controller);

    /**
     * @return AbstractController|null
     */
    public function getController();

    /**
     * @return AssetManager
     */
    public function getAssetManager(): AssetManager;
}
