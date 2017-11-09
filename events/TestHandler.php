<?php
namespace app\events;
use see\event\BeforeAction;
use see\event\BeforeRender;
use see\event\EventException;
class TestHandler extends \see\base\Object
{
	public function init(){
		// BeforeAction::on(['#^site/\w+$#','#^test/[\w\/]+#','test'],function(){
		// 	echo "event test \n";
		// });
		BeforeAction::on("*",[$this,'testHandler'],false);
		// BeforeRender::on(function($event){
		// 	$view = $event->sender;
		// 	$view = \See::$app->getView();
		// 	$view->assign("r","r test");
		// 	$params= $view->params;
		// });


		EventException::on(function(){
			echo "event exception\n";
		});
	}

	public function testHandler($event){
		$app = \See::$app;
		$app->controller->event='xxx';
		$route = \See::$app->requestedRoute;
		echo "{$route} event test testHandler\n";
		// $event->handled = true;//停止
	}
}