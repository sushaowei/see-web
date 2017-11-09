<?php
namespace app\modules\test\events;
use see\event\BeforeAction;
use see\event\EventException;
class ModulesTestHandler extends \see\base\Object
{
	public function init(){
		$eventHandler = \See::$app->getEventHandler('TestHandler');
		BeforeAction::off([$eventHandler,'testHandler']);
		BeforeAction::on([$this,'sign']);
		EventException::on(function(){
			echo "event exception\n";
		});
	}

	public function sign($event){
		echo "event sign\n";
		
	}
}