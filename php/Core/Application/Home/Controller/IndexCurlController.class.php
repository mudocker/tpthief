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
 * 前台首页控制器
 * 主要获取首页聚合数据
 */
class IndexCurlController extends HomeController {

    public function _initialize(){
        $this->_config = R('Loadconfig/config');
    }
	public function index($parameter=''){
		//调用配置
		A('Loadconfig')->base();
        $config = $this->_config;
		$weburl = $config['weburl'];	//目标站网址
		//判断url参数
		if($parameter){
			$request_uri = $_SERVER['REQUEST_URI'];
			$geturl = $weburl.$request_uri;
		}else{
			$geturl = $weburl;
		}
        $webconfig = R('Loadconfig/webconfig');
        $action_model = $webconfig['ACTION_MODEL'];
        if($action_model == 1){
            $html = $this->html($weburl,$request_uri);
        }else{
            $html = $this->nohtml($weburl,$request_uri);
        }
		echo $html;
		return;
	}

	/**缓存配置**/
	public function getHconfig(){
		$dir = './Runtime/Config/';
		if(!is_dir($dir)) mkdir($dir,0755,true);		
		$file = $dir.'hconfig.php';
		$res = include($file);
		if(!$res || !is_array($res)){
			$info = M('HuancunConfig')->where('status=1')->field('name,value')->select();
			$str = '<?php'.PHP_EOL.'/*缓存配置文件*/'.PHP_EOL.'return array('.PHP_EOL;
			foreach($info as $list){
				$str .= " '".$list['name']."' => '".$list['value']."',".PHP_EOL;
			}
			$str .= ');';
			file_put_contents($file,$str);
			$res = $info;
		}
		return $res;
	}

	//纯动态
	public function nohtml($weburl,$parameter=''){
        $config = $this->_config;
        if($config['url_rewrite_on'] == 1) $parameter = get_url_before_rewrite($parameter,$config['url_rewrite_rules']);
        if($config['forcedurl'] == 1) $parameter = url_convert($parameter,1);
		$geturl = $weburl.$parameter;
        $geturl = preg_replace('/([^:])\/\//is','${1}/',$geturl);//处理url中的"//"
        $extension = get_extension($geturl);
        $content = R('Curl/getcontent',array($geturl));
        if(in_array($extension,array('jpg','gif','jpeg','png','css','js'))){
            $html = $content;
        }else{
            $html = R('Replace/act',array($content,$geturl));

        }
		return $html;
	}



