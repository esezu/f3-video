<?php
// 首页控制器
class Home extends Controller {
    // 首页方法
    public function index($f3) {
    // 获取请求参数
    $infoId = $_GET['info'] ?? null; // 视频详情ID
    $searchKey = $_GET['key'] ?? null; // 搜索关键词
    $sortId = $_GET['sort'] ?? null; // 分类ID
    $page = max(1, (int)($_GET['page'] ?? 1)); // 页码，确保至少为1
    $apiSelect = $_GET['api'] ?? $_COOKIE['api_select'] ?? '1'; // API选择，从URL或Cookie获取，默认为1
    
    // 输入验证
    $infoId = htmlspecialchars((string)$infoId);
    $searchKey = htmlspecialchars((string)$searchKey);
    $sortId = htmlspecialchars((string)$sortId);
    $apiSelect = htmlspecialchars((string)$apiSelect);
    // 确保API选择是有效的数字
    if (!is_numeric($apiSelect) || $apiSelect < 1) {
        $apiSelect = '1';
    }

    // 确定页面类型
    $pageType = 'list'; // 默认页面类型为列表
    $uniqueId = $sortId ?? ''; // 唯一标识符
    
    // 根据请求参数确定页面类型
    if (!empty($infoId)) {
        $pageType = 'info'; // 详情页面
        $uniqueId = $infoId;
    } elseif (!empty($searchKey)) {
        $pageType = 'search'; // 搜索页面
        $uniqueId = urldecode($searchKey); // 解码搜索关键词
    }
    
    // 保存API选择到Cookie，有效期30天
    setcookie('api_select', $apiSelect, time() + 86400 * 30, '/');
    
    // 获取默认API配置
    $defaultApiConfig = $f3->get('API_URL_1');
    // 解析API配置
    list($defaultApiName, $defaultApiUrl) = explode('#', $defaultApiConfig);
    
    // 获取当前API配置
    $apiConfig = $f3->get('API_URL_' . $apiSelect);
    if ($apiConfig) {
        // 解析API配置
        list($apiName, $apiUrl) = explode('#', $apiConfig);
    } else {
        // 如果没有找到API配置，使用默认值
        $apiName = $defaultApiName;
        $apiUrl = $defaultApiUrl;
        $apiSelect = '1';
    }
    
    // 获取其他配置
    $showTimeLimit = $f3->get('SHOW_TIME_LIMIT');
    $sortDesc = $f3->get('SORT_DESC');
    $videoParser = $f3->get('VIDEO_PARSER');
    $templateName = $f3->get('TEMPLATE_NAME');
    $baseUrl = $f3->get('SCHEME') . '://' . $f3->get('HOST') . ':' . $f3->get('PORT');
    
    // 构建API列表
    $apiList = [];
    $i = 1;
    while (true) {
        // 获取API配置
        $config = $f3->get('API_URL_' . $i);
        // 如果没有更多API配置，退出循环
        if (!$config) {
            break;
        }
        // 解析API配置
        list($name, $url) = explode('#', $config);
        // 添加到API列表
        $apiList[] = ['id' => $i, 'name' => $name, 'url' => $url];
        $i++;
    }
    
    // 获取分类数据
    $categories = handleDataCache('category', '', 1, $apiUrl, $showTimeLimit, $sortDesc);
    
    // 获取视频数据
    $videoData = handleDataCache($pageType, $uniqueId, $page, $apiUrl, $showTimeLimit, $sortDesc);
    
    // 处理详情页面
    $videoInfo = [];
    $videoList = [];
    $playerScript = '';
    
    if ($pageType === 'info') {
        // 安全处理视频信息数据结构
        if (isset($videoData['list']) && isset($videoData['list']['video'])) {
            $videoInfo = $videoData['list']['video'];
            // 确保videoInfo是数组
            if (!is_array($videoInfo)) {
                $videoInfo = [];
            }
        }
        
        // 保留原始的dl数据结构，供JS使用
        if (!isset($videoInfo['dl'])) {
            $videoInfo['dl'] = ['dd' => ''];
        }
        
        // 在PHP端处理播放源，优先使用m3u8的播放源
        $dlData = $videoInfo['dl']['dd'] ?? [];
        $selectedDd = null;
        
        if (is_array($dlData)) {
            foreach ($dlData as $ddItem) {
                if (is_string($ddItem)) {
                    // 检查是否包含m3u8
                    if (stripos($ddItem, 'm3u8') !== false) {
                        $selectedDd = $ddItem;
                        break;
                    }
                } elseif (is_array($ddItem) && isset($ddItem['@attributes']['flag'])) {
                    // 检查flag是否包含m3u8
                    if (stripos($ddItem['@attributes']['flag'], 'm3u8') !== false) {
                        $selectedDd = $ddItem;
                        break;
                    }
                }
            }
            // 如果没有找到m3u8，使用第一个播放源
            if ($selectedDd === null && !empty($dlData)) {
                $selectedDd = $dlData[0];
            }
        }
        
        // 使用选中的播放源
        if ($selectedDd !== null) {
            $videoInfo['dl']['dd'] = is_array($selectedDd) ? [$selectedDd] : $selectedDd;
        }
        
        // 生成JavaScript代码（压缩成一行）
        $playerScript = "<script>var bflist=" . json_encode($videoInfo['dl']) . ";var jx='" . $videoParser . "';function bf(str){document.getElementById(\"iframe\").style.display=\"block\";document.getElementById(\"frame\").src=jx+str;}if(Array.isArray(bflist['dd'])){var viddz=bflist['dd'];}else{var viddz=new Array(bflist['dd']);}var bfmoban=document.getElementById(\"playlist\").innerHTML;document.getElementById(\"playlist\").innerHTML='';for(var i in viddz){viddz[i]=viddz[i].split(\"#\");for(var k=0;k<viddz[i].length;k++){viddz[i][k]=viddz[i][k].split(\"$\");if(viddz[i][k][2]==undefined){var zyname='yun';}else{var zyname=viddz[i][k][2];}if(k=='0'){document.getElementById(\"playlist\").innerHTML+=bfmoban.replace(/资源加载中/g,zyname);var jjmoban=document.getElementById(\"zylx\"+zyname).innerHTML;var bfnr='';}bfnr=bfnr+jjmoban.replace(/剧集加载中/g,viddz[i][k][0]).replace(/剧集地址加载中/g,viddz[i][k][1]);}document.getElementById(\"zylx\"+zyname).innerHTML=bfnr;}</script>";
    } else {
        // 处理列表和搜索页面
        // 安全处理视频列表数据结构
        if (isset($videoData['list']) && isset($videoData['list']['video'])) {
            $videoList = $videoData['list']['video'];
            // 确保videoList是数组
            if (!is_array($videoList)) {
                $videoList = [];
            }
        }
    }
    
    // 初始化分页数据
    $pagination = [
        'current' => $page, // 当前页码
        'prev' => 1, // 上一页
        'next' => 1, // 下一页
        'last' => 1, // 最后一页
        'first' => '' // 第一页
    ];
    
    // 从API响应中获取分页信息
    if (!empty($videoData['list']['@attributes']['pagecount'])) {
        $pageCount = (int)$videoData['list']['@attributes']['pagecount'];
        $pagination['last'] = $pageCount;
        $pagination['prev'] = max(1, $page - 1);
        $pagination['next'] = min($pageCount, $page + 1);
    }
    
    // 构建分页URL
    $pagination['firstUrl'] = buildPageUrl($pageType, $uniqueId, 1);
    $pagination['prevUrl'] = buildPageUrl($pageType, $uniqueId, $pagination['prev']);
    $pagination['nextUrl'] = buildPageUrl($pageType, $uniqueId, $pagination['next']);
    $pagination['lastUrl'] = buildPageUrl($pageType, $uniqueId, $pagination['last']);
    
    // 初始化SEO数据
    $seoData = [
        'CURRENT_CATEGORY' => '最近更新',
        'SEARCH_KEYWORD' => '',
        'VIDEO_NAME' => ''
    ];
    
    // 根据页面类型设置SEO数据
    if ($pageType === 'search') {
        $seoData['SEARCH_KEYWORD'] = $uniqueId;
    }
    
    if ($pageType === 'list') {
        // 查找当前分类名称
        foreach ($categories as $cate) {
            if ($cate['分类号'] == $uniqueId) {
                $seoData['CURRENT_CATEGORY'] = $cate['分类名'];
                break;
            }
        }
    }
    
    if ($pageType === 'info') {
        // 从视频信息中获取视频名称
        $seoData['VIDEO_NAME'] = $videoInfo['name'] ?? '';
    }
    
    // 获取UI路径
    $uiPath = $f3->get('UI');
    // 构建模板路径
    $templatePath = $uiPath . $templateName;
    
    // 根据页面类型设置要包含的模板
    $templateToInclude = $templateName . '/list.html';
    if ($pageType === 'info') {
        $templateToInclude = $templateName . '/info.html';
    } elseif ($pageType === 'search') {
        $templateToInclude = $templateName . '/search.html';
    }
    
    // 使用F3的set方法设置模板变量
    $f3->set('SITE_NAME', $f3->get('SITE_NAME'));
    $f3->set('CURRENT_API_NAME', $apiName);
    $f3->set('CURRENT_API_URL', $apiUrl);
    $f3->set('CURRENT_API', $apiSelect);
    $f3->set('API_LIST', $apiList);
    $f3->set('CATEGORIES', $categories);
    $f3->set('VIDEO_DATA', $videoList);
    $f3->set('VIDEO_INFO', $videoInfo);
    $f3->set('PLAYER_SCRIPT', $playerScript);
    $f3->set('PAGINATION', $pagination);
    $f3->set('CURRENT_CATEGORY', $seoData['CURRENT_CATEGORY']);
    $f3->set('SEARCH_KEYWORD', $seoData['SEARCH_KEYWORD']);
    $f3->set('VIDEO_NAME', $seoData['VIDEO_NAME']);
    $f3->set('VIDEO_PARSER', $videoParser);
    $f3->set('BASE', $baseUrl);
    $f3->set('TEMPLATE_PATH', $templatePath);
    $f3->set('TEMPLATE_TO_INCLUDE', $templateToInclude);
    
    // F3的Template::instance()->render()方法用于渲染模板
    echo \Template::instance()->render($templateName . '/indexs.html');
    }
}