<?php

define('ROOT', dirname(__DIR__)); 

require ROOT."/vendor/autoload.php";


/**
 * 文章列表页模板
 * 列表块
 * 列模板输入
 * 
 * 
 */

$setting = [ 
    // 列表页面模板
    'template' => 'list.html',//'http://www.yantaiport.com.cn/index.php?m=content&c=index&a=lists&catid=25',
    'list_dom' => "//div[@class='right w715px']//div[@class='w653px pt15 m_tzhb2']",
    'li_html' => '<li><a href="<{url}>" class="pl15"><{title}></a><{date}></li>'
];

$document = FluentDOM::load(
  'list.html',
  'text/html',
  [FluentDOM\Loader\Options::ALLOW_FILE => TRUE]
);

$content = [
    ['url'=>'/index.php?id=1','title'=>'这不曾是我要的光明','date'=>'2016-05-26'],
    ['url'=>'/index.php?id=2','title'=>'还有多少话要说','date'=>'2016-05-27'],
];
 

foreach($document($setting['list_dom']) as $list_dom){
    while($a = $list_dom->getLastElementChild()){  
        $a->remove();
    }
    $li_html = '';
    foreach($content as $row){
        $keys = $values = [];
        while (list($key, $val) = each($row)){
            $keys[]='<{'.trim($key).'}>';
            $values[]=$val;
        }
        $li_html = str_replace($keys,$values, $setting['li_html']);
        $list_dom->appendXml('<a>'.$li_html.'</a>');
        
        $fragment = $list_dom->ownerDocument->createDocumentFragment();
        $fragment->appendXml($li_html.'22');
        $list_dom->getLastElementChild()->before($fragment);
        break;
    }
    
//    $fragment = $list_dom->getDocument()->createDocumentFragment();
//    $fragment->appendXml($xmlFragment);
//    $content[0]->before($fragment);
//    $list_dom->appendXml($li_html);
    /*
    创建Document 抽取出 所有的 Element(Node) 进行 append
    $li_dom = FluentDOM::load(  $li_html, 'text/html' );
    foreach($li_dom('//*[@node()]') as $a){
        print_r($a);
        $list_dom->append($a);
    }
    */

//     print_r($list_dom->getLastElementChild());
//     print_r($list_dom->getFirstElementChild());
//    print_r($list_dom->getLastElementChild()->remove());
    echo $list_dom->saveHtml();
//    echo $document->saveHtml();
    
}