	/** html展示方式
	 * 判断图片，是否下载
	 * 判断子目录首页
	**/
	public function html($weburl,$parameter=''){
        $config = $this->_config;
        if($config['url_rewrite_on'] == 1) $parameter = get_url_before_rewrite($parameter,$config['url_rewrite_rules']);
        if($config['forcedurl'] == 1) $parameter = url_convert($parameter,1);
		$geturl = $weburl.$parameter;
        $geturl = preg_replace('/([^:])\/\//is','${1}/',$geturl);//处理url中的"//"
		//默认文件存放目录
		$htmlpath = './Runtime/Html';
		$csspath = $htmlpath.'/css';
		$jspath = $htmlpath.'/js';
		$imgpath = $htmlpath.'/img';
		$datapath = $htmlpath.'/data';

//		$urlpath = parse_url($geturl,PHP_URL_PATH);//文件路径
		$urlpath = $_SERVER['REQUEST_URI'];		//当前网站URI
		/**获取当前访问文件扩展名**/
		$extension = parse_url($geturl,PHP_URL_PATH);	//url解析
		$extension = pathinfo($extension,PATHINFO_EXTENSION);//返回文件路径相关信息

		//缓存配置
		$hconfig = $this->getHconfig();

		/*
		 *判断当前url
		 * 首页为 /
		 */
		if($urlpath == '/'){
			//判断是否开启缓存
			if($hconfig['HTML_CACHE'] == 1){
				$filename = $htmlpath.'/index.html';
				$html = $this->cacheIndex($filename,$extension,$geturl,$weburl);
			}else{
				$html = $this->nocache($geturl);
				$html = $this->replaceIndex($html);
			}
		}else{
			//分类页首页
			if($extension == ''){
				$extension = 'html';
				$filename = $datapath.$urlpath.'/index.html';
				//使用缓存
				if($hconfig['HTML_CACHE'] == 1){
					$html = $this->useCache($filename,$extension,$geturl,$weburl);
				}else{
					$html = $this->nocache($geturl);
				}
                                if($code = self::getHtmlCode($html))
                                        header("Content-Type:text/html;charset=$code");
                                $html = \Home\Library\HtmlDomReplace::AppendNav($html);
                                if(strtolower(trim($code))!='utf-8' && $code){
                                    $html = mb_convert_encoding($html, $code,'utf-8');
                                }
			}elseif($extension == 'css'){
				$filename = $csspath.parse_url($urlpath,PHP_URL_PATH);
				//使用CSS缓存
				if($hconfig['CSS_CACHE'] == 1){
					$html = $this->useCache($filename,$extension,$geturl);
				}else{
					$html = $this->nocache($geturl);
				}
			}elseif($extension == 'js'){
				$filename = $jspath.parse_url($urlpath,PHP_URL_PATH);
				//使用JS缓存
				if($hconfig['JS_CACHE'] == 1){
					$html = $this->useCache($filename,$extension,$geturl);
				}else{
					$html = $this->nocache($geturl);
				}				
			}elseif(in_array($extension,array('jpg','jpeg','gif','png'))){
				$filename = $imgpath.parse_url($urlpath,PHP_URL_PATH);
				//使用图片缓存
				if($hconfig['IMG_CACHE'] == 1){
					$html = $this->useCache($filename,$extension,$geturl);
				}else{
                    header('Content-Type:  image/jpeg');
					$html = $this->nocache($geturl);
				}
			}elseif(in_array($extension,array('htm','html','shtm','shtml','txt'))){
				$filename = $datapath.parse_url($urlpath,PHP_URL_PATH);
				//使用html缓存
				if($hconfig['HTML_CACHE'] == 1){
					$html = $this->useCache($filename,$extension,$geturl,$weburl);
				}else{
					$html = $this->nocache($geturl);
				}
                                if($code = self::getHtmlCode($html))
                                        header("Content-Type:text/html;charset=$code");
                                $html = \Home\Library\HtmlDomReplace::AppendNav($html);
                                if(strtolower(trim($code))!='utf-8' && $code){
                                    $html = mb_convert_encoding($html, $code,'utf-8');
                                }
                                
			}else{
				//$filename = $datapath.parse_url($urlpath,PHP_URL_PATH);
				$html = $this->nocache($geturl);
                                if($code = self::getHtmlCode($html))
                                        header("Content-Type:text/html;charset=$code");
                                $html = \Home\Library\HtmlDomReplace::AppendNav($html);
                                if(strtolower(trim($code))!='utf-8' && $code){
                                    $html = mb_convert_encoding($html, $code,'utf-8');
                                }
			}
			return $html;
		}
		return $html;
	}	
	/**首页缓存**/
	public function cacheIndex($filename,$extension,$geturl,$weburl){
		if(!file_exists($filename)){
			$content = R('Curl/getcontent',array($geturl));
			$html = R('Replace/act',array($content,$weburl));
			$html = $this->replaceIndex($html);
			file_put_contents($filename,$html);
		}else{
			//读取本地文件
			$html = file_get_contents($filename);
		}
		return $html;
	}
	/* 使用缓存
	* filename 文件路径
	* extension 文件扩展名
	* geturl 请求路径
	* weburl 当前网址
	*/
	public function useCache($filename,$extension,$geturl,$weburl=''){
		if(!file_exists($filename)){
			/*判断路径，是否生成文件夹*/
			$content = R('Curl/getcontent',array($geturl));
			/* 如果文件为html\txt，执行替换*/
			if(in_array($extension,array('html','html','shtm','shtml','txt'))){
				$html = R('Replace/act',array($content,$weburl));
			}else{
				$html = $content;
			}
			/**下载或生成文件**/
			$dir = pathinfo($filename,PATHINFO_DIRNAME);
			if(!is_dir($dir)){
				mkdir($dir,0755,true);		
			}
			file_put_contents($filename,$html);
		}else{
			//读取本地文件
			$html = file_get_contents($filename);
			if($extension == 'css'){
				header('Content-Type: text/css');		
			}elseif($extension == 'js'){
				header('Content-Type: application/x-javascript');
			}elseif (in_array($extension,array('jpg','jpeg','gif','png'))){
                header('Content-Type: image/jpeg');
            }else{
                header('Content-Type: text/html');
            }
		}
		return $html;
	}

