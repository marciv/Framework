<?php

namespace Framework;

use Framework\Exceptions\MiddlewareNotFoundException;
use Framework\Exceptions\ControllerNotFoundException;
use Framework\Exceptions\ActionNotFoundException;
use Framework\MiddlewareEngine;
use Framework\Enums\HTTPMethod;
use Framework\HTTPRequest;
use Framework\Middleware;
use Framework\Controller;

use Illuminate\Http\Response;

/**
 * Class Route
 *
 * @property Middleware[] $middlewares The list of middlewares of route
 * @property string|Controller $controller The controller of route
 * @property string $action The controller method of route
 * @property HTTPMethod $method The request method of route
 * @property array $param The request params of route
 * @property string $path The request path of route
 */
class Route {
    protected $middlewares;
    protected $controller;
    protected $action;
    protected $method;
    protected $params;
    protected $path;

    /**
     * Constructor of Route class
     *
     * @return void
     */
    public function __construct() {
        $middlewares = $this->middlewares;
        foreach($middlewares as $index => $middleware) {
            /* Check if route controller is children of Middleware class */
            if(!is_subclass_of($middleware, Middleware::class)) {
                throw(new MiddlewareNotFoundException("The middleware '$middleware' is not subclass of ".Middleware::class));
            }

            /* Check if middleware is instance or reference of class */
            $middleware = (is_string($middleware))? new $middleware(): $middleware;
            if(!$middleware instanceof Middleware) {
                throw(new MiddlewareNotFoundException("The middleware '$middleware' is not instance of ".Middleware::class));
            }

            /* Replace route middleware with instance */
            $this->middlewares[$index] = $middleware;
        }

        /* Check if route controller is children of Controller class */
        $controller = $this->controller;
        if(!is_subclass_of($controller, Controller::class)) {
            throw(new ControllerNotFoundException("The controller '$controller' is not subclass of ".Controller::class));
        }

        /* Check if controller is instance or reference of class */
        $controller = (is_string($controller))? new $controller(): $controller;
        if(!$controller instanceof Controller) {
            throw(new ControllerNotFoundException("The controller '$controller' is not instance of ".Controller::class));
        }
        
        /* Check if route controller has action method */
        $action = $this->action;
        if(!method_exists($controller, $action)) {
            throw(new ActionNotFoundException("The controller method '$action' doesn't exist in ".$controller::class));
        }

        /* Set default array route middleware */
        if(!isset($this->middlewares)) {
            $this->middlewares = array();
        }
        /* Set default array route params */
        if(!isset($this->params)) {
            $this->params = array();
        }
        /* Set default route path */
        if(!isset($this->path)) {
            $this->path = "/.*";
        }

        $this->controller = $controller;
    }
        
    /**
     * Set list of middleware instance in route
     * 
     * @param Middleware[] $middlewares The list of middleware instance to be set in route
     *
     * @return void
     */
    public function setMiddlewares(array $middlewares): void {
        $this->middlewares = $middlewares;
    }

    /**
     * Set controller instance in route
     * 
     * @param Controller $controller The controller instance to be set in route
     *
     * @return void
     */
    public function setController(Controller $controller): void {
        $this->controller = $controller;
    }

    /**
     * Set controller method action in route
     * 
     * @param string $action The controller method action to be set in route
     *
     * @return void
     */
    public function setAction(string $action): void {
        $this->action = $action;
    }

    /**
     * Set HTTP request method in route
     * 
     * @param HTTPMethod $method The HTTP request method to be set in route
     *
     * @return void
     */
    public function setMethod(HTTPMethod $method): void {
        $this->method = $method;
    }

    /**
     * Set list of HTTP request parameters in route
     * 
     * @param array $params The list of HTTP request parameters to be set in route
     *
     * @return void
     */
    public function setParams(array $params): void {
        $this->params = $params;
    }

    /**
     * Set HTTP request path in route
     * 
     * @param string $path The HTTP request path to be set in route
     *
     * @return void
     */
    public function setPath(string $path): void {
        $this->path = $path;
    }

    /**
     * Get list of middleware instance in route
     *
     * @return Middleware[]
     */
    public function getMiddlewares(): array {
        return $this->middlewares;
    }

    /**
     * Get controller instance in route
     *
     * @return Controller
     */
    public function getController(): Controller {
        return $this->controller;
    }

    /**
     * Get controller method action in route
     *
     * @return string
     */
    public function getAction(): string {
        return $this->action;
    }

    /**
     * Get HTTP request method in route
     *
     * @return HTTPMethod
     */
    public function getMethod(): HTTPMethod {
        return $this->method;
    }

    /**
     * Get list of HTTP request parameters in route
     *
     * @return array
     */
    public function getParam(): array {
        return $this->param;
    }

    /**
     * Get HTTP request path in route
     *
     * @return string
     */
    public function getPath(): string {
        return $this->path;
    }

    /**
     * The entry point method of route class
     * 
     * @param HTTPRequest $httpRequest The current HTTP request of route 
     * @param Response $httpResponse The current HTTP response of route 
     *
     * @return Response
     */
    public function run(HTTPRequest $request, Response $response): Response {
        /* Execute middlewares chain of route */
        $response = MiddlewareEngine::runNextMiddleware($request, $response);
        /* If response has not content execute controller */
        if(!$response->getContent()) {
            /* Merge request parameters with route */
            $params = array_merge($request->getParams(), $this->params);
            /* Execute route controller action method */
            $this->controller->{$this->action}($request, $response, $params);
        }

        return $response;
    }
}