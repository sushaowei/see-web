<?php
namespace app\modules\test\events;
use see\event\BeforeAction;

class ModulesTestHandler extends \see\base\Object
{
	public function init(){
		$eventHandler = \See::$app->getEventHandler('TestHandler');
		BeforeAction::off([$eventHandler,'testHandler']);
		BeforeAction::on([$this,'sign']);
		
	}

	public function sign($event){
		echo "event sign\n";
		
	}
}