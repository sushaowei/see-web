<?php
namespace see\event;
use see\exception\NotFoundException;
class defaultHandler extends \see\base\Object
{
	public function init(){
		Event::on("see\web\Application","EventException",function($event){
			$code = $event->e->getCode();
			if($code == 301){
				//重定向 收尾
				$response = \See::$app->getResponse();
				$response->setStatusCode(301);
				$response->data = "";//不作任何输出
			}else{
				throw new NotFoundException("Page not found", 404);
			}
		});
	}
}