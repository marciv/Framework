<?php

namespace Framework;

use Framework\Exceptions\MiddlewareNotFoundException;
use Framework\Exceptions\AppFolderNotFoundException;
use Illuminate\Http\Response as Response;

class Framework
{
    use Router, MiddlewareEngine;
    public $_httpRequest;
    public $_httpResponse;
    public static $_appFolder;

    public function __construct()
    {
        if(empty(self::$_appFolder)){
            throw new AppFolderNotFoundException();
        } else {
            $this->_httpRequest = new HttpRequest();
            $this->_httpResponse = new Response();
            Framework::setListRoute(self::$_appFolder);
            Framework::setMiddleware(self::$_appFolder);
        }
    }

    public static function setAppFolder($folder){
        self::$_appFolder = $folder;
    }


    public function run()
    {
        if (Framework::setMiddlewareChain($this->_httpRequest)) {
            $this->_httpResponse = $this->runMiddlewareChain($this->_httpRequest,$this->_httpResponse );
        }
        $this->findRoute();

        $this->_httpResponse = $this->_foundRoute->run($this->_httpRequest,$this->_httpResponse);

        if(!$this->_httpResponse instanceof Response){
            $this->_httpResponse = new Response();
            $this->_httpResponse->setContent($this->_httpResponse);
        }
        // $this->_httpResponse->prepare($this->_httpRequest);
        $this->_httpResponse->send();
        // echo '<pre>';
        // print_r($this->_foundRoute);
        // print_r(self::$middlewareChain);
        // echo '</pre>';
        return;
    }
}
