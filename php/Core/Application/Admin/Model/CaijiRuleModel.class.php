<?php
// +----------------------------------------------------------------------
// | OneThink [ WE CAN DO IT JUST THINK IT ]
// +----------------------------------------------------------------------
// | Copyright (c) 2013 http://www.onethink.cn All rights reserved.
// +----------------------------------------------------------------------
// | Author: huajie <banhuajie@163.com>
// +----------------------------------------------------------------------

namespace Admin\Model;
use Think\Model;

/**
 * 文档基础模型
 */
class CaijiRuleModel extends Model{

    /* 自动验证规则 */
    protected $_validate = array(
        array('weburl', 'require', '目标站地址不能为空', self::MUST_VALIDATE, 'regex', self::MODEL_BOTH),
        array('weburl', '/^http(s?):\/\/([\w-]+\.)+[\w-]+\/$/', '目标站地址不合法', self::VALUE_VALIDATE, 'regex', self::MODEL_BOTH),
        array('proxy_ip', '/^https?:\/\/(25[0-5]|2[0-4]\d|[0-1]\d{2}|[1-9]?\d)\.(25[0-5]|2[0-4]\d|[0-1]\d{2}|[1-9]?\d)\.(25[0-5]|2[0-4]\d|[0-1]\d{2}|[1-9]?\d)\.(25[0-5]|2[0-4]\d|[0-1]\d{2}|[1-9]?\d)(:\d+)?(@[\w\d]+\:[\w\d]+)?$/', '代理IP地址不合法', self::VALUE_VALIDATE, 'regex', self::MODEL_BOTH),
        array('title', 'require', '节点名称不能为空', self::MUST_VALIDATE, 'regex', self::MODEL_BOTH),
        array('title', '1,80', '标题长度不能超过80个字符', self::MUST_VALIDATE, 'length', self::MODEL_BOTH),
        array('webname', 'require', '目标站名称不能为空', self::MUST_VALIDATE, 'regex', self::MODEL_BOTH),
	);

    /* 自动完成规则 */
    protected $_auto = array(
        array('title', 'htmlspecialchars', self::MODEL_BOTH, 'function'),
        array('siftags', 'getSiftags', self::MODEL_BOTH, 'callback'),
        array('other_tags', 'getOthertags', self::MODEL_BOTH, 'callback'),
        array('str_rules', 'getStrRules', self::MODEL_BOTH, 'callback'),
        array('create_time', NOW_TIME, self::MODEL_INSERT),
        array('update_time', NOW_TIME, self::MODEL_BOTH),
    );


    /**
     * 新增或更新一个文档
     * @param array  $data 手动传入的数据
     * @return boolean fasle 失败 ， int  成功 返回完整的数据
     * @author huajie <banhuajie@163.com>
     */
    public function update($data = null){

        /* 获取数据对象 */
        $data = $this->token(false)->create($data);
//        print_r($data);
//        print_r($this->xpathEncode($data));
//        exit;
        if(empty($data)){
            return false;
        }


        /* 添加或新增基础内容 */
        if(empty($data['id'])){ //新增数据
            $id = $this->add(); //添加基础内容
            if(!$id){
                $this->error = '新增基础内容出错！';
                return false;
            }
        } else { //更新数据
            $status = $this->save(); //更新基础内容
            if(false === $status){
                $this->error = '更新基础内容出错！';
                return false;
            }
        }


        //行为记录
        if($id){
            action_log('add_document', 'document', $id, UID);
        }

        //内容添加或更新完成
        return $data;
    }
    
//    public function xpathEncode(&$data){
//        $xpath = [];
//        while(list($key,$val) = each($data)){
//            if(substr($key, 0, 6) == 'xpath_'){
//                $xpath[$key] = $val;
//                unset($data[$key]);
//            }
//        }
//        $data['xpath'] = json_encode($xpath);
//        return $data;
//    }

    /**
     * 生成标签过滤的值
     * @return number 推荐位
     * @author huajie <banhuajie@163.com>
     */
    protected function getSiftags(){
        $siftags = I('post.siftags');
        if(!is_array($siftags)){
            return 0;
        }else{
			$res = implode(',',$siftags);
            return $res;
        }
    }

    /**
     * 生成站内外过滤的值
     * @return number 推荐位
     * @author huajie <banhuajie@163.com>
     */
    protected function getOthertags(){
        $othertags = I('post.other_tags');
        if(!is_array($othertags)){
            return 0;
        }else{
			$res = implode(',',$othertags);
            return $res;
        }
    }
    protected function getStrRules(){
        $mbstr = I('post.mbstr');
        $mystr = I('post.mystr');
        foreach($mbstr as $k=>$mb){
            if(!empty($mb)){
                $replace[] = base64_encode(stripslashes($mb)).'******'.base64_encode(stripslashes($mystr[$k]));
                //$replace[] = html_entity_decode($mb,ENT_QUOTES,'utf-8').'******'.html_entity_decode($mystr[$k],ENT_QUOTES,'utf-8');
            }else{
                continue;
            }
        }
        if($replace){
            $rules = implode(PHP_EOL.'##########'.PHP_EOL,$replace);
            return $rules;
        }else{
            return '';
        }
    }
}