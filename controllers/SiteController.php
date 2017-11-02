<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2016/6/11 0011
 * Time: 下午 11:35
 */

namespace app\controllers;


use see\web\Controller;

/**
 * Class SiteController
 * @package app\controllers
 */
class SiteController extends Controller
{

    /**
     * 首页
     * @return string
     */
    public function actionIndex(){
        $data = ['title'=>'首页','text'=>'Hello See!'];
        return $this->render("index", $data);
    }
}