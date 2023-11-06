<?php

namespace Framework;

use Framework\HTTPRequest;
use Framework\Middleware;

use Illuminate\Http\Response;

/**
 * Class MiddlewareEngine
 *
 * @property Middleware[] $middlewares The list of middlewares in chain engine
 */
class MiddlewareEngine {
    private static $middlewares = array();

    /**
     * Initialize MiddlewareEngine static class
     *
     * @param Middleware[] $middlewares The list of middlewares route
     * @return void
     */
    public static function init(array $middlewares): void {
        self::$middlewares = $middlewares;
    }

    /**
     * Get list of middleware instance in chain engine
     *
     * @return Middleware[]
     */
    public static function getMiddlewares(): array {
        return self::$middlewares;
    }

    /**
     * Run next middleware in chain engine
     *
     * @param HTTPRequest $httpRequest The current HTTP request of middleware chain 
     * @param Response $httpResponse The current HTTP response of middleware chain 
     * @return Response
     */
    public static function runNextMiddleware(HTTPRequest $request, Response $response): Response {
        if(count(self::$middlewares)) {
            $middleware = array_shift(self::$middlewares);
            return $middleware->handle($request, $response);
        }

        return $response;
    }
}