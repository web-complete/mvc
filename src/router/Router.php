<?php

namespace WebComplete\mvc\router;

use FastRoute\Dispatcher;
use FastRoute\RouteCollector;
use WebComplete\mvc\router\exception\Exception;
use WebComplete\mvc\router\exception\NotAllowedException;
use WebComplete\mvc\router\exception\NotFoundException;

class Router
{

    /**
     * @var Routes
     */
    private $config;

    /**
     * @param Routes $config
     */
    public function __construct(Routes $config)
    {
        $this->config = $config;
    }

    /**
     * @param string $method
     * @param string $uri
     *
     * @return Route
     * @throws \WebComplete\mvc\router\exception\Exception
     * @throws \WebComplete\mvc\router\exception\NotAllowedException
     * @throws \WebComplete\mvc\router\exception\NotFoundException
     */
    public function dispatch(string $method, string $uri): Route
    {
        $route = null;
        $dispatcher = $this->configureDispatcher();
        $routeInfo = $dispatcher->dispatch($method, $uri);
        switch ($routeInfo[0]) {
            case Dispatcher::NOT_FOUND:
                throw new NotFoundException('Route not found');
                break;
            case Dispatcher::METHOD_NOT_ALLOWED:
                throw new NotAllowedException('Route not allowed');
                break;
            case Dispatcher::FOUND:
                if (!isset($routeInfo[1][0])) {
                    throw new Exception('Controller class name expected');
                }
                if (!isset($routeInfo[1][1])) {
                    throw new Exception('Action method name expected');
                }
                $route = new Route($routeInfo[1][0], $routeInfo[1][1], $routeInfo[2]);
                break;
        }

        return $route;
    }

    /**
     * @return Dispatcher
     */
    protected function configureDispatcher(): Dispatcher
    {
        return \FastRoute\simpleDispatcher(function (RouteCollector $collector) {
            foreach ($this->config as $route) {
                $collector->addRoute($route[0], $route[1], $route[2]);
            }
        });
    }
}
