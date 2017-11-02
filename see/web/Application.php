<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2016/6/11 0011
 * Time: 上午 12:59
 */

namespace see\web;

use see\exception\NotFoundException;

/**
 * Class Application
 * @package see\web
 * @property UrlManager @urlManager
 */
class Application extends \see\base\Application
{
    public $defaultRoute = 'site';

    /**
     * @param \see\web\Request $request
     * @return mixed|Response|\see\web\Response
     */
    public function handleRequest($request)
    {
        try {
            \See::$log->addBasic('clientIp', $request->getRealUserIp());
            list($route, $params) = $request->resolve();
            $this->requestedRoute = $route;
            \See::$log->addBasic('route', $route);
            \See::$log->trace('trace route:%s', $route);
            $result = $this->runAction($route, $params);
            if ($request instanceof Response) {
                $response = $result;
            } else {
                $response = $this->getResponse();
                $response->data = $result;
            }
            \See::$log->addBasic('httpStatus', 200);
            $response->setStatusCode(200);
            return $response;
        } catch (NotFoundException $e) {
            $response = $this->getResponse();
            $response->notFoundSend($e);
            \See::$log->addBasic('httpStatus', 404);
            return $response;
        }

    }

    /**
     * @return \see\web\UrlManager
     */
    public function getUrlManager()
    {
        return $this->get('urlManager');
    }

    public function coreComponents()
    {
        return array_merge(parent::coreComponents(), [
            'urlManager' => ['class' => '\see\web\UrlManager'],
            'request' => ['class' => '\see\web\Request'],
            'response' => ['class' => '\see\web\Response'],
        ]);
    }

    /**
     * @inheritdoc
     */
    protected function bootstrap()
    {
        $request = $this->getRequest();
        \See::setAlias('@webroot', dirname($request->getScriptFile()));
        \See::setAlias('@web', $request->getBaseUrl());

        parent::bootstrap();
    }
}