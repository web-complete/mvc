<?php

namespace WebComplete\mvc;

use DI\ContainerBuilder;
use DI\Scope;
use Symfony\Component\Cache\Simple\FilesystemCache;
use Symfony\Component\Cache\Simple\NullCache;
use Symfony\Component\HttpFoundation\Request;
use WebComplete\core\cube\CubeManager;
use WebComplete\core\utils\alias\AliasHelper;
use WebComplete\core\utils\alias\AliasService;
use WebComplete\core\utils\container\ContainerAdapter;
use WebComplete\core\utils\container\ContainerInterface;
use WebComplete\core\utils\helpers\ClassHelper;
use WebComplete\mvc\errorHandler\ErrorHandler;
use WebComplete\mvc\router\Router;
use WebComplete\mvc\view\View;
use WebComplete\mvc\view\ViewInterface;

class Application
{
    /** @var array */
    protected $config;

    /** @var ContainerInterface */
    protected $container;

    /**
     * @param array $config
     * @param bool $initErrorHandler
     *
     * @throws \Exception
     */
    public function __construct(array $config, $initErrorHandler = true)
    {
        $this->config = $config;
        if ($initErrorHandler) {
            $this->initErrorHandler();
        }
        $definitions = \array_merge(
            $this->init(),
            $this->config['definitions'] ?? []
        );
        $this->initContainer($definitions);
        $this->afterInit();
    }

    /**
     */
    protected function initErrorHandler()
    {
        $errorHandler = new ErrorHandler();
        $errorHandler->register();
        $errorHandler->setErrorPagePath($this->config['errorPagePath'] ?? '');
    }

    /**
     * @return array
     * @throws \Exception
     */
    protected function init(): array
    {
        $aliasService = new AliasService($this->config['aliases'] ?? []);
        $definitions = [
            AliasService::class => $aliasService,
            Router::class => new Router($this->config['routes'] ?? []),
            Request::class => Request::createFromGlobals(),
            ViewInterface::class => \DI\object(View::class)->scope(Scope::PROTOTYPE)
        ];

        $pmCache = \ENV === 'dev' ? new NullCache() : new FilesystemCache();
        $cubeManager = new CubeManager(new ClassHelper(), $pmCache);
        $cubesLocations = $this->config['cubesLocations'] ?? [];
        foreach ($cubesLocations as $location) {
            $cubeManager->registerAll($aliasService->get($location), $definitions);
        }

        return $definitions;
    }

    protected function afterInit()
    {
        AliasHelper::setInstance($this->container->get(AliasService::class));
    }

    /**
     * @return array
     */
    public function getConfig(): array
    {
        return $this->config;
    }

    /**
     * @return ContainerInterface
     */
    public function getContainer(): ContainerInterface
    {
        return $this->container;
    }

    /**
     * @param ContainerInterface $container
     */
    public function setContainer(ContainerInterface $container)
    {
        $this->container = $container;
    }

    /**
     * @param $definitions
     *
     * @throws \DI\DependencyException
     * @throws \DI\NotFoundException
     * @throws \InvalidArgumentException
     */
    protected function initContainer($definitions)
    {
        $definitions[ContainerInterface::class] = \DI\object(ContainerAdapter::class);
        $container = (new ContainerBuilder())->addDefinitions($definitions)->build();
        $this->container = $container->get(ContainerInterface::class);
        $this->container->setContainer($container);
    }
}