	/**不使用缓存**/
	public function nocache($geturl){
		$content = R('Curl/getcontent',array($geturl));
		$html = R('Replace/act',array($content,$geturl));
		return $html;
	}
	/** 替换首页TDK **/
	public function replaceIndex($html){
		$pattern = array(
			'/<TITLE>([\w\W]*?)<\/TITLE>/is',
			'/<META\s+name=["|\']keywords["|\']\s+content=["|\']([\w\W]*?)["|\'][^>]*>/is',
			'/<meta\s+content="[^"]+"\s+name="keywords"\s*?\/?>/is',
			"/<meta\s+content='[^']+'\s+name='keywords'\s*?\/?>/is",
			'/<meta http-equiv="keywords" content="[^"]+"\/>/is',
			'/<meta\s+name=[\'|"]description[\'|"]\s+content=[\'|"][\w\W]+?[\'|"][^>]*>/is',
			'/<meta\s+content="[^"]+"\s+name="description"\s*?\/?>/is',
			"/<meta\s+content='[^']+'\s+name='description'\s*?\/?>/is",
		);
		$conf = R('Loadconfig/webconfig');
		/**判断目标站编码，非utf-8则需要转换**/
		$cjconf = R('Loadconfig/config');
		$charset_id =$cjconf['webcharset'];
		$codesarr = C('WEBCHARSETS');
		$webcharset = $codesarr[$charset_id];
		/*preg_match('/<meta[^>]+charset="?([^>]*?)["|\']([^>]*)?\/?>/',$html,$match);//判断meta标签里的编码*/
                preg_match('/<meta[^>]+charset=["|\']?([^"^\'^>]*)["|\']?([^>]+)?\/?>/',$html,$match);//判断meta标签里的编码
		if($charset_id >0){
			$index_title = iconv("utf-8",$webcharset."//TRANSLIT",$conf['WEB_INDEX_TITLE']);
			$index_keywords = iconv("utf-8",$webcharset."//TRANSLIT",$conf['INDEX_KEYWORDS']);
			$index_description = iconv("utf-8",$webcharset."//TRANSLIT",$conf['INDEX_DESCRIPTION']);		
		}else{
			/**处理meta标签编码与实际编码不符的情况**/
                        if(strtolower($match[1]) != 'utf-8'){
				/*$html = preg_replace('/<meta[^>]+charset="?([^>]*?)["|\']([^>]*)?\/?>/','<meta charset="utf-8">',$html);*/
                                $html = preg_replace('/<meta[^>]+charset=["|\']?([^"^\'^>]*)["|\']?([^>]+)?\/?>/','<meta charset="utf-8">',$html);
			}
			$index_title = $conf['WEB_INDEX_TITLE'];
			$index_keywords = $conf['INDEX_KEYWORDS'];
			$index_description = $conf['INDEX_DESCRIPTION'];		
		}

		$replace = array(
			'<title>'.$index_title.'</title>',
			'<meta name="keywords" content="'.$index_keywords.'" />',
			'<meta name="keywords" content="'.$index_keywords.'" />',
			'<meta name="keywords" content="'.$index_keywords.'" />',
			'<meta name="keywords" content="'.$index_keywords.'" />',
			'<meta name="description" content="'.$index_description.'" />',
			'<meta name="description" content="'.$index_description.'" />',
			'<meta name="description" content="'.$index_description.'" />',
		);
		$html = preg_replace($pattern,$replace,$html);
		//添加JS
		$html = preg_replace('/<body[^>]*?>/isU','$0'.PHP_EOL.'<script src="/base.js"></script>',$html);
		$html = str_replace(array('</body>','</BODY>'),'<div style="display:none"><script charset="utf-8" src="/js.js"></script></div>'.PHP_EOL.'</body>',$html);
		return $html;
	}
}