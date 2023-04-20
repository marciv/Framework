<?php

namespace Framework;

use Illuminate\Http\Request;
use Illuminate\Http\Redirect;



class HttpRequest
{

    private         $_param;
    private         $_method;
    private         $_route;
    public          $_session;
    public          $request;

    function __construct()
    {

        $this->request = Request::capture();
        $this->_method = $this->request->method();
        $this->_param = array();
        $this->_session = null;
        $this->bindParam();
    }

    public function getUrl()
    {
        return  $this->request->fullUrl();        
    }

    public function getPath()
    {
        return '/' . ltrim($this->request->getPathInfo(), "/");
    }

    public function getMethod()
    {
        return  $this->_method;
    }

    public function getParam(string $paramName)
    {
        if(isset($this->_param[$paramName])){
            return $this->_param[$paramName];
        } else {
            return null;
        }

    }

    public function getParams()
    {
        return $this->_param;
    }

    public function getSession()
    {
        return $this->_session;
    }
    public function setSession(object $session)
    {
        $this->_session = $session;
    }         

    public function setParams(array $params)
    {
        foreach ($params as $oneParams => $value) {
            $this->_param[$oneParams] = $value;
        }
        return;
    }

    public function deleteParam(string $name)
    {

        unset($this->_param[$name]);

        return;
    }    

    public function setRoute($route)
    {
        $this->_route = $route;
    }

    public function bindParam($method = "ALL")
    {
        switch ($method) {
            case "GET":
            case "DELETE":
                $this->_param = $this->request->query();
                break;
            case "POST":
            case "PUT":
                $this->_param = $this->request->post();
                break;
            case "ALL":
                $this->_param = $this->request->all();
                break;
        }
    }

    public function __call($method, $args){
        if(method_exists($this->request,$method)){        
            return $this->request->{$method}();
        }
        return false;       
    }

}