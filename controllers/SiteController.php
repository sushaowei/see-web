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
        $data = ['title'=>'首页','text'=>'Hello!1'];
        echo $this->request->get("e","",'xss_clean');
        $test = new Test();
        return $this->render("index", $data);
    }

    public function actionAbout(){
        var_dump(\See::$app->user);
        echo $this->event,"\n";
    	return "about";
    }

    public function actionContent(){
        $t = new Test();
        $t->fun = function(){
            echo $this->name;
            echo "xxxxx";
        };
        $fun = $t->fun;
        $fun();
        return "content";
    }

    public function actionCachetest(){
        // $fileCache = \See::$app->getFileCache();
        // $fileCache->set("test","test content1");
        // // $fileCache->gc();
        // $value = $fileCache->get('test');
        // return $value;
    }
}

class Test{
    public $name = 'test';
}

