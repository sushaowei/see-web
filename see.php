<?php
/**
 * Created by PhpStorm.
 * User: ch168mk
 * Date: 16/7/4
 * Time: 下午5:08
 */
define("ROOT",__DIR__);
set_time_limit(0);
require('./see/See.php');
$config = require('./config/console.php');
if( file_exists('./config/console-local.php')){
    $local = require ('./config/console-local.php');
    $config = array_merge($config, $local);
}

$app = new \see\console\Application($config);
$app->run($argv);

