<?php
// 控制器基类
class Controller {
    protected $f3;
    protected $view;
    // 构造函数，初始化f3实例
    public function __construct() {
        $this->f3 = \Base::instance();
        $this->view = new \View;
    }

}
