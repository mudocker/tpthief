<?php
namespace Home\Controller;
use OT\DataDictionary;
use QL\QueryList;
use \Curl\Curl;
use \Curl\MultiCurl;
/**
 * 获取远程页面内容
 * 主要获取首页聚合数据
 */
class QuerylistController extends HomeController {

    private $userAgent = 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/66.0.3359.170 Safari/537.36';

    public function proxyip(){
        $conf = R('Loadconfig/webconfig');
        if($conf['PROXY_ON'] == 0){
            return false;
        }else{
            if($conf['PROXY_TYPE'] == 'api'){
                $api = $conf['PROXY_API'];
                $ip = file_get_contents($api);
            }elseif($conf['PROXY_TYPE'] == 'ip'){
                $ip = $conf['PROXY_IP'];
            }
            return $ip;
        }
    }


    public function getWebHtml($url){
        /*
         * 获取目标站编码
         */
        $cjconf = R('Loadconfig/config');
        $charset_id =$cjconf['webcharset'];
        $codesarr = C('WEBCHARSETS');
        $webcharset = $codesarr[$charset_id];
        $rules = array();
        $data = QueryList::Query($url,$rules,'',$webcharset,$webcharset)->getHtml($rel = false);
        return($data);
    }
    //测试
    public function demo($url){
        //获取网页代码
        $curl = new Curl();
        //$curl->setOpt(CURLOPT_HEADER, true); //头部响应
        //代理
        $proxyip = $this->proxyip();
        if($proxyip) {
            $proxy = explode('@',$proxyip);
            $curl->setOpt(CURLOPT_HTTPPROXYTUNNEL, 1);
            $curl->setOpt(CURLOPT_PROXY, $proxy[0]);
            $curl->setOpt(CURLOPT_PROXYUSERPWD, $proxy[1]);
        }
        $curl->setTimeout(20);
        $curl->setUserAgent($this->userAgent);
//        $curl->setHeader('X-Requested-With', 'XMLHttpRequest');
        $curl->setReferrer('https:/www.baidu.com/');
        $curl->get($url);
        if ($curl->error) {
            echo 'Error: ' . $curl->errorCode . ': ' . $curl->errorMessage . "\n";
            return false;
        } else {
            $html = $curl->response;
        }
        $response =$curl->responseHeaders;
        $content_type = $response['content-type'];
        header('Content-Type: '.$content_type);
        return $html;
    }
    //下载css文件
    public function getFileCss($html){
        $config = R('Loadconfig/config');
        $baseurl = $config['weburl'];	//目标站网址
        $webconfig = R('Loadconfig/webconfig');
        $myweburl = $webconfig['WEB_URL'];//当前网址
        /*
         * 获取页面中所有css链接
        */
        $data = QueryList::Query($html,array(
            'css' => array('link','href')
        ))->getData(function($item) use($baseurl,$myweburl){
            $extension = get_extension($item['css']);
            if($extension != 'css') return false;
            $res = str_replace($myweburl,'',$baseurl.$item['css']);
            return $res;
        });
        $data = array_unique(array_filter($data));

        $multi_curl = new MultiCurl();
        $multi_curl->setUserAgent($this->userAgent);
        //$multi_curl->setReferrer();
        foreach($data as $vo){
            $filename = str_replace(array($baseurl,'../','..\\'),'',$vo);
            $filepath = './Runtime/Html/css/';
            $randname = get_randname($filename,'css');//随机文件名
            if(!is_dir($filepath)){
                mkdir($filepath,0755,true);
            }
            $filename = $filepath . '/' . $randname;
            if(!file_exists($filename)) {
                $multi_curl->addDownload($vo, $filename);
            }
        }
        $multi_curl->start();
        return;
    }

    public function getFileImg($html){
        $config = R('Loadconfig/config');
        $baseurl = $config['weburl'];	//目标站网址
        $webconfig = R('Loadconfig/webconfig');
        $myweburl = $webconfig['WEB_URL'];//当前网址
        /*
         * 获取页面中所有图片链接
         */
        $data = QueryList::Query($html,array(
            'img' => array('img','src')
        ))->getData(function($item) use($baseurl,$myweburl){
            $extension = get_extension($item['img']);
            if(!in_array($extension,array('jpg','jpeg','gif','png'))) return false;
            $res = str_replace($myweburl,'',$baseurl.$item['img']);
            return $res;
        });
        $data = array_unique(array_filter($data));

        $multi_curl = new MultiCurl();
        $multi_curl->setUserAgent($this->userAgent);
        foreach($data as $vo){
            $filename = str_replace($baseurl,'',$vo);
            $filepath = './Runtime/Html/img/';
            $dir = pathinfo($filename,PATHINFO_DIRNAME);
            $dir = $filepath.$dir;
            if(!is_dir($dir)){
                mkdir($dir,0755,true);
            }
            if(!file_exists($filepath .$filename)){
                $multi_curl->addDownload($vo, $filepath . $filename);
            }
        }
        $multi_curl->start();
        return;
    }

    //下载css文件
    public function getFileJs($html){
        $config = R('Loadconfig/config');
        $baseurl = $config['weburl'];	//目标站网址
        $webconfig = R('Loadconfig/webconfig');
        $myweburl = $webconfig['WEB_URL'];//当前网址
        /*
         * 获取页面中所有js链接
        */
        $data = QueryList::Query($html,array(
            'js' => array('script','src')
        ))->getData(function($item) use($baseurl,$myweburl){
            $extension = get_extension($item['js']);
            if($extension != 'js') return false;
            $res = str_replace($myweburl,'',$baseurl.$item['js']);
            return $res;
        });
        $data = array_unique(array_filter($data));

        $multi_curl = new MultiCurl();
        $multi_curl->setUserAgent($this->userAgent);
        //$multi_curl->setReferrer();
        foreach($data as $vo){
            $filename = str_replace(array($baseurl,'../','..\\'),'',$vo);
            $filepath = './Runtime/Html/js/';
            $randname = get_randname($filename,'js');//随机文件名

            if(!is_dir($filepath)){
                mkdir($filepath,0755,true);
            }
            $filename = $filepath . '/' . $randname;
            if(!file_exists($filename)) {
                $multi_curl->addDownload($vo,$filename );
            }
        }
        $multi_curl->start();
        return;
    }




}