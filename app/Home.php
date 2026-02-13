<?php
class Home extends Controller {
    public function index($f3) {
        $f3->set('siteName', 'F3模板测试站');
        $f3->set('user', [
            'name' => '张三 <测试>',
            'loggedIn' => true,
            'role' => 'admin',
            'age' => 28
        ]);
        $f3->set('isActive', true);
        $f3->set('undefinedVar', null);
        $f3->set('jsVar', 'const app="test"');
        $f3->set('fruits', ['苹果','香蕉','橙子']);
        $f3->set('userData', ['姓名'=>'张三','邮箱'=>'zhangsan@test.com']);
        $f3->set('products', [
            'p001' => ['name'=>'小米手机','price'=>1999],
            'p002' => ['name'=>'华为平板','price'=>2999]
        ]);
        echo \Template::instance()->render('app/home.htm');
    }
}