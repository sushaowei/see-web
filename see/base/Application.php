<?php
/**
 * Created by PhpStorm.
 * User: ch168mk
 * Date: 16/7/5
 * Time: 上午10:34
 */
namespace see\base;

use see\exception\ErrorException;
use see;

/**
 * Class Application
 * @package see\base
 */
abstract class Application extends Module
{

    /**
     * @var string
     */
    public $controllerNamespace = 'app\\controllers';

    /**
     * @var
     */
    public $id;

    /**
     * @var string
     */
    public $name = 'My Application';


    public $envDev=false;

    /**
     * @var string
     */
    public $version = '1.1';

    /**
     * @var string
     */
    public $charset = 'UTF-8';
    
    /**
     * @var see\web\Controller
     */
    public $controller;

    /**
     * @var
     */
    public $requestedRoute;

    /**
     * @var
     */
    public $requestedAction;

    /**
     * @var
     */
    public $requestedParams;

    /**
     * @var array
     */
    public $bootstrap = [];

    /**
     * @var array
     */
    public $loadedModules = [];

    /**
     * @var
     */
    public $runtimePath;

    /**
     * params
     */
    public $params=[];

    /**
     * Application constructor.
     * @param array $config
     */
    public function __construct(array $config=[])
    {
        \See::$app = $this;
        static::setInstance($this);
        $this->preInit($config);
        
        Component::__construct($config);
    }

    /**
     * @param $config
     * @throws ErrorException
     */
    public function preInit(&$config)
    {
        if (!isset($config['id'])) {
            throw new ErrorException('The "id" configuration for the Application is required.');
        }
        if (isset($config['basePath'])) {
            $this->setBasePath($config['basePath']);
            unset($config['basePath']);
        } else {
            throw new ErrorException('The "basePath" configuration for the Application is required.');
        }

      
        if (isset($config['runtimePath'])) {
            $this->setRuntimePath($config['runtimePath']);
            unset($config['runtimePath']);
        } else {
            // set "@runtime"
            $this->runtimePath = $this->getBasePath() . DIRECTORY_SEPARATOR . "runtime";
            See::setAlias('@runtime', $this->runtimePath);
        }
        
        if (isset($config['timeZone'])) {
            date_default_timezone_set($config['timeZone']);
            unset($config['timeZone']);
        } elseif (!ini_get('date.timezone')) {
            date_default_timezone_set('PRC');
        }

        $this->setComponents($config);
        unset($config['components']);
    }

    /**
     * @return array
     */
    public function coreComponents()
    {
        return [
            'view' => ['class' => 'see\base\View'],
            'errorHandler' => ['class' => 'see\base\ErrorHandler'],
            'log' => ['class' => 'see\base\Logger'],
            'db' => ['class' => 'see\db\PdoMysql'],
            'cache' => ['class' => 'see\base\Memcached'],
            
        ];
    }

    /**
     * @param $config
     * @throws ErrorException
     */
    protected function setComponents($config){
        foreach ($this->coreComponents() as $id => $component) {
            if (!isset($config['components'][$id])) {
                $config['components'][$id] = $component;
            } elseif (is_array($config['components'][$id]) && !isset($config['components'][$id]['class'])) {
                $config['components'][$id]['class'] = $component['class'];
            }
        }
        if(!empty($config['components'])){
            foreach($config['components'] as $id=>$component){
                $this->set($id,$component);
            }
        }
    }

    /**
     * @throws ErrorException
     * @throws \ErrorException
     */
    protected function bootstrap()
    {
        foreach ($this->bootstrap as $class) {
            $component = null;
            if (is_string($class)) {
                if ($this->has($class)) {
                    $component = $this->get($class);
                } elseif ($this->hasModule($class)) {
                    $component = $this->getModule($class);
                } elseif (strpos($class, '\\') === false) {
                    throw new ErrorException("Unknown bootstrapping component ID: $class");
                }
            }
            if (!isset($component)) {
                See::createObject($class);
            }
        }
        
        
    }

    /**
     * @throws ErrorException
     */
    public function init(){
        $this->bootstrap();
        $errorHandler = $this->getErrorHandler();
        $errorHandler->register();
        \See::$log = $this->getLog();
    }

    /**
     * @return ErrorHandler
     * @throws \ErrorException
     */
    public function getErrorHandler()
    {
        return $this->get('errorHandler');
    }

    /**
     * @return mixed|object
     * @throws \ErrorException
     */
    public function getLog()
    {
        return $this->get('log');
    }

    /**
     * @return string
     */
    public function getUniqueId()
    {
        return '';
    }

    /**
     * @param $path
     * @throws ErrorException
     */
    public function setBasePath($path)
    {
        parent::setBasePath($path);
        \See::setAlias('@app', $this->getBasePath());
    }

    /**
     * @return int
     * @throws ErrorException
     */
    public function run()
    {


        $response = $this->handleRequest($this->getRequest());


        $response->send();

        See::$log->notice('request completed, url:%s', $this->getRequest()->getUrl());
        
        return $response->exitStatus;
    }

    /**
     * @param $request
     * @return \see\web\Response
     */
    abstract public function handleRequest($request);

    /**
     * @param $path
     * @throws \Exception
     */
    public function setRuntimePath($path)
    {
        $this->runtimePath = See::getAlias($path);
        See::setAlias('@runtime', $this->runtimePath);
    }

    /**
     * @return see\web\Request | see\console\Request
     * @throws \ErrorException
     */
    public function getRequest()
    {
        return $this->get('request');
    }

    /**
     * @return \see\web\Response
     */
    public function getResponse()
    {
        return $this->get('response');
    }

    /**
     * @return View
     * @throws \ErrorException
     */
    public function getView()
    {
        return $this->get('view');
    }

    /**
     * @return see\db\PdoMysql
     * @throws \ErrorException
     */
    public function getDb(){
        return $this->get('db');
    }

    /**
     * @return mixed|object|Memcached
     * @throws \ErrorException
     */
    public function getCache(){
        return $this->get('cache');
    }
}