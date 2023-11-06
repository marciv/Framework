<?php

namespace Framework;

use Symfony\Component\HttpFoundation\Session\Session;
use Illuminate\Http\Request;

use Framework\Enums\HTTPMethod;
use Framework\Route;

/**
 * HTTPRequest class
 * 
 * @property HTTPMethod $method The HTTP request method
 * @property array $params The HTTP request parameters
 * @property Route $route The HTTP request Framework\Route
 * @property float $started The HTTP request started timestamp
 * @property Request $request The instance of Illuminate\Http\Request
 * @property Session|null $session The instance of Symfony\Component\HttpFoundation\Session\Session
 * @property string $path The HTTP request URL path
 */
class HTTPRequest {
    private $method;
    private $params;
    private $route;
    public $started;
    public $request;
    public $session;
    public $path;

    /**
     * Constructor of HTTPRequest class
     *
     * @return void
     */
    function __construct() {
        $this->request = Request::capture();
        $this->method = HTTPMethod::fromValue($this->request->method());
        $this->path = "/" . ltrim($this->request->getPathInfo(), "/");
        $this->started = microtime(true);
        $this->params = array();
        $this->session = null;
        $this->bindParam();
    }

    /**
     * Get the full URL of request.
     *
     * @return string
     */
    public function getUrl(): string {
        return $this->request->fullUrl();
    }

    /**
     * Get the relative path of request.
     *
     * @return string
     */
    public function getPath(): string {
        return $this->path;
    }

    /**
     * Set the relative path in request.
     * 
     * @param string $name The relative path of request
     *
     * @return void
     */
    public function setPath(string $path): void {
        $this->path = "/".ltrim($path,"/");
    }

    /**
     * Set the HTTP method in request.
     *
     * @return HTTPMethod
     */
    public function getMethod(): HTTPMethod {
        return $this->method;
    }

    /**
     * Get the parameter of request.
     * 
     * @param string $name The name of parameter request
     *
     * @return mixed
     */
    public function getParam(string $name): mixed {
        if(isset($this->params[$name])) {
            return $this->params[$name];
        }
        return null;
    }

    /**
     * Get all parameters of request.
     *
     * @return mixed
     */
    public function getParams(): array {
        return $this->params;
    }

    /**
     * Get session of request.
     *
     * @return mixed
     */
    public function getSession(): mixed {
        return $this->session;
    }
    
    /**
     * Set session in request.
     * 
     * @param Session $session The session of request
     *
     * @return void
     */
    public function setSession(Session $session): void {
        $this->session = $session;
    }

    /**
     * Set list of parameters in request.
     * 
     * @param array $params The list of request parameters
     *
     * @return void
     */
    public function setParams(array $params): void {
        foreach($params as $key => $value) {
            $this->params[$key] = $value;
        }
    }

    /**
     * Remove parameter of request.
     * 
     * @param string $name The name of parameter to remove
     *
     * @return void
     */
    public function deleteParam(string $name): void {
        unset($this->params[$name]);
    }

    /**
     * Set new parameter in request.
     * 
     * @param string $name The name of new parameter
     * @param mixed $value The value of new parameter
     *
     * @return void
     */
    public function setParam(string $name, mixed $value): void {
        $this->params[$name] = $value;
    }

    /**
     * Set route in request.
     * 
     * @param Route $route The route of request
     *
     * @return void
     */
    public function setRoute(Route $route): void {
        $this->route = $route;
    }

    /**
     * Get route of request.
     *
     * @return Route
     */
    public function getRoute(): Route {
        return $this->route;
    }

    /**
     * Get duration in milliseconds of request.
     *
     * @return float
     */
    public function getDuration(): float {
        return floor((microtime(true) - $this->started) * 1000);
    }

    /**
     * Set Illuminate\Http\Request params in request.
     *
     * @return void
     */
    public function bindParam(): void {
        $method = $this->getMethod();

        /* WARNING : This code break API because SET method use query params and form data */
        /* if($method === HTTPMethod::GET || $method === HTTPMethod::DELETE) {
            $params = $this->request->query();
        }
        else if($method === HTTPMethod::POST || $method === HTTPMethod::PUT) {
            $params = $this->request->post();
        }
        else {
            $params = $this->request->all();
        } */

        $params = $this->request->all();

        /* Merge request params with instance params */
        if(!empty($params) && is_array($params)) {
            $this->params = array_merge($this->params, $params);
        }

        /* ??? */
        if(isset($this->params["query"])) {
            unset($this->params["query"]);
        }
    }
}
