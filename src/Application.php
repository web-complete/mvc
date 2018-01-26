<?php

namespace WebComplete\mvc;

use DI\ContainerBuilder;
use DI\Scope;
use Monolog\ErrorHandler as MonologErrorHandler;
use Monolog\Logger;
use Symfony\Component\HttpFoundation\Request;
use WebComplete\core\cube\CubeManager;
use WebComplete\core\utils\alias\AliasHelper;
use WebComplete\core\utils\alias\AliasService;
use WebComplete\core\utils\container\ContainerAdapter;
use WebComplete\core\utils\container\ContainerInterface;
use WebComplete\core\utils\hydrator\Hydrator;
use WebComplete\core\utils\hydrator\HydratorInterface;
use WebComplete\mvc\errorHandler\ErrorHandler;
use WebComplete\mvc\logger\LoggerService;
use WebComplete\mvc\router\Router;
use WebComplete\mvc\view\View;
use WebComplete\mvc\view\ViewInterface;

class Application
{
    /** @var array */
    protected $config;

    /** @var ContainerInterface */
    protected $container;
    protected $errorHandler;

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
        $this->errorHandler = new ErrorHandler();
        $this->errorHandler->register();
        $this->errorHandler->setErrorPagePath($this->config['errorPagePath'] ?? '');
    }

    /**
     * @return array
     * @throws \Exception
     */
    protected function init(): array
    {
        $aliasService = new AliasService($this->config['aliases'] ?? []);
        $applicationConfig = new ApplicationConfig($this->config);

        $definitions = [
            ApplicationConfig::class => $applicationConfig,
            AliasService::class => $aliasService,
            Router::class => new Router($this->config['routes'] ?? []),
            Request::class => Request::createFromGlobals(),
            ViewInterface::class => \DI\object(View::class)->scope(Scope::PROTOTYPE),
            HydratorInterface::class => \DI\object(Hydrator::class),
        ];
        return $definitions;
    }

    protected function afterInit()
    {
        $aliasService = $this->container->get(AliasService::class);
        AliasHelper::setInstance($aliasService);
        $cubeManager = $this->getContainer()->get(CubeManager::class);
        $cubesLocations = $this->config['cubesLocations'] ?? [];
        $cubesDefinitions = [];

        foreach ($cubesLocations as $location) {
            $cubeManager->registerAll($aliasService->get($location), $cubesDefinitions);
        }

        foreach ($cubesDefinitions as $def => $value) {
            $this->getContainer()->set($def, $value);
        }

        $commonLogger = $this->container->get(LoggerService::class)->get('*');
        MonologErrorHandler::register($commonLogger, [], Logger::CRITICAL, Logger::EMERGENCY);
        $cubeManager->bootstrap($this->container);
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
