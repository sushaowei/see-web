<?php
/**
 * Created by PhpStorm.
 * User: ch168mk
 * Date: 16/7/4
 * Time: 下午6:39
 */
namespace see\base;
use see;
use see\exception\NotFoundException;
use see\exception\ErrorException;
class Module extends ServiceLocation
{
    protected $id;
    
    public $modules = [];
    
    public $defaultRoute = 'default';
    /**
     * @var Module
     */
    public $father;
    
    private $basePath;

    public $controllerNamespace;
    
    public $viewPath;
    
    public function __construct($id,$module,array $config=[])
    {
        $this->id = $id;
        $this->father = $module;
        
        parent::__construct($config);
    }
    
    public function init(){
        if($this->controllerNamespace === null){
            $class = get_class($this);
            if(($pos = strrpos($class,'\\'))!== false){
                $this->controllerNamespace =  substr($class, 0, $pos) .  '\\controllers';
            }
        }

    }
    /**
     * 获取当前的模块对象
     * @return static|null the currently requested instance of this module class, or null if the module class is not requested.
     */
    public static function getInstance()
    {
        $class = get_called_class();
        return isset(See::$app->loadedModules[$class]) ? See::$app->loadedModules[$class] : null;
    }

    /**
     * 保存当前模块到See::$app的属性中
     * @param Module|null $instance the currently requested instance of this module class.
     */
    public static function setInstance($instance)
    {
        if ($instance === null) {
            unset(See::$app->loadedModules[get_called_class()]);
        } else {
            See::$app->loadedModules[get_class($instance)] = $instance;
        }
    }
    
    public function getBasePath(){
        if($this->basePath === null){
            $reflection = new \ReflectionClass($this);
            return $this->basePath = dirname($reflection->getFileName());
        }
        return $this->basePath;
    }

    public function setBasePath($path)
    {
        $path = See::getAlias($path);
        $p = strncmp($path, 'phar://', 7) === 0 ? $path : realpath($path);
        if ($p !== false && is_dir($p)) {
            $this->basePath = $p;
        } else {
            throw new ErrorException("The directory does not exist: $path");
        }
    }
    
    public function getUniqueId(){
        return $this->father ? ltrim($this->father->getUniqueId() . '/'. $this->id) : $this->id;
    }
    
    public function getControllerPath(){
        return \See::getAlias('@' . str_replace('\\', '/', $this->controllerNamespace));
    }

    public function getViewPath()
    {
        if ($this->viewPath === null) {
            $this->viewPath = $this->getBasePath() . DIRECTORY_SEPARATOR . 'views';
        }
        return $this->viewPath;
    }

    public function hasModule($id)
    {
        if (($pos = strpos($id, '/')) !== false) {
            // sub-module
            $module = $this->getModule(substr($id, 0, $pos));

            return $module === null ? false : $module->hasModule(substr($id, $pos + 1));
        } else {
            return isset($this->modules[$id]);
        }
    }

    public function getModule($id, $load = true)
    {
        if (($pos = strpos($id, '/')) !== false) {
            // sub-module
            $module = $this->getModule(substr($id, 0, $pos));

            return $module === null ? null : $module->getModule(substr($id, $pos + 1), $load);
        }

        if (isset($this->modules[$id])) {
            if ($this->modules[$id] instanceof Module) {
                return $this->modules[$id];
            } elseif ($load) {
                /* @var $module Module */
                $module = See::createObject($this->modules[$id], [$id, $this]);
                $module->setInstance($module);
                return $this->modules[$id] = $module;
            }
        }

        return null;
    }

    public function getModules($loadedOnly = false)
    {
        if ($loadedOnly) {
            $modules = [];
            foreach ($this->modules as $module) {
                if ($module instanceof Module) {
                    $modules[] = $module;
                }
            }

            return $modules;
        } else {
            return $this->modules;
        }
    }
    public function runAction($route, $params = [])
    {
        \See::$app->requestedRoute = $route;
        $parts = $this->createController($route);
        if (is_array($parts)) {
            /* @var $controller Controller */
            list($controller, $actionID) = $parts;
            See::$app->controller = $controller;
            $result = $controller->runAction($actionID, $params);
            return $result;
        } else {
            $id = $this->getUniqueId();
            throw new NotFoundException('Unable to resolve the request "' . ($id === '' ? $route : $id . '/' . $route) . '".');
        }
    }

    public function createController($route)
    {
        if ($route === '') {
            $route = $this->defaultRoute;
        }

        // double slashes or leading/ending slashes may cause substr problem
        $route = trim($route, '/');
        if (strpos($route, '//') !== false) {
            return false;
        }

        if (strpos($route, '/') !== false) {
            list ($id, $route) = explode('/', $route, 2);
        } else {
            $id = $route;
            $route = '';
        }

        $module = $this->getModule($id);
        if ($module !== null) {
            return $module->createController($route);
        }

        if (($pos = strrpos($route, '/')) !== false) {
            $id .= '/' . substr($route, 0, $pos);
            $route = substr($route, $pos + 1);
        }
        $controller = $this->createControllerByID($id);
        if ($controller === null && $route !== '') {
            $controller = $this->createControllerByID($id . '/' . $route);
            $route = '';
        }
        return $controller === null ? false : [$controller, $route];
    }

    public function createControllerByID($id)
    {
        $pos = strrpos($id, '/');
        if ($pos === false) {
            $prefix = '';
            $className = $id;
        } else {
            $prefix = substr($id, 0, $pos + 1);
            $className = substr($id, $pos + 1);
        }

        if (!preg_match('%^[a-z][a-z0-9\\-_]*$%', $className)) {
            return null;
        }
        if ($prefix !== '' && !preg_match('%^[a-z0-9_/]+$%i', $prefix)) {
            return null;
        }

        $className = str_replace(' ', '', ucwords(str_replace('-', ' ', $className))) . 'Controller';
        $className = ltrim($this->controllerNamespace . '\\' . str_replace('/', '\\', $prefix)  . $className, '\\');
        if (strpos($className, '-') !== false || !class_exists($className)) {
            return null;
        }

        if (is_subclass_of($className, 'see\base\Controller')) {
            $controller = See::createObject($className, [$id, $this]);
            return get_class($controller) === $className ? $controller : null;
        } else {
            throw new ErrorException("Controller class must extend from \\see\\base\\Controller.");
        }
    }
}