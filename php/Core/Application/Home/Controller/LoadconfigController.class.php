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
class LoadconfigController extends HomeController {


	/** config **/
	public function base(){
		@set_time_limit(120);
		@ini_set('pcre.backtrack_limit', 1000000);
		date_default_timezone_set('PRC');
	}
	/** 获取采集规则 **/
	public function config(){
		$dir = './Runtime/Config/';
		if(!is_dir($dir)) mkdir($dir,0755,true);		
		$file = $dir.'cjconfig.php';
		$res = include($file);
		if(!$res || !is_array($res)){
			$rule_id = M('CaijiConfig')->where('name="DEFAULT_RULE"')->getField('value');
			$info = M('CaijiRule')->where("id=$rule_id")->find();
			$str = '<?php'.PHP_EOL.'/*采集规则配置文件*/'.PHP_EOL.'return array('.PHP_EOL;
			foreach($info as $k=>$v){
				$v = addcslashes($v,"\\");//转义特殊符号，避免冲突
				$v = addcslashes($v,"'");//转义特殊符号，避免冲突
				$str .= " '".$k."' => '".trim($v)."',".PHP_EOL;
			}
			$str .= ');';
			file_put_contents($file,$str);
			$res = $info;
		}
		return $res;
	}
	
	/* 采集配置 */
	public function webconfig(){
		$dir = './Runtime/Config/';
		if(!is_dir($dir)) mkdir($dir,0755,true);		
		$file = $dir.'webconfig.php';
		$res = include($file);
		if(!$res || !is_array($res)){
			$info = M('CaijiConfig')->where('status=1')->field('name,value')->select();
			$str = '<?php'.PHP_EOL.'/*网站配置文件*/'.PHP_EOL.'return array('.PHP_EOL;
			foreach($info as $list){
				$v = trim($list['value']);
				$v = addcslashes($v,"\\");//转义特殊符号，避免冲突
				$v = addcslashes($v,"'");//转义特殊符号，避免冲突
				$str .= " '".trim($list['name'])."' => '".$v."',".PHP_EOL;
			}
			$str .= ');';
			file_put_contents($file,$str);
			$res = $info;
		}
		return $res;	
	}

}