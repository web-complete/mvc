<?php

namespace WebComplete\mvc\front;

use WebComplete\core\utils\container\ContainerInterface;
use WebComplete\core\utils\event\Observable;
use WebComplete\core\utils\traits\TraitObservable;
use WebComplete\mvc\controller\AbstractController;
use WebComplete\mvc\router\Route;
use WebComplete\mvc\router\Router;
use WebComplete\mvc\router\exception\NotAllowedException;
use WebComplete\mvc\router\exception\NotFoundException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\RedirectResponse;

class FrontController implements Observable
{
    use TraitObservable;

    const ERROR_CONTROLLER_KEY  = 'errorController';
    const EVENT_DISPATCH_BEFORE = 'fc_dispatch_before';
    const EVENT_DISPATCH_AFTER  = 'fc_dispatch_after';

    public static $errorActions = [
        403 => 'action403',
        404 => 'action404',
        500 => 'action500',
    ];

    /**
     * @var Request
     */
    protected $request;
    /**
     * @var Response
     */
    protected $response;
    /**
     * @var Router
     */
    protected $router;
    /**
     * @var ContainerInterface
     */
    protected $controllerContainer;

    /**
     * @param Router $router
     * @param Request $request
     * @param Response $response
     * @param ContainerInterface $controllerResolver
     */
    public function __construct(
        Router $router,
        Request $request,
        Response $response,
        ContainerInterface $controllerResolver
    ) {
        $this->router = $router;
        $this->request = $request;
        $this->response = $response;
        $this->controllerContainer = $controllerResolver;
    }

    /**
     * @param string|null $method
     * @param string|null $uri
     *
     * @return Response
     * @throws \WebComplete\mvc\router\exception\NotAllowedException
     * @throws \UnexpectedValueException
     * @throws \InvalidArgumentException
     * @throws \Psr\Container\NotFoundExceptionInterface
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws \Exception
     */
    public function dispatch($method = null, $uri = null): Response
    {
        $method = $method ?? $this->request->getMethod();
        $uri = $uri ?? \parse_url($this->request->getRequestUri(), \PHP_URL_PATH);
        $eventData = ['method' => $method, 'url' => $this->request->getRequestUri()];
        $this->trigger(self::EVENT_DISPATCH_BEFORE, $eventData);

        try {
            $route = $this->router->dispatch($method, $uri);
            $this->processRoute($route);
        } catch (NotFoundException $e) {
            $this->processError($e, 404);
        } catch (NotAllowedException $e) {
            $this->processError($e, 403);
        } catch (\Exception $e) {
            if (\ENV === 'dev') {
                throw $e;
            }
            $this->processError($e, 500);
        }
        $this->response->prepare($this->request);
        $this->trigger(self::EVENT_DISPATCH_AFTER, $eventData);
        return $this->response;
    }

    /**
     * @param Route $route
     *
     * @throws \InvalidArgumentException
     * @throws \UnexpectedValueException
     * @throws \Psr\Container\NotFoundExceptionInterface
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws \WebComplete\mvc\router\exception\NotAllowedException
     */
    public function processRoute(Route $route)
    {
        $controllerClass = $route->getClass();
        $actionMethod = $route->getMethod();
        /** @var AbstractController $controller */
        $controller = $this->controllerContainer->get($controllerClass);
        $this->processController($controller, $actionMethod, $route->getParams());
    }

    /**
     * @param AbstractController $controller
     * @param string $actionMethod
     * @param array $params
     *
     * @throws \Psr\Container\NotFoundExceptionInterface
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws \InvalidArgumentException
     * @throws \UnexpectedValueException
     * @throws \WebComplete\mvc\router\exception\NotAllowedException
     */
    public function processController(
        AbstractController $controller,
        string $actionMethod,
        array $params = []
    ) {
        $result = $controller->beforeAction();
        if ($result === true) {
            $result = \call_user_func_array([$controller, $actionMethod], $params);
            $result = $controller->afterAction($result);
        }
        $this->processResult($result);
    }

    /**
     * @param \Exception|null $exception
     * @param int $code
     *
     * @throws \Psr\Container\NotFoundExceptionInterface
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws \UnexpectedValueException
     * @throws \InvalidArgumentException
     * @throws \WebComplete\mvc\router\exception\NotAllowedException
     */
    public function processError(\Exception $exception = null, int $code)
    {
        $this->response->setContent('Page not found');
        if ($controller = $this->controllerContainer->get(self::ERROR_CONTROLLER_KEY)) {
            $this->processController($controller, self::$errorActions[$code], [$exception]);
        }
        $this->response->setStatusCode($code);
    }

    /**
     * @param $result
     *
     * @throws \InvalidArgumentException
     * @throws \UnexpectedValueException
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws \Psr\Container\NotFoundExceptionInterface
     * @throws \WebComplete\mvc\router\exception\NotAllowedException
     */
    protected function processResult($result)
    {
        if (\is_string($result)) {
            $this->response->setStatusCode(200);
            $this->response->headers->set('content-type', 'text/html');
            $this->response->setContent($result);
        } elseif (\is_array($result)) {
            $this->response->setStatusCode(200);
            $this->response->headers->set('content-type', 'application/json');
            $this->response->setContent(\json_encode($result));
        } elseif ($result instanceof Response) {
            if ($result instanceof RedirectResponse) {
                $this->response = $result;
            } elseif ($result->getStatusCode() !== 200) {
                $this->processError(null, $result->getStatusCode());
            }
        } elseif ($result === false) {
            throw new NotAllowedException('Action is not allowed');
        }
    }
}
