<?php
/**
 * Created by PhpStorm.
 * User: ch168mk
 * Date: 16/7/4
 * Time: 下午5:08
 */
define('ROOT',dirname(__DIR__));

require __DIR__ . '/../vendor/autoload.php';

$config = require('../config/main.php');

$app = new \see\web\Application($config);
$app->run();
