
/otxghtgl2018.php
administrator
pax2017htgl

#  编码正则匹配错误导致html修改异常

Core\Application\Home\Controller

IndexQueryController.class.php  约224行 235行

IndexCurlController.class.php  约250行 290行

    两处替换
    /*preg_match('/<meta[^>]+charset="?([^>]*?)["|\']([^>]*)?\/?>/is',$html,$match);//判断meta标签里的编码*/
    preg_match( '/<meta[^>]+charset=["|\']?([^"^\'^>]*)["|\']?([^>]+)?\/?>/is',$html,$match);//判断meta标签里的编码
    
    /*$html = preg_replace('/<meta[^>]+charset="?([^>]*?)["|\']([^>]*)?\/?>/i','<meta charset="utf-8">',$html);*/
    $html = preg_replace( '/<meta[^>]+charset=["|\']?([^"^\'^>]*)["|\']?([^>]+)?\/?>/i','<meta charset="utf-8">',$html);


# 乱码

IndexQueryController 约103行 

增加 header("Content-Type:text/html;charset=[code]");

增加方法 getHtmlCode($html);

# 发布内容

> 设置

- 导航xpath
- 页面模板网址
- 页面内容xpath

> 过滤Url (非镜像站网址)

> 根据导航Xpath 添加导航
> 获取缓存的模板 根据设置的xpath 替换内容

