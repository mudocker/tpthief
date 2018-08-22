<?php
// +----------------------------------------------------------------------
// | OneThink [ WE CAN DO IT JUST THINK IT ]
// +----------------------------------------------------------------------
// | Copyright (c) 2013 http://www.onethink.cn All rights reserved.
// +----------------------------------------------------------------------
// | Author: 麦当苗儿 <zuojiazi@vip.qq.com> <http://www.zjzit.cn>
// +----------------------------------------------------------------------
namespace Admin\Controller;
use Admin\Model\AuthGroupModel;
use Think\Page;
/**
 * 其他文章
 * @author 麦当苗儿 <zuojiazi@vip.qq.com>
 */
class TemplateController extends AdminController {
 

	/**模板管理**/
	public function index(){
		$model = M('Model')->getByName('templet');
		$model_id   =   null;
		$cate_id    =   0;
		$this->assign('model', null);
        //解析列表规则
        $fields =	array();
        $grids  =	preg_split('/[;\r\n]+/s', trim($model['list_grid']));
        foreach ($grids as &$value) {
            // 字段:标题:链接
            $val      = explode(':', $value);
            // 支持多个字段显示
            $field   = explode(',', $val[0]);
            $value    = array('field' => $field, 'title' => $val[1]);
            if(isset($val[2])){
                // 链接信息
                $value['href']  =   $val[2];
                // 搜索链接信息中的字段信息
                preg_replace_callback('/\[([a-z_]+)\]/', function($match) use(&$fields){$fields[]=$match[1];}, $value['href']);
            }
            if(strpos($val[1],'|')){
                // 显示格式定义
                list($value['title'],$value['format'])    =   explode('|',$val[1]);
            }
            foreach($field as $val){
                $array  =   explode('|',$val);
                $fields[] = $array[0];
            }
        }
		$list = M('Templet')->where('status>=0')->select();
        $list = $this->parseDocumentList($list,4);

        $this->assign('list',   $list);
		$this->assign('list_grids', $grids);
        // 记录当前列表页的cookie
        Cookie('__forward__',$_SERVER['REQUEST_URI']);
        $this->display();	
	}

	/* 新增模板 */
	public function add(){
        // 获取当前的模型信息
        $model    =   M('Model')->getByName('templet');
        //获取表单字段排序
        $fields = get_model_attribute($model['id']);
        $this->assign('fields',     $fields);
        $this->assign('model',      $model);
        $this->meta_title = '新增模板';
        $this->display();	
	}

    /**
     * 节点编辑
     * @author huajie <banhuajie@163.com>
     */
    public function edit(){

        $id     =   I('get.id','');
        if(empty($id)){
            $this->error('参数不能为空！');
        }

        // 获取详细数据 
        $Document = D('Templet');
        $data = $Document->where("id=$id")->find();
        if(!$data){
            $this->error($Document->getError());
        }

		$model_id = $this->model_id;
        // 获取当前的模型信息
        $model    =   M('Model')->getByName('templet');
        $this->assign('data', $data);
        $this->assign('model_id', $model_id);
        $this->assign('model',      $model);

        //获取表单字段排序
        $fields = get_model_attribute($model['id']);
        $this->assign('fields',     $fields);

        $this->meta_title   =   '编辑节点';
        $this->display();
    }

    /**
     * 更新节点
     * @author huajie <banhuajie@163.com>
     */
    public function update(){
        $document   =   D('Templet');
        $res = $document->update();
        if(!$res){
            $this->error($document->getError());
        }else{
			if($res['id']) $this->createTem($res['id']);//生成模板文件
			$this->success($res['id']?'更新成功':'新增成功', Cookie('__forward__'));
        }
    }

    /**
     * 设置一条或者多条数据的状态
     * @author huajie <banhuajie@163.com>
     */
    public function setStatus($model='Templet'){
        return parent::setStatus('Templet');
    }

	/**生成网站配置文件**/
	public function createTem($id){
        $dir = './template/Home/classic/';
        if(!is_dir($dir)) mkdir($dir,0755,true);  
		$info = M('Templet')->where("status=1 and id=$id")->field('name,content')->find();
        $file = $dir.'/'.$info['name'].'.html';
		$res = $info['content'];
		file_put_contents($file,$res);	
	}

}