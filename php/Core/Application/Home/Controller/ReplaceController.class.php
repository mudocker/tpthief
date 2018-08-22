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
 * 前台替换
 * 主要获取首页聚合数据
 */
class ReplaceController extends HomeController {

    public function _initialize(){
        $this->mydomain = getmydomain();
        $this->_config = R('Loadconfig/config');
    }

	/* 替换
	 * @html 要替换的内容
	 * @geturl 目标网站域名
	*/
	public function act($html,$geturl){
		/*当前域名*/
		$mydomain = $this->mydomain;
		$parseurl = parse_url($geturl);
		$weburl = $parseurl['scheme'].'://'.$parseurl['host'].'/';//目标站域名
        $weburl2 = '//'.$parseurl['host'].'/';//目标站域名2,不规范的情况
		/** 替换域名 */
		$res = str_replace(array($weburl,$weburl2),$mydomain,$html);
		$res = $this->replaceSubdomain($res);
        //强制域名处理
        $res = $this->urlConvert($res);
        //url重写,替换url
        $res = $this->urlRewrite($res);
		//过滤标签
		$res = $this->filterTags($res);
		//站内外过滤
		$res = $this->filterOthertags($res);
        //同义词替换
        $res = $this->similarReplace($res);
		//字符串替换
		$res = $this->stringReplace($res);
		//正则替换
		$res = $this->regexReplace($res);
		//关键词内链
//		$res = $this->linkwords($res);
		return $res;
	}

	/**二级域名处理 **/
	public function replaceSubdomain($html){
		$config = $this->_config;
		//$mydomain = $_SERVER['REQUEST_SCHEME'].'://'.$_SERVER['HTTP_HOST'];
        $mydomain = $this->mydomain;
		if(!$config['subdomain']) return $html;
		$arr = explode('##########',$config['subdomain']);
		$arr = array_filter($arr);
		foreach($arr as $list){
			$strs = explode('******',$list);
			$find[] = trim($strs[0]);
			$replace[] = $mydomain.'/'.trim($strs[1]);
		}
		$res = str_replace($find,$replace,$html);
		return $res;
	}
    /**
     * 强制处理冲突hRL
     */
    public function urlConvert($html){
        $config = $this->_config;
        if($config['forcedurl'] == 1){
            preg_match_all('/href\s*=\s*("|\')(.*?)\1/is',$html,$match);
            $links = $match[0];
            $res = array_map(function($url){
                $find = array(
                    '.php?',
                    '&',
                );
                $replace = array(
                    '/php/',
                    '/and/',
                );
                $result = str_replace($find,$replace,$url);
                return $result;
            },$links);
            $find  = array_diff($links,$res);
            $replace  = array_diff($res,$links);
            $res = str_replace($find,$replace,$html);
            return $res;
        }else{
            return $html;
        }
    }
    /*
     * url重写
     */
    public function urlRewrite($html){
        $config = $this->_config;
        if($config['url_rewrite_on'] == 1){
            if(empty($config['url_rewrite_rules'])) return $html;
            //获取网页所有链接
            preg_match_all('/href\s*=\s*("|\')(.*?)\1/is',$html,$match);
            $links = $match[0];
            //获取重写规则
            $rules = explode(PHP_EOL,$config['url_rewrite_rules']);
            foreach($rules as $vo){
                $arr = explode('=>',$vo);
                $find[] = trim($arr[0]);
                $replace[] = trim($arr[1]);
            }
            //判断url是否需要重写，并返回重写后的url
            $res = array_map(function($links) use ($find,$replace){
                foreach($find as $k=>$list){
                    preg_match('~'.$list.'~is',$links,$match);
                    if($match[0]){
                        $res = str_replace($list,$replace[$k],$links);
                        return $res;
                        break;
                    }
                }
                return $links;
            },$links);
            $find  = array_diff($links,$res);
            $replace  = array_diff($res,$links);
            $res = str_replace($find,$replace,$html);
            return $res;
        }else{
            return $html;
        }
    }
	/** 标签过滤 **/
	public function filterTags($html){
		$config = $this->_config;
		if($config['siftags'] == 0) return $html;
		$arr = explode(',',$config['siftags']);	//选中的标签
		if($arr){
			$tags = parse_field_attr('[SIFTTAGS]');//所有标签
			foreach($arr as $i){
				if(array_key_exists($i,$tags)){
					$html = $this->filterHtml($html,$tags[$i]);
				}
			}
		
		}
		return $html;
	}
	/*
	 * 过滤标签
	 * @html 源码,tag 标签
	*/
	public function filterHtml($html,$tag){
		switch($tag){
			case "iframe":
				$regx = "/<iframe([^>]+>)(.+)?<\/iframe>/isU";
				break;
			case "frame":
				$regx = "/<frameset([^>]+>)[\s\S]+?<\/frameset>/is";
				break;
			case "object":
				$regx = "/<object([^>]+>)[\s\S]+?<\/object>/is";
				break;
			case "script":
				$regx = "/<script([\s]+)?([^>]+)?>[\s\S]*?<\/script>/is";
				break;
			case "form":
				$regx = "/<form[^>]+>[\s\S]*?<\/form>/is";
				break;
			case "textarea":
				$regx = "/<textarea([^>]+>)[\s\S]*?<\/textarea>/is";
				break;
			case "input":
				$regx = "/<input[^>]*\/>/is";
				break;
			case "select":
				$regx = "/<select([^>]+)?>[\s\S]*?<\/select>/is";
				break;
			case "hr":
				$regx = "/<hr[^>]*\/?>/is";
				break;
			case "embed";
				$regx = "/<embed([^>]+)?>[\s\S]*?<\/embed>|<embed[^>]+\/>/is";
				break;
			case "img";
				$regx = "/<img[^>]*\/?>/is";
				break;
			default:
				$regx = "0";
		}
		if($regx){
			$html = preg_replace($regx,'',$html);
			/* 另一种替换方法
			preg_match_all($regx, $html, $match);
			if($match){
				foreach($match[0] as $k=>$list){
					$html = str_replace($list,'',$html);
				}
			}*/
		}
		return $html;		
	}

