<?php
// 引入F3框架并初始化实例
require __DIR__ . '/../fatfree-core/base.php';
$f3 = \Base::instance();

$f3->set('AUTOLOAD','app/');
// $f3->set('UI', 'ui/');
$f3->set('DEBUG', 3);

// 首页路由
$f3->route('GET|POST /', 'Home->index');
// JSON数据路由
$f3->route('GET /jsondata', 'Home->jsondata');

// 运行F3应用
$f3->run();