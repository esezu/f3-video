<?php
// 首页控制器
class Home extends Controller {
    // 首页方法
    public function index($f3) {
        // 单个用户数据
        $userData = array(
            'name' => '张三',
            'age' => 18,
            'sex' => '男'
        );

        // 复杂数组（多用户数据）
        $users = array(
            array('name' => '李四', 'age' => 20, 'sex' => '女'),
            array('name' => '王五', 'age' => 22, 'sex' => '男')
        );

        // 注册所有需要的变量到模板
        $f3->set('user', $userData);
        $f3->set('users', $users);
        // 输出渲染结果
        echo $this->view->render('app/home.htm');
    }
    // 输出JSON数据
    public function jsondata($f3) {
        // 1. 构造要返回的数组数据（可替换为数据库查询结果等）
        $responseData = array(
            'code' => 200,
            'msg' => '请求成功',
            'data' => array(
                'name' => '张三',
                'age' => 18,
                'sex' => '男',
                'hobbies' => ['编程', '阅读', '运动']
            )
        );

        // 2. 设置响应头（关键：告诉前端这是JSON数据）
        header('Content-Type: application/json; charset=utf-8');
        // 可选：禁止缓存（避免前端获取旧数据）
        header('Cache-Control: no-cache, no-store, must-revalidate');

        // 3. 生成JSON字符串（保留中文，不转义Unicode）
        $jsonStr = json_encode($responseData, JSON_UNESCAPED_UNICODE);

        // 4. 输出JSON并终止脚本
        echo $jsonStr;
        exit; // 确保没有多余输出
    }
}