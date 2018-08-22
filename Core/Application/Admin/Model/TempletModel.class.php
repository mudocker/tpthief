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
class TempletModel extends Model{

    /* 自动验证规则 */
    protected $_validate = array(
        array('name', 'require', '目标站名称不能为空', self::MUST_VALIDATE, 'regex', self::MODEL_BOTH),
        array('name', '1,30', '标识长度不能超过30个字符', self::MUST_VALIDATE, 'length', self::MODEL_BOTH),
        array('name', '', '标识被占用', self::EXISTS_VALIDATE, 'unique'), //标识被占用
        array('title', 'require', '节点名称不能为空', self::MUST_VALIDATE, 'regex', self::MODEL_BOTH),
        array('title', '1,80', '标题长度不能超过80个字符', self::MUST_VALIDATE, 'length', self::MODEL_BOTH),
        array('content', 'require', '模板内容不能为空', self::MUST_VALIDATE, 'regex', self::MODEL_BOTH),
	);

    /* 自动完成规则 */
    protected $_auto = array(
        array('title', 'htmlspecialchars', self::MODEL_BOTH, 'function'),
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
			$data['id'] = $id;
        } else { //更新数据
            $status = $this->save(); //更新基础内容
            if(false === $status){
                $this->error = '更新基础内容出错！';
                return false;
            }
        }


        //行为记录
        if($id){
            action_log('add_tem', 'templet', $id, UID);
        }

        //内容添加或更新完成
        return $data;
    }


}