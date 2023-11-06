<?php

namespace Framework;

use Framework\Exceptions\AppFolderNotFoundException;
use Framework\MiddlewareEngine;
use Framework\HTTPRequest;
use Framework\Router;
use Framework\Views;

use Illuminate\Http\Response;

/**
 * Framework class
 * 
 * @property string $appFolder The folder path of app
 * @property Response $response The current HTTP response
 * @property HTTPRequest $request The current HTTP request
 */
class Framework {
    public $appFolder;
    public $response;
    public $request;
    
    /**
     * Constructor of Framework class
     *
     * @param string $folder The app folder path
     * @return void
     */
    public function __construct(string $folder) {
        /* Check if app folder exist */
        if(!is_dir($folder)) {
            throw(new AppFolderNotFoundException("The app folder doesn't exist : ".$folder));
        }

        Router::init($folder);
        Views::init($folder);
        
        $this->request = new HTTPRequest();
        $this->response = new Response();
        $this->appFolder = $folder; 
    }

    /**
     * The entry point of Framework
     *
     * @return void
     */
    public function run(): Response {
        /* Get associated route from HTTP request */
        $route = Router::getRoute($this->request);
        /* Set route instance in HTTP request */
        $this->request->setRoute($route);

        /* Set route middlewares in chain engine */
        MiddlewareEngine::init($route->getMiddlewares());
        /* Execute middlewares chain and controller */
        $this->response = $route->run($this->request, $this->response);

        if(!$this->response instanceof Response) {
            $content = $this->response;
            $this->response = new Response();
            $this->response->setContent($content);
        }

        return $this->response->send();
    }
}