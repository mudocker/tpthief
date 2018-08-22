<?php
// +----------------------------------------------------------------------
// | OneThink [ WE CAN DO IT JUST THINK IT ]
// +----------------------------------------------------------------------
// | Copyright (c) 2013 http://www.onethink.cn All rights reserved.
// +----------------------------------------------------------------------
// | Author: 麦当苗儿 <zuojiazi@vip.qq.com> <http://www.zjzit.cn>
// +----------------------------------------------------------------------

namespace Addons\Attachment;
use Common\Controller\Addon;

/**
 * 附件插件
 * @author 麦当苗儿 <zuojiazi@vip.qq.com>
 */
class AttachmentAddon extends Addon{

	public $info = array(
		'name'        => 'Attachment',
		'title'       => '附件',
		'description' => '用于文档模型上传附件',
		'status'      => 1,
		'author'      => 'thinkphp',
		'version'     => '0.1'
	);

	public $admin_list = array(
		'list_grid' => array(
			'id:ID',
			'title:文件名',
			'size:大小',
			'update_time_text:更新时间',
			'document_title:文档标题'
		),
		'model'=>'Attachment',
		'order'=>'id asc'
	);

	public $custom_adminlist = 'adminlist.html';

	public function install(){
		return true;
	}

	public function uninstall(){
		return true;
	}

	/* 显示文档模型编辑页插件扩展信息表单 */
	public function documentEditForm($param = array()){
		$this->assign($param);
		$this->display(T('Addons://Attachment@Article/edit'));
	}

	/* 文档末尾显示附件列表 */
	public function documentDetailAfter($info = array()){
		if(empty($info) || empty($info['id'])) return ;
		$Attachment = D('Addons://Attachment/Attachment');
		$map = array('record_id' => $info['id'], 'status' => 1);
		$list = $Attachment->field(true)->where($map)->select();
		if(!$list) return ;
		$this->assign('list', $list);
		$this->display(T('Addons://Attachment@Article/detail'));
	}

	/**
	 * 文档保存成功后执行行为
	 * @param  array  $data     文档数据
	 * @param  array  $catecory 分类数据
	 */
	public function documentSaveComplete($param){
		if (MODULE_NAME == 'Home') {
			list($data, $category) = $param;

			$default  = C('ATTACHMENT_DEFAULT');                                                                            	/* 附件默认配置项 */

			/* 合并当前配置 */
			$config = $category['extend']['attachment'];
			$config = empty($config) ? $default : array_merge($default, $config);
			$attach = I('post.attachment');

			/* 该分类不允许上传附件 */
			if(!$config['is_upload'] || !in_array($attach['type'], str2arr($config['allow_type']))) return ;


			switch ($attach['type']) {
				case 1: //外链
					# code...
					break;
				case 2: //文件
					$info = json_decode(think_decrypt($attach['info']), true);
					if(!empty($info)){
						$Attachment = D('Addons://Attachment/Attachment');
						$Attachment->saveFile($info['name'], $info, $data['id']);
					} else return;

					break;
			}
		}
	}
}
