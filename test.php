<?php
$url = 'http://www.guangming.com/';

$headers = [ 
    'Accept'=>'text/html,application/xhtml+xml,application/xml;q=0.9,image/webp,image/apng,*/*;q=0.8',
    'Accept-Language'=>'zh-CN,zh;q=0.9',
    'Cache-Control'=>'max-age=0',
    'Connection'=>'keep-alive',
    'Host'=>'www.guangming.com',
    'Upgrade-Insecure-Requests'=>'1',
];
echo CURLOPT_URL,"\n" ,CURLOPT_RETURNTRANSFER,"\n",CURLOPT_TIMEOUT,"\n",CURLOPT_USERAGENT,"\n",CURLOPT_REFERER;exit;
// exit('1');

// print_r($headers);

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
// curl_setopt ($ch, CURLOPT_FOLLOWLOCATION, 1);
curl_setopt($ch, CURLOPT_TIMEOUT,30); //超时设置，30s
// curl_setopt($ch, CURLOPT_HTTPHEADER,$headers); 
curl_setopt($ch, CURLOPT_REFERER, 'https://www.baidu.com');   
// curl_setopt($ch, CURLOPT_ENCODING, 'gzip, deflate');    
curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 6.1; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/67.0.3396.99 Safari/537.36');
// curl_setopt($ch, CURLOPT_COOKIE, 'ASP.NET_SessionId=fcsfd0qhstqzjzjww1u1lw45; Hm_lvt_ea6173e557faa14ff9b8cc539323df10=1533869520; UM_distinctid=16521bf78d80-0d23c2dccedd32-5b193413-1fa400-16521bf78d91a7; CNZZDATA1253175524=1211172296-1533865051-%7C1533865051; looyu_id=c7a9767ff51dc6022e05b6af0aa5ee65_10030772%3A1; looyu_10030772=v%3Ac7a9767ff51dc6022e05b6af0aa5ee65%2Cref%3A%2Cr%3A%2Cmon%3Ahttp%3A//m9106.talk99.cn/monitor%2Cp0%3Ahttp%253A//www.guangming.com/; IESESSION=alive; _qddaz=QD.88v3ja.t8490i.jkneb7zx; _qddab=3-4r16nv.jkneb802; pgv_pvi=9360787456; pgv_si=s7320617984; tencentSig=6117554176; Hm_lpvt_ea6173e557faa14ff9b8cc539323df10=1533870103');
$contents = trim(curl_exec($ch));
$filetype = curl_getinfo($ch, CURLINFO_CONTENT_TYPE); //获取请求文件类型


curl_close($ch); 
header('Content-Type: '.$filetype); // 根据文件类型修改响应请求
echo $contents;
