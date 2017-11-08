<?php
namespace see\event;
class RouteResolved extends EventHandler
{
	protected static $eventName = "RouteResolved";
	protected static $eventClass = 'see\\web\\Application';
}