	//站内外过滤
	public function filterOthertags($html){
		$config = $this->_config;
		if(!$config['other_tags']) return $html;
		$arr = explode(',',$config['other_tags']);
		foreach($arr as $list){
			switch ($list){
				case 'outjs':
					$html = $this->filterOutjs($html);
					break;
				case 'outcss':
					$html = $this->filterOutcss($html);
					break;
				case 'outlink':
					$html = $this->filterOutlink($html);
					break;
				default:
					$html = $html;
			}

		}
		return $html;
	}

	//过滤站外js
	public function filterOutjs($html){
		$mydomain = $_SERVER['HTTP_HOST'];	//当前网站域名
		preg_match_all('/<script[^>]+src[^>]+><\/script>/is',$html,$match);
		foreach($match[0] as $k=>$list){
			/**判断是否为站外JS**/
			if(preg_match('/src=[\'|\"]([^\"|^\']+)[\'|\"]/is',$list,$jsmatch)){
				$jshost = parse_url($jsmatch[1],PHP_URL_HOST);
				if(!empty($jshost) && $jshost !=$mydomain)
				$find[] = $list;
			}			
		}
		if(!empty($find)) $html = str_replace($find,'',$html);
		return $html;
	}
	//过滤站外css
	/** 以//开头的过滤不了 **/
	public function filterOutcss($html){
		$mydomain = $_SERVER['HTTP_HOST'];	//当前网站域名
		preg_match_all('/<link[^>]+>/is',$html,$match);
		foreach($match[0] as $k=>$list){
			/**判断是否为站外Css**/
			if(preg_match('/href=[\'|\"]([^\"|^\']+)[\'|\"]/is',$list,$cssmatch)){
				$csshost = parse_url($cssmatch[1],PHP_URL_HOST);
				if(!empty($csshost) && $csshost !=$mydomain)
				$find[] = $list;
			}			
		}
		if(!empty($find)) $html = str_replace($find,'',$html);
		return $html;
	}
	//过滤站外链接
	public function filterOutlink($html){
		$mydomain = $_SERVER['HTTP_HOST'];	//当前网站域名
		preg_match_all('/<a[^>]+href[^>]+>[\s\S]*?<\/a>/is',$html,$match);
		foreach($match[0] as $k=>$list){
			/**判断是否为站外Css**/
			if(preg_match('/href=[\'|\"]([^\"|^\']+)[\'|\"]/is',$list,$cssmatch)){
				$csshost = parse_url($cssmatch[1],PHP_URL_HOST);
				if(!empty($csshost) && $csshost !=$mydomain)
				$find[] = $list;
			}			
		}
		if(!empty($find)) $html = str_replace($find,'',$html);
		return $html;
	}

