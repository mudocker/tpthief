<?php
// +----------------------------------------------------------------------
// | OneThink [ WE CAN DO IT JUST THINK IT ]
// +----------------------------------------------------------------------
// | Copyright (c) 2013 http://www.onethink.cn All rights reserved.
// +----------------------------------------------------------------------
// | Author: 麦当苗儿 <zuojiazi@vip.qq.com> <http://www.zjzit.cn>
// +----------------------------------------------------------------------

namespace Home\Controller;
use OT\DataDictionary;

/**
 * 获取远程页面内容
 * 主要获取首页聚合数据
 */
class CurlController extends HomeController {


	public function getcontent($url){ 
		//判断是否有其他子域名
		$config = R('Loadconfig/config');
		if($config['subdomain']){
			$url = $this->checkSubdomain($url,$config);
		}
		$res = $this->curlget($url);
		if(!$res){
			return '404';
		}else{
			return $res;
		}
	}
	/**处理目标站有多个二级域名的情况**/
	public function checkSubdomain($url,$config){
		$mydomain = $_SERVER['HTTP_HOST'];
		$arr = explode('##########',$config['subdomain']);
		$arr = array_filter($arr);
		foreach($arr as $list){
			$strs = explode('******',$list);
			$find[] = trim($strs[0]);
			$replace[] = trim($strs[1]);
		}
		$geturl = I('get.');
		$url_param = explode('/',$geturl['parameter']);
		$param = $url_param[0];
		if(in_array($param,$replace)){
			$k = array_search($param,$replace);
			array_shift($url_param);
			$parameter = implode('/',$url_param);
			$url = $find[$k].'/'.$parameter;
		}
		return $url;
	
	}
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
	public function curlget($url){
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $url); 
		//模拟浏览器类型
		curl_setopt($ch, CURLOPT_USERAGENT,  "Mozilla/5.0 (Windows NT 5.1; zh-CN) AppleWebKit/535.12 (KHTML, like Gecko) Chrome/22.0.1229.79 Safari/535.12");
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt ($ch, CURLOPT_FOLLOWLOCATION, 1);
		curl_setopt($ch, CURLOPT_TIMEOUT,30);	//超时设置，30s
        $proxyip = $this->proxyip();
        if($this->proxyip()) {
			//curl_setopt($ch, CURLOPT_PROXY, $proxyip);
            $proxy = explode('@',$proxyip);
            curl_setopt($ch, CURLOPT_HTTPPROXYTUNNEL, 1);
            curl_setopt($ch, CURLOPT_PROXY, $proxy[0]);
            curl_setopt($ch, CURLOPT_PROXYUSERPWD, $proxy[1]);
		}
		$contents = trim(curl_exec($ch));
		$filetype = curl_getinfo($ch, CURLINFO_CONTENT_TYPE);	//获取请求文件类型
		curl_close($ch); 
		header('Content-Type: '.$filetype);		// 根据文件类型修改响应请求
		return $contents; 	
	}


}