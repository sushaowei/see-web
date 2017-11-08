<?php
namespace app\events;
use see\event\BeforeAction;
use see\event\BeforeRender;
class TestHandler extends \see\base\Object
{
	public function init(){
		BeforeAction::on(['#^site/\w+$#','#^test/[\w\/]+#','test'],function(){
			echo "event test \n";
		});
		BeforeAction::on("*",[$this,'testHandler'],false);
		BeforeRender::on(function($event){
			$view = $event->sender;
			$view = \See::$app->getView();
			$view->assign("r","r test");
		});
	}

	public function testHandler($event){
		$app = \See::$app;
		$app->controller->event='xxx';
		echo "event test testHandler\n";
		// $event->handled = true;//停止
	}
}