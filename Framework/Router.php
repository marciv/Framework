<?php

namespace Framework;

use Framework\Exceptions\RouteFolderNotFoundException;
use Framework\Exceptions\NoRouteFoundException;
use Framework\Enums\HTTPMethod;
use Framework\HTTPRequest;
use Framework\Route;

use DirectoryIterator;
use ReflectionClass;
use Exception;

/**
 * Class Router
 * Extract all reference of extended route class inside app folder
 * 
 * @property Route[] $listRoute The list of app routes class
 * @property string $routeFolder The folder path of routes
 */
class Router {
    protected static $listRoute = array();
    protected static $routeFolder;
    
    /**
     * Initialize Router static class
     *
     * @param string $folder The app folder path
     * @return void
     */
    public static function init(string $folder): void {
        $routesPath = realpath($folder."Routes");
        /* Check if app routes folder exist */
        if(!is_dir($routesPath)) {
            throw(new RouteFolderNotFoundException("The app routes folder doesn't exist : ".$routesPath));
        }
        
        /* Foreach all routes file inside folder */
        foreach(new DirectoryIterator($routesPath) as $routeFile) {
            if(!$routeFile->isFile()) continue;
            /* Import class from absolute path of route file */
            require_once(join(DIRECTORY_SEPARATOR, array($routesPath, $routeFile->getFilename())));
            /* Get file name without .php with namespace */
            $routeName = "\\Routes\\".$routeFile->getBasename(".php");
            /* Check if route class exist from file name */
            if(!class_exists($routeName)) {
                throw(new Exception("The route class '$routeName' doesn't exist"));
            }
            /* Check if route class is children of Route class */
            else if(!is_subclass_of($routeName, Route::class)) {
                throw(new Exception("The route '$routeName' is not subclass of ".Route::class));
            }
            /* Try to reflect route class from file name */
            $route = (new ReflectionClass($routeName))->newInstance();
            /* Check if route instance of Route class */
            if(!$route instanceof Route) {
                throw(new Exception("The route '$route' is not instance of ".Route::class));
            }
            /* Push route class instance in list */
            self::$listRoute[] = $route;
        }

        self::$routeFolder = $routesPath;
    }
        
    /**
     * Get list of route instance in router
     *
     * @return Route[]
     */
    public static function getRoutes(): array {
        return self::$listRoute;
    }
        
    /**
     * Add route instance in router list 
     *
     * @param HTTPMethod $method The HTTP method of route
     * @param Route $route The instance of Route
     * @return void
     */
    public static function addRoute(HTTPMethod $method, Route $route): void {
        /* Check if route class is children of Route class */
        if(!is_subclass_of($route, Route::class)) {
            throw(new Exception("The route '$route' is not subclass of ".Route::class));
        }
        /* Check if route is instance of Route class */
        else if(!$route instanceof Route) {
            throw(new Exception("The route '$route' is not instance of ".Route::class));
        }
        $route->setMethod($method);
        self::$listRoute[] = $route;
    }

    /**
     * Add GET route instance in router list 
     *
     * @param Route $route The instance of Route
     * @return void
     */
    public function get(Route $route): void {
        self::addRoute(HTTPMethod::GET, $route);
    }

    /**
     * Add POST route instance in router list 
     *
     * @param Route $route The instance of Route
     * @return void
     */
    public function post(Route $route): void {
        self::addRoute(HTTPMethod::POST, $route);
    }

    /**
     * Add PUT route instance in router list 
     *
     * @param Route $route The instance of Route
     * @return void
     */
    public function put(Route $route): void {
        self::addRoute(HTTPMethod::PUT, $route);
    }

    /**
     * Add DELETE route instance in router list 
     *
     * @param Route $route The instance of Route
     * @return void
     */
    public function delete(Route $route): void {
        self::addRoute(HTTPMethod::DELETE, $route);
    }

    /**
     * Add ALL route instance in router list 
     *
     * @param Route $route The instance of Route
     * @return void
     */
    public function all(Route $route): void {
        self::addRoute(HTTPMethod::ALL, $route);
    }

    /**
     * Get associated route from HTTP request
     * 
     * @param HTTPRequest $request The HTTP request to find a route
     *
     * @return Route
     */
    public static function getRoute(HTTPRequest $request): Route {
        $method = $request->getMethod();
        $path = $request->getPath();

        /* Find route match with request */
        $found = array_filter(self::$listRoute, function(Route $route) use($method, $path) {
            /* Check if request path match with route path */
            return preg_match("#^" . $route->getPath() . "$#", $path)
            /* Check if request method match with route method */
            && in_array($route->getMethod(), array(HTTPMethod::ALL, $method));
        });

        if(!count($found)) {
            throw(new NoRouteFoundException("No route founded for path : ".$path.", method : ".$method->value));
        }

        return array_shift($found);
    }
}