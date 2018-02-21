<?php

namespace WebComplete\mvc\controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\RedirectResponse;
use WebComplete\core\utils\container\ContainerInterface;
use WebComplete\mvc\view\View;
use WebComplete\mvc\view\ViewInterface;

abstract class AbstractController
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
     * @var Request
     */
    protected $request;
    /**
     * @var Response
     */
    protected $response;
    protected $layout;

    /**
     * @param ContainerInterface $container
     */
    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
        $this->request = $this->container->get(Request::class);
        $this->response = $this->container->get(Response::class);
        $this->view = $this->container->make(View::class);
        $this->view->setController($this);
    }

    /**
     * @return bool|string|Response
     */
    public function beforeAction()
    {
        return true;
    }

    /**
     * @param $result
     *
     * @return mixed
     */
    public function afterAction($result)
    {
        return $result;
    }

    /**
     * @param $templatePath
     * @param array $vars
     *
     * @return Response
     * @throws \Exception
     */
    protected function responseHtml($templatePath, array $vars = []): Response
    {
        $html = $this->view->layout($this->layout)->render($templatePath, $vars);
        $this->updateResponse(200, 'text/html', $html);
        return $this->response;
    }

    /**
     * @param $templatePath
     * @param array $vars
     *
     * @return Response
     * @throws \Exception
     */
    protected function responseHtmlPartial($templatePath, array $vars = []): Response
    {
        $html = $this->view->layout()->render($templatePath, $vars);
        $this->updateResponse(200, 'text/html', $html);
        return $this->response;
    }

    /**
     * @param array $data
     *
     * @return Response
     * @throws \Exception
     */
    protected function responseJson(array $data): Response
    {
        $this->updateResponse(200, 'application/json', \json_encode($data));
        return $this->response;
    }

    /**
     * @param string $url
     * @param int $code
     * @param array $headers
     *
     * @return RedirectResponse
     * @throws \InvalidArgumentException
     */
    protected function responseRedirect(string $url, int $code = 302, array $headers = []): RedirectResponse
    {
        return new RedirectResponse($url, $code, $headers);
    }

    /**
     * @return Response
     * @throws \InvalidArgumentException
     */
    protected function responseNotFound(): Response
    {
        $this->response->setStatusCode(404);
        return $this->response;
    }

    /**
     * @return Response
     * @throws \InvalidArgumentException
     */
    protected function responseAccessDenied(): Response
    {
        $this->response->setStatusCode(403);
        return $this->response;
    }

    /**
     * @param $statusCode
     * @param $contentType
     * @param $content
     *
     * @throws \InvalidArgumentException
     * @throws \UnexpectedValueException
     */
    private function updateResponse($statusCode, $contentType, $content)
    {
        $this->response->setStatusCode($statusCode);
        $this->response->headers->set('content-type', $contentType);
        $this->response->setContent($content);
    }
}
