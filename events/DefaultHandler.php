<?php
namespace app\events;
use see\exception\NotFoundException;
use see\event\RouteResolved;
class DefaultHandler extends \see\base\Object
{
	public function init(){
		//监听事件
		RouteResolved::on([$this,'checkSign']);
		RouteResolved::on(["#^user/#"],[$this,'initUser']);

		//接管404，重设组件 notFound
		$app = \See::$app;
		$app->set('notFound',function($e){
			// echo $e->getMessage();
			// echo \See::$app->getView()->render("//notFound");
			\See::$app->getResponse()->setData( \See::$app->getView()->render("//notFound") );
		});
	}
	//checksign
	public function checkSign(){
		//todo...
		if(!isset($_GET['sign'])){
			// throw new NotFoundException("Error sign", 1);
		}
	}

	//init user
	public function initUser($event){
		$app = \See::$app;
		$app->user = ['name'=>'jone'];
	}
}