	//字符串替换
	public function stringReplace($html){
		$config = $this->_config;
		if(!$config['str_rules']) return $html;
		$str_rules = $config['str_rules'];
		/**判断目标站编码，非utf-8则需要转换**/
		$charset_id =$config['webcharset'];
		if($charset_id >0){
			$codesarr = C('WEBCHARSETS');
			$webcharset = $codesarr[$charset_id];
			//$str_rules = iconv("utf-8",$webcharset."//TRANSLIT",$str_rules);
		}
		$arr = explode('##########',$str_rules);
		$arr = array_filter($arr);
		if($webcharset){
            foreach($arr as $list){
                $strs = explode('******',$list);
                $find[] = iconv("utf-8",$webcharset."//TRANSLIT",trim(base64_decode($strs[0])));
                $replace[] = iconv("utf-8",$webcharset."//TRANSLIT",trim(base64_decode($strs[1])));
            }
        }else{
            foreach($arr as $list){
                $strs = explode('******',$list);
                $find[] = trim(base64_decode($strs[0]));
                $replace[] = trim(base64_decode($strs[1]));
            }
        }
		$html = str_replace($find,$replace,$html);
		return $html;
	}

	//正则替换
	public function regexReplace($html){
		$config = $this->_config;
		if(!$config['reg_rules']) return $html;
		$arr = explode('##########',$config['reg_rules']);
		$arr = array_filter($arr);
		foreach($arr as $list){
			$strs = explode('******',$list);
			$find = trim($strs[0]);
			$find = addcslashes($find,'/');// 在/前加反斜杠
			$find = '/'.$find.'/is';
			$pattern[] = $find;
			$replace[] = trim($strs[1]);
		}
		$html = preg_replace($pattern,$replace,$html);
		return $html;
	}

	/**同义词替换 **/
	public function similarReplace($html){
		$conf = R('Loadconfig/webconfig');
		$status = $conf['REPLACE_WORD_ON'];
		if($status == false) return $html;
		$info = $conf['REPLACE_WORD'];
		$arr = explode(PHP_EOL,$info);
		foreach($arr as $list){
			$strs = explode(':',$list);
			$find[] = trim($strs[0]);
			$replace[] = trim($strs[1]);
		}
		$html = str_replace($find,$replace,$html);
		return $html;
	}

	/** 关键词内链 **/
	public function linkwords($html){
		$conf = R('Loadconfig/webconfig');
		$status = $conf['LINKWORDS_ON'];
		if($status == false) return;
		$info = $conf['LINKWORDS'];
		return $html;
	
	}
	//过滤iframe 
	public function filterIframe($html){
		$regx = "/<iframe([^>]+>)(.+)?<\/iframe>/isU";
		preg_match_all($regx, $html, $match);
		if($match){
			foreach($match[0] as $k=>$list){
				$html = str_replace($list,'',$html);
			}
		}
		return $html;
	}

	//过滤object
	public function filterObject($html){
		return $html;
	}

	//过滤script 
	public function filterScript($html){
		$regx = "/<script([\s]+)?([^>]+)?>[\s\S]*?<\/script>/is";
		preg_match_all($regx, $html, $match);
		if($match){
			foreach($match[0] as $k=>$list){
				$html = str_replace($list,'',$html);
			}
		}
		return $html;
	}

	//匹配js
	public function getalljs($html) {
		$regx = "~(<script\s+[^>]+>)~iUs";
		preg_match_all($regx, $html, $match);
		$jsArr=array();
		if($match){
			foreach($match[1] as $k=>$vo){
				if(preg_match('~src\s*=\s*(["|\']?)\s*([^"\'\s>\\\\]+)\s*\\1~i', $vo,$jsmatch)){
					$jsArr[]=$jsmatch[2];
				}
			}
			$jsArr=array_unique($jsArr);
		}
		sort($jsArr);
		return $jsArr;
	}
	//匹配css
	public function getallcss($html) {
		$regx = "~(<link[^>]+>)~iUs";
		preg_match_all($regx, $html, $match);
		$cssHrefArr=array();
		if($match){
			foreach($match[1] as $k=>$vo){
				if(!preg_match('~rel\s*=\s*(["|\']?)\s*stylesheet\s*\\1~i', $vo)){
					unset($match[1][$k]);
					continue;
				}
				if(preg_match('~href\s*=\s*(["|\']?)\s*([^"\'\s>\\\\]+)\s*\\1~i', $vo,$hrefmatch)){
					$cssHrefArr[]=$hrefmatch[2];
				}
			}
			$cssHrefArr=array_unique($cssHrefArr);
		}
		sort($cssHrefArr);
		return $cssHrefArr;
	}
	//强制过滤
    public function forcefilter($html){
	    $find= array(
	        'index.php?',
            '&'
        );
	    $replace = array(
	        'index/',
            'and'
        );
	    $res = str_replace($find,$replace,$html);
	    return $res;
    }




}