<?php
// 首页控制器
class Home extends Controller {
    // 首页方法
    public function index($f3) {
        // 生成json测试数据，包含中文
        $data = array(
            'name' => '张三',
            'age' => 18,
            'sex' => '男'
        );
        // 输出json数据
        $json = json_encode($data, JSON_UNESCAPED_UNICODE);

        $f3->set('TITLE', 'F3+Vue3 测试首页');

        // 传递数据到模板
        $f3->set('data', $json);
        // 发送到模板
        $this->view->render('app/home.htm');
        
    }
}