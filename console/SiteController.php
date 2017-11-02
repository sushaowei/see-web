<?php
namespace app\console;

use see\console\Controller;

class SiteController extends Controller
{
    public function actionIndex($a,$b){
        return "hello console $a,$b";
    }
}
