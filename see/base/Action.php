<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2016/6/10 0010
 * Time: 下午 9:10
 */

namespace see\base;

/**
 * Controller 的 Action 对象
 * Class Action
 * @package see\base
 */
class Action extends Component
{

    public $id;
    /**
     * @var Controller
     */
    public $controller;
    
    public $actionMethod;
    
    public function __construct($id, $controller, $methodName, array $config=[])
    {
        $this->id = $id;
        $this->controller = $controller;
        $this->actionMethod = $methodName;
        parent::__construct($config);
    }
    
    public function getUniqueId(){
        return $this->controller->getUniqueId() . DIRECTORY_SEPARATOR . $this->id;
    }
    
    public function runWithParams($params){
        $args = $this->controller->bindActionParams($this, $params);
        if(\See::$app->requestedParams === null){
            \See::$app->requestedParams = $args;
        }
        return call_user_func_array([$this->controller, $this->actionMethod], $args);
    }
}