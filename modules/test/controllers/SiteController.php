<?php
namespace app\modules\test\controllers;

class SiteController extends \see\web\Controller
{
    public function actionIndex(){
    	echo $this->getViewPath();
        return "modules test ";
    }
}
