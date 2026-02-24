<?php
// 引入F3框架并初始化实例
require __DIR__ . '/../fatfree-core/base.php';
$f3 = \Base::instance();

$f3->set('AUTOLOAD','app/');
// $f3->set('UI', 'ui/');
$f3->set('DEBUG', 3);

$f3->set('apiurls', [
'id1' => ['name'=>'小米资源','url'=>'https://demo.com'],
'id2' => ['name'=>'红米资源','url'=>'https://demo.com']
]);

// 测试路由
$f3->route('GET /test', function($f3) {
    $apiurl = $f3->get('apiurls');
    $f3->set('apiurl', $apiurl);
    $f3->set('apiurl_base64', base64_encode(json_encode($apiurl)));

    $f3->set('siteName', '资源管理平台');
    echo \Template::instance()->render('demo.htm');
});

// 首页路由
$f3->route('GET|POST /', 'Home->index');

// 运行F3应用
$f3->run();