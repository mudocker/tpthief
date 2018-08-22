<?php
// +----------------------------------------------------------------------
// | OneThink [ WE CAN DO IT JUST THINK IT ]
// +----------------------------------------------------------------------
// | Copyright (c) 2013 http://www.onethink.cn All rights reserved.
// +----------------------------------------------------------------------
// | Author: 麦当苗儿 <zuojiazi@vip.qq.com> <http://www.zjzit.cn>
// +----------------------------------------------------------------------

/**
 * 前台公共库文件
 * 主要定义前台公共函数库
 */

/**
 * 检测验证码
 * @param  integer $id 验证码ID
 * @return boolean     检测结果
 * @author 麦当苗儿 <zuojiazi@vip.qq.com>
 */
function check_verify($code, $id = 1){
	$verify = new \Think\Verify();
	return $verify->check($code, $id);
}

/**
 * 获取列表总行数
 * @param  string  $category 分类ID
 * @param  integer $status   数据状态
 * @author 麦当苗儿 <zuojiazi@vip.qq.com>
 */
function get_list_count($category, $status = 1){
    static $count;
    if(!isset($count[$category])){
        $count[$category] = D('Document')->listCount($category, $status);
    }
    return $count[$category];
}

/**
 * 获取段落总数
 * @param  string $id 文档ID
 * @return integer    段落总数
 * @author 麦当苗儿 <zuojiazi@vip.qq.com>
 */
function get_part_count($id){
    static $count;
    if(!isset($count[$id])){
        $count[$id] = D('Document')->partCount($id);
    }
    return $count[$id];
}

/**
 * 获取导航URL
 * @param  string $url 导航URL
 * @return string      解析或的url
 * @author 麦当苗儿 <zuojiazi@vip.qq.com>
 */
function get_nav_url($url){
    switch ($url) {
        case 'http://' === substr($url, 0, 7):
        case '#' === substr($url, 0, 1):
            break;        
        default:
            $url = U($url);
            break;
    }
    return $url;
}

 // 分析枚举类型字段值 格式 a:名称1,b:名称2
 // 暂时和 parse_config_attr功能相同
 // 但请不要互相使用，后期会调整
function parse_field_attr($string) {
    if(0 === strpos($string,':')){
        // 采用函数定义
        return   eval('return '.substr($string,1).';');
    }elseif(0 === strpos($string,'[')){
        // 支持读取配置参数（必须是数组类型）
        return C(substr($string,1,-1));
    }
    
    $array = preg_split('/[,;\r\n]+/', trim($string, ",;\r\n"));
    if(strpos($string,':')){
        $value  =   array();
        foreach ($array as $val) {
            list($k, $v) = explode(':', $val);
            $value[$k]   = $v;
        }
    }else{
        $value  =   $array;
    }
    return $value;
}
/*
 * 根据url获取请求文件的扩展名
 */
function get_extension($url){
    $extension = parse_url($url,PHP_URL_PATH);	//url解析
    $extension = pathinfo($extension,PATHINFO_EXTENSION);
    return strtolower($extension);
}
/*
 * 根据网站ul，获取成对应的文件路径
 */
function get_filepath($url){
    $urlpath = parse_url($url,PHP_URL_PATH);
    $extension = get_extension($url);//扩展名
    $htmlpath = './Runtime/Html';   //缓存位置
    $filename = $urlpath;   //文件位置
    switch ($extension){
        case '':
            if($urlpath == '/'){
                $filename = $htmlpath.$urlpath.'/index.html';   //网站首页
            }else{
                $filename = $htmlpath.'/data'.$urlpath.'/index.html';//列表首页
            }
            break;
        case 'css':
            $filename = $htmlpath.'/css'.$filename;
            break;
        case 'js':
            $filename = $htmlpath.'/js'.$filename;
            break;
        case in_array($extension,array('jpg','gif','jpeg','png')):
            $filename = $htmlpath.'/img'.$filename;
            break;
        case in_array($extension,array('html','html','shtml','jhtml')):
            $filename = $htmlpath.'/data'.$filename;
            break;
        default:
            $filename = $htmlpath.'/data'.$filename;;
    }
    $filename = str_replace('//','/',$filename);
    return $filename;
}
/*
 * 根据文件扩展名设置Content-type
 * @extension str 扩展名
 */
function get_ContentType($extension){
    switch($extension){
        case '':
            $content_type = 'text/html';
            break;
        case 'css':
            $content_type = 'text/css';
            break;
        case 'js':
            $content_type = 'application/x-javascript';
            break;
        case in_array($extension,array('jpg','gif','jpeg','png')):
            $content_type = 'image/'.$extension;
            break;
        default:
            $content_type = 'text/html';
    }
    return $content_type;
}
/*
 * 生成随机文件名
 * ***/
function get_randname($v,$extension){
    $v  = base64_encode($v);
    $key = sha1('f7*d)1V&_d22');
    $data = md5($key.$v);
    return $data.'.'.$extension;
}

/**处理目标站有多个二级域名的情况**/
function check_subdomain($url,$config){
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
/**获取当前网站域名**/
function getmydomain(){
    $config = R('Loadconfig/webconfig');
    return $config['WEB_URL'];
}
/**
 * 强制转换url
 **/
function url_convert($url,$reverse=0) {
    $find = array(
        '.php?',
        '&',
    );
    $replace = array(
      '/php/',
      '/and/',
    );
    if($reverse ==1){
        $res = str_replace($replace,$find,$url);
    }else{
        $res = str_replace($find,$replace,$url);
    }
    return $res;
}
/**获取url重写前的url**/
function get_url_before_rewrite($url,$rules){
    if(!$rules) return $url;
    $rules = explode(PHP_EOL,$rules);
    foreach($rules as $vo){
        $arr = explode('=>',$vo);
        $find[] = trim($arr[0]);
        $replace[] = trim($arr[1]);
    }
    foreach($replace as $vo){
        preg_match('~'.$vo.'~is',$url,$match);
        if($match){
            $k = array_search($vo,$replace);
            break;
        }
    }
    if(isset($k)){
        return $find[$k];
    }else{
        return $url;
    }
}