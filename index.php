<?php
// 引入F3框架并初始化实例
require __DIR__ . '/../fatfree-core/base.php';
$f3 = \Base::instance();

$f3->set('AUTOLOAD','app/');
$f3->set('UI', 'ui/');
$f3->set('DEBUG', 3);

// 全局配置（使用F3配置管理）站点基本信息
$f3->set('SITE_NAME', '影视资源');
$f3->set('SITE_DOMAIN', 'demo.test');
$f3->set('SITE_EMAIL', 'admin@admin.com');

// API配置，格式为：API名称#API地址
$f3->set('API_URL_1', '豪华资源#https://hhzyapi.com/api.php/provide/vod/at/xml');
$f3->set('API_URL_2', '无尽资源#https://api.wujinapi.me/api.php/provide/vod/from/wjm3u8/at/xml/');
$f3->set('API_URL_3', '红牛资源#https://www.hongniuzy2.com/api.php/provide/vod/at/xml/');
$f3->set('API_URL_4', '如意资源#https://cj.rycjapi.com/api.php/provide/vod/at/xml/');

// 视频解析器配置
$f3->set('VIDEO_PARSER', 'https://hhjiexi.com/play/?url=');
// 模板名称配置
$f3->set('TEMPLATE_NAME', 'default3');
// 排序方式配置yes倒序/no正常
$f3->set('SORT_DESC', 'no');
// 时间限制配置
$f3->set('SHOW_TIME_LIMIT', '');

// 5. SEO模板配置
// 配置不同页面类型的SEO标题、关键词和描述
$f3->set('SEO_TITLE', [
    'list' => '{{@CURRENT_CATEGORY}} - {{@SITE_NAME}}',
    'search' => '{{@SEARCH_KEYWORD}}的搜索结果 - {{@SITE_NAME}}',
    'info' => '{{@VIDEO_NAME}} - {{@SITE_NAME}}'
]);
$f3->set('SEO_KEYWORDS', [
    'list' => '{{@CURRENT_CATEGORY}},最新电影,最新电视,最新综艺,最新动漫',
    'search' => '{{@SEARCH_KEYWORD}},最新电影,最新电视,最新综艺,最新动漫',
    'info' => '{{@VIDEO_NAME}},最新电影,最新电视,最新综艺,最新动漫'
]);
$f3->set('SEO_DESCRIPTION', [
    'list' => '{{@SITE_NAME}}提供最新的电影、电视、综艺、动漫在线播放服务',
    'search' => '{{@SITE_NAME}}提供{{@SEARCH_KEYWORD}}的在线播放服务',
    'info' => '{{@SITE_NAME}}提供{{@VIDEO_NAME}}的在线播放服务'
]);

// 首页路由
$f3->route('GET|POST /', 'Home::index');

// 运行F3应用
$f3->run();