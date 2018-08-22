<?php




define('ROOT', dirname(__DIR__)); 

require ROOT."/vendor/autoload.php";

/**
 * 导航追加 / 替换 [index1,index2,...]
 * Xpath 导航块
 * 输入模板 | 定位模板
 * 单个导航内容模板 String | Xpath + 无关内容清理
 * 新导航 [后台内容分类ID,...]
 */


$xpath_setting = [
    'nav_ul' => "//div[@class='mnnavbg']/div[@class='header-menu']//ul[1]", //childElement 修改 href text 后 append
    'nav_a' => ".//a",
    'nav_clean' => ".//div[@class='menuson']",
    'category' => [  // 需要与后台的文章分类对应
        '百度' => 'http://baidu.com',
        '网易' => 'http://163.com',
    ],
];

$document = FluentDOM::load(
  'Runtime/Html/index.html',
  'text/html',
  [FluentDOM\Loader\Options::ALLOW_FILE => TRUE]
);
// print_r($document("//div[@class='mnnavbg']/div[@class='header-menu']/ul"));
// exit;
// $document->saveHtml()
foreach ($document($xpath_setting['nav_ul']) as  $key => $ul) {
//    $newLi = clone $ul->getFirstElementChild();
    foreach($xpath_setting['category'] as $catename => $cateUrl){
        $newLi = clone $ul->getLastElementChild();
        //先清除额外内容
        $c = getFirstDom($newLi($xpath_setting['nav_clean']));
        if($c) $c->remove();
        // 查找链接 修改内容和url
        $a = getFirstDom($newLi($xpath_setting['nav_a']));
        if(!$a)  continue;
        $a->nodeValue = $catename;
        $a->setAttribute('href',$cateUrl);
        // 添加到导航最后一个
        $ul->appendChild($newLi);
    }
    
//    echo $ul->saveHtml();
//    echo $document->saveHtml();
    
    
//    print_r($newLi(".//a")[0]->saveHtml());
    //echo $a->nodeValue = $key;
    // print_r();
//  $links[] = [
//    'caption' => (string)$a,
//    'href' => $a['href']
//  ];
}
echo $document->saveHtml();
function getFirstDom(\DOMNodeList $a){
    if($a->length > 0)
        return $a[0];
    return false;
}
 /* 
 * 
 * 
$ch = curl_init('http://www.cfmcc.com');
//curl_setopt($ch, CURLOPT_TIMEOUT, 50);
//curl_setopt($ch,CURLOPT_USERAGENT,'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/66.0.3359.170 Safari/537.36');
//curl_setopt($ch,CURLOPT_REFERER, 'https:/www.baidu.com/');
//curl_setopt($ch,CURLOPT_CUSTOMREQUEST, 'GET');
//curl_setopt($ch,CURLOPT_HTTPGET, true);
//curl_setopt($ch,CURLOPT_RETURNTRANSFER, true);
//curl_setopt($ch,CURLINFO_HEADER_OUT, true);
$output = curl_exec($ch);
echo $output;
$info = curl_getinfo($ch);
echo curl_errno($ch);
echo curl_error($ch);
print_r($info);
curl_close($ch);
 * 
*/