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

// 首页路由
$f3->route('GET|POST /', 'Home->index');

// 测试路由
$f3->route('GET /test', function($f3) {
    $f3->set('name','你好');
    $f3->set('user', [
        'name' => '张三 <测试>',
        'loggedIn' => true,
        'role' => 'admin',
        'age' => 28
    ]);
    $f3->set('isActive', true);
    $f3->set('undefinedVar', null);
    $f3->set('fruits', ['苹果','香蕉','橙子']);
    $f3->set('userData', ['姓名'=>'张三','邮箱'=>'zhangsan@test.com']);
    $f3->set('products', [
        'p001' => ['name'=>'小米手机','price'=>1999],
        'p002' => ['name'=>'华为平板','price'=>2999]
    ]);
    $f3->set('currentTime', date('Y-m-d H:i:s'));

    echo \Template::instance()->render('demo.htm');
});

// 运行F3应用
$f3->run();