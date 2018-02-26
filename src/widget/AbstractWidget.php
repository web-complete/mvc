<?php

namespace WebComplete\mvc\widget;

use WebComplete\core\utils\container\ContainerInterface;
use WebComplete\mvc\view\View;
use WebComplete\mvc\view\ViewInterface;

abstract class AbstractWidget
{

    /**
     * @var ContainerInterface
     */
    protected $container;
    /**
     * @var ViewInterface
     */
    protected $view;

    /**
     * @param ContainerInterface $container
     */
    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
        $this->view = $this->container->get(View::class);
    }

    /**
     * @param array $params
     * @return string
     */
    abstract public function run(array $params = []): string;

    /**
     * @param string $templatePath
     * @param array $vars
     *
     * @return string
     * @throws \Exception
     */
    protected function render(string $templatePath, array $vars = []): string
    {
        return $this->view->layout()->render($templatePath, $vars);
    }
}
