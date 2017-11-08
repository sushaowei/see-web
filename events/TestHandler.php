<?php
namespace app\events;
use see\event\BeforeAction;
class TestHandler extends \see\base\Object
{
	public function init(){
		BeforeAction::on(['#^site/\w+$#','#^test/[\w\/]+#','test'],function(){
			echo "event test \n";
		});
		BeforeAction::on("*",[$this,'testHandler'],false);
	}

	public function testHandler($event){
		echo "event test testHandler\n";
		$event->handled = true;//停止
	}
}