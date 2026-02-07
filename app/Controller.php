<?php
// 控制器基类
class Controller {
    protected $f3;
    // 构造函数，初始化f3实例
    public function __construct() {
        $this->f3 = \Base::instance();
    }
/**
 * 模拟国内IP生成X-Forwarded-For请求头
 * 使用F3的\Web::request()发送GET请求（替换原生cURL所有逻辑）
 * 
 * @param string $url API请求地址
 * @return string|false API响应内容或失败时返回false
 */
function fetchAPI($url) {
    // 定义国内IP段范围
    $ip_long = array(
        array('607649792', '608174079'), // 36.102.0.0-36.103.255.255
        array('1038614528', '1039007743'), // 61.232.0.0-61.233.255.255
        array('1783627776', '1784676351'), // 106.80.0.0-106.95.255.255
        array('2035023872', '2035154943'), // 121.76.0.0-121.77.255.255
        array('2078801920', '2079064063'), // 123.232.0.0-123.235.255.255
        array('-1950089216', '-1948778497'), // 139.196.0.0-139.215.255.255
        array('-1425539072', '-1425014785'), // 171.8.0.0-171.15.255.255
        array('-1236271104', '-1235419137'), // 182.80.0.0-182.92.255.255
        array('-770113536', '-768606209'), // 210.25.0.0-210.47.255.255
        array('-569376768', '-564133889') // 222.16.0.0-222.95.255.255
    );
    
    // 随机选择一个IP段，并生成该段内的随机IP
    $rand_key = mt_rand(0, 9);
    $ips = long2ip(mt_rand($ip_long[$rand_key][0], $ip_long[$rand_key][1]));
    
    // 构建请求头，包括模拟的X-Forwarded-For和用户代理
    $headers = [
        'X-Forwarded-For: ' . $ips,
        'User-Agent: ' . ($_SERVER['HTTP_USER_AGENT'] ?? 'Mozilla/5.0 (Unknown; Linux x86_64) AppleWebKit/537.36')
    ];
    
    // 构建请求选项
    $options = [
        'method' => 'GET', // 请求方法
        'header' => $headers, // 请求头
        'encoding' => 'gzip', // 支持gzip压缩
        'timeout' => 30 // 请求超时时间
    ];
    
    try {
        // 获取F3的Web实例
        $web = \Web::instance();
        // 发送HTTP请求
        $response = $web->request($url, $options);
        // 返回响应体
        return $response['body'];
    } catch (Exception $e) {
        // 记录错误但不终止脚本执行
        error_log("API请求失败: " . $e->getMessage() . " - URL: " . $url);
        return false;
    }

}

/**
 * 处理API数据获取和缓存
 * 
 * @param string $sort 数据类型：category(分类)、info(详情)、search(搜索)、list(列表)
 * @param string $id 数据ID，如分类ID、视频ID、搜索关键词
 * @param int $page 页码
 * @param string $apiUrl API URL
 * @param string $showTimeLimit 时间限制配置
 * @param string $sortDesc 排序方式配置
 * @return array 处理后的数据数组
 */
function handleDataCache($sort, $id, $page, $apiUrl, $showTimeLimit, $sortDesc) {
    
    $actualPage = $page;
    $requestUrl = '';
    
    // 根据数据类型构建请求URL
    switch ($sort) {
        case 'category':
            // 分类列表请求
            $requestUrl = $apiUrl . '?ac=list';
            break;
        case 'info':
            // 视频详情请求
            $requestUrl = $apiUrl . '?ac=videolist&ids=' . $id . $showTimeLimit;
            break;
        case 'search':
            // 搜索结果请求
            $requestUrl = $apiUrl . '?ac=videolist&wd=' . urlencode($id) . '&pg=' . $page . $showTimeLimit;
            break;
        case 'list':
            // 视频列表请求
            // 实时获取分页信息
            $pageUrl = $apiUrl . '?ac=videolist&t=' . $id . '&pg=' . $page . $showTimeLimit;
            $pageContent = fetchAPI($pageUrl);
            // 提取总页数
            preg_match('/pagecount="(\d+)"/', $pageContent, $matches);
            $pageCount = $matches[1] ?? 1;
            
            // 如果需要倒序排序，则计算实际页码
            if ($sortDesc === 'yes') {
                $actualPage = $pageCount - $page + 1;
            }
            // 构建实际请求URL
            $requestUrl = $apiUrl . '?ac=videolist&t=' . $id . '&pg=' . $actualPage . $showTimeLimit;
            break;
    }
    
    // 发送API请求获取数据
    $remoteData = fetchAPI($requestUrl);
    // 如果数据为空，返回空数组
    if (empty($remoteData)) {
        return [];
    }
    
    // 分类数据特殊处理
    if ($sort === 'category') {
        // 禁用libxml错误报告
        libxml_use_internal_errors(true);
        // 解析XML数据
        $xml = simplexml_load_string($remoteData);
        // 启用libxml错误报告
        libxml_use_internal_errors(false);
        // 如果解析失败，返回空数组
        if ($xml === false) {
            return [];
        }
        
        // 构建分类数组
        $categories = [];
        // 添加"最近更新"分类
        $categories[] = ['分类号' => '', '分类名' => '最近更新'];
        
        // 遍历XML中的分类数据
        if (isset($xml->class->ty)) {
            foreach ($xml->class->ty as $ty) {
                $categories[] = [
                    '分类号' => (string)$ty['id'],
                    '分类名' => (string)$ty
                ];
            }
        }
        
        return $categories;
    } else {
        // 清理数据，移除不必要的标签
        $cleanData = preg_replace('/<script(.*?)<\/script>|<span[^>]*?>|<\/span>|<p\s[^>]*?>|<p>|<\/p>/i', '', $remoteData);
        
        // 禁用libxml错误报告
        libxml_use_internal_errors(true);
        // 解析XML数据，使用LIBXML_NOCDATA选项保留CDATA内容
        $xml = simplexml_load_string($cleanData, 'SimpleXMLElement', LIBXML_NOCDATA);
        // 启用libxml错误报告
        libxml_use_internal_errors(false);
        // 如果解析失败，返回空数组
        if ($xml === false) {
            return [];
        }
        
        // 将XML转换为数组
        $xmlArray = json_decode(json_encode($xml), true);
        
        // 清理数组，保留flag属性，删除其他不必要的@attributes和空对象
        $cleanJson = json_encode($xmlArray);
        $cleanJson = preg_replace('/(?:,\{"@attributes":\{(?!.*"flag")[^}]*\}\}|\{"@attributes":\{(?!.*"flag")[^}]*\}\},)/', '', $cleanJson);
        $cleanJson = preg_replace('/,"([^"]*?)":\{(?:\{"0":""\}|\{\})/', '', $cleanJson);
        $cleanJson = str_replace(['" "'], ['""'], $cleanJson);
        
        // 将清理后的JSON转换回数组并返回
        return json_decode($cleanJson, true);
    }
}

/**
 * 构建页面URL
 * 
 * @param string $sort 页面类型：list(列表)、search(搜索)、info(详情)
 * @param string $id 数据ID
 * @param int $page 页码
 * @return string 构建好的URL
 */
function buildPageUrl($sort, $id = '', $page = 1) {
    // 构建URL参数
    $params = [];
    switch ($sort) {
        case 'list':
            $params['sort'] = $id;
            $params['page'] = $page;
            break;
        case 'search':
            $params['key'] = $id;
            $params['page'] = $page;
            break;
        case 'info':
            $params['info'] = $id;
            break;
    }
    
    // 获取F3实例
    $f3 = \Base::instance();
    // 获取BASE URL，如果未设置则构建
    $baseUrl = $f3->get('BASE');
    if (empty($baseUrl)) {
        $baseUrl = $f3->get('SCHEME') . '://' . $f3->get('HOST') . ':' . $f3->get('PORT');
    }
    // 构建完整URL并返回
    return $baseUrl . '?' . http_build_query($params);
}

}
