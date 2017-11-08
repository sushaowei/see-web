<?php
//重定向
function redirect($url){
	header("Location:{$url}");
	throw new \see\exception\EventException("redirect", 301);
}