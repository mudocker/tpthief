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
class MakehtmlController extends AdminController {
 
	
	public function cate(){
		$list = $this->showCateList();
		$this->assign('list',$list);
		$this->assign('meta_title','生成分类');
		$this->display();
	}
	public function showCateList($pid=0,$prev=''){
		$map['status'] = 1;
		$map['pid'] = $pid;
		$list = M('Category')->where($map)->field('id,pid,name,title')->select();
		if(!$list) return;
		foreach($list as $vo){
			$vo['prev'] .= $prev;
			$res[] = $vo;
			$child = $this->showCateList($vo['id'],$vo['prev'].'&nbsp;&nbsp;');
			if($child) $res = array_merge($res,$child);
		}
		return $res;
	}

	//生成文章页面
	public function article(){
		$list = $this->showCateList();
		$this->assign('list',$list);
		$this->assign('meta_title','生成文章');
		$this->display();	
	}
	/**生成分类下所有文章页面**/
	public function createArticle($category_id){
		 /*获取所有分类id*/
		$cateids = $this->getChilds($category_id);
		if(is_array($cateids)){
			$ids = implode(',',$cateids);
			$map['category_id'] = array('in',$ids);		
		}else{
			$map['category_id'] = $cateids;		
		}
		$map['status'] = 1;
		$list = M('Document')->where($map)->field('id')->select();
		if(!$list){
			echo '分类下没有文章！';
			return false;
		}
		foreach($list as $vo){
			$arr[] = $vo['id'];
		}
		$arr = implode(',',$arr);
		$this->createArcHtml($arr);
		return;
	}

	/**生成分类列表**/
	public function createCate($category_id,$p=1){
		 /*获取所有分类id*/
		$cateids = $this->getChilds($category_id);
		if(is_array($cateids)){
			$cateids = implode(',',$cateids);
			$this->createCatesHtml($cateids,0,$p);
			return;
		}else{
			$res = $this->createCateHtml($cateids,$p);
			return;
		}		
	}

	/* 文档分类检测 */
	private function category($id = 0){
		/* 标识正确性检测 */
		$id = $id ? $id : I('get.category', 0);
		if(empty($id)){
			$this->error('没有指定文档分类！');
		}
		/* 获取分类信息 */
		$category = D('Category')->info($id);
		if($category && 1 == $category['status']){
			switch ($category['display']) {
				case 0:
					$this->error('该分类禁止显示！');
					break;
				//TODO: 更多分类显示状态判断
				default:
					return $category;
			}
		} else {
			$this->error('分类不存在或被禁用！');
		}
	}
	
	/*
	 *输出分页*
	 * @map 查询条件 @p 分页
	 */
	public function showpage($map,$p=1){
		$M = M('Document'); // 实例化User对象
		$list_row = $map['list_row'];//列表行数
		import('ORG.Util.Page');// 导入分页类
		$count      = $M->where($map)->count();// 查询满足要求的总记录数
		$Page       = new Page($count,$list_row);// 实例化分页类 传入总记录数和每页显示的记录数
		$Page->baseUrl = './';
		$show       = $Page->show();// 分页显示输出
		return $show;
	}

	/*
	 * 获取所有分类ID
	*/
	public function getChilds($id){
		$map['pid'] = $id;
		$info = M('Category')->where($map)->field('id')->select();
		if(!$info){
			return $id;
		}else{
			$res = array($id);
			foreach($info as $list){
				$son = $this->getChilds($list['id']);
				if(is_array($son)){
					$res = array_merge($res,$son);
				}else{				
					array_push($res,$son);
				}
			}
		}
		return $res;
	}

	/*
	 * 根据分类ID生成分类列表
	 * category_id 分类ID
	*/
	public function createCateHtml($category_id,$p=1){		
		$category = $this->category($category_id);//当前分类信息
		$list_row = $category['list_row'];//列表每页行数
		/**赋值数据并渲染页面**/
		$this->showCate($category,$p);
		/**生成分类列表页**/
		$map['list_row'] = $category['list_row'];
		$map['category_id'] = $category['id'];
		$map['status'] = 1;
		/**跳转到下一页**/
		$count = M('Document')->where($map)->count();		// 查询满足要求的总记录数
		if($count<1) return;
		$pagetotal = ceil($count/$category['list_row']);	//总页数
		/** 总页数只有一页和第一页的情况 **/
		if($p < $pagetotal){
			$nextpage = $p+1;
			$this->redirect('createCate', array('category_id' => $category['id'],'p'=>$nextpage), 1, '，跳转到第'.$nextpage.'页');
		}else{
			echo '<br>总页数'.$p.'页，生成完成！';
		}
	}
	/*
	 * 根据分类ID生成分类列表(数组)
	 * category_id 分类ID
	 * @k int 键名 @p int 页数
	*/
	public function createCatesHtml($str_ids,$k=0,$p=1){
		$category_ids = str2arr($str_ids);
		$category = $this->category($category_ids[$k]);//当前分类信息
		$list_row = $category['list_row'];//列表每页行数
		/**赋值数据并渲染页面**/
		$this->showCate($category,$p);
		/**生成分类列表页**/
		$map['list_row'] = $category['list_row'];
		$cateids = $this->getChilds($category['id']);
		$map['category_id'] = array('in',$cateids);
		$map['status'] = 1;
		/**跳转到下一页**/
		$count = M('Document')->where($map)->count();		// 查询满足要求的总记录数
		if($count<1) return;
		$pagetotal = ceil($count/$category['list_row']);	//总页数
		
		if($p < $pagetotal){
			$nextpage = $p+1;
			$this->redirect('createCatesHtml', array('str_ids' => $str_ids,'k'=>$k,'p'=>$nextpage), 1, '跳转到第'.$nextpage.'页...');
		}else{
			echo '总页数'.$p.'页，生成完成！<br>';
			$arr_count = count($category_ids);
			$next_key = $k+1;
			if($next_key < $arr_count){
				$cid = $category_ids[$next_key];
				$next_title = M('Category')->where("id=$cid")->getField('title');
				$this->redirect('createCatesHtml', array('str_ids' => $str_ids,'k'=>$next_key,'p'=>1), 0.5, $next_title.'，跳转到第1页...');		
			}
			return;
		}
	}
	

	/** 
	 * 分类页模板赋值渲染,并生成html页面
	 * @id int 分类ID
	 */
	public function showCate($category,$p){

		$list_row = $category['list_row'];//列表每页行数
		/* 获取当前分类列表 */
		$Document = D('Document');
		//当前分类以及子分类
		$cate_ids = $this->getChilds($category['id']);
		if(is_array($cate_ids)){
			$cateids = implode(',',$cate_ids);	
		}else{
			$cateids = $cate_ids;		
		}
		$list = $Document->page($p, $category['list_row'])->lists($cateids);

		if(false === $list){
			$this->error('获取列表数据失败！');
			return;
		}
		/* 模板赋值并渲染模板 */
		$this->assign('category', $category);
		$this->assign('list', $list);

		//面包屑
		$breadcrumbs = $this->getBreadcrumbs($category['id']);
		$this->assign('breadcrumbs',$breadcrumbs);
		//当前分类列表
		$current_cate = $this->showCurrentCate($category['id']);
		$this->assign('currentcate',$current_cate);
		//文件夹路径
		$filepath = R('Article/getCatepath',array($category['id']));
		if(!is_dir($filepath)){
			mkdir($filepath,0777,true);		
		}
		$this->assign('arcpath',$filepath);//文章url根路径
		/** 分页赋值 **/
		$map['list_row'] = $category['list_row'];
		if($cate_ids) {
			$map['category_id'] = array('in',$cate_ids);
		}else{
			$map['category_id'] = $category['id'];	
		}
		$map['status'] = 1;
		$page = $this->showpage($map,$p);
		$page = str_replace(array('/admin.php?s=/','Makehtml/'),'',$page);
		$page = str_replace('./',$filepath.'/',$page);
		$page = preg_replace(array('/str_ids\/.+?\//is','/k\/\d+?\//','/category_id\/\d+?\//'), '', $page);
		$this->assign('page',$page);	
	
		/** TDK **/	
		$caiji_title = M('CaijiConfig')->where("name='WEB_SITE_TITLE'")->getField('value');
		$this->assign('title',$caiji_title);
		//导航
		$catelist = M('Category')->where("pid=0 and status=1")->select();
		$this->assign('catelist',$catelist);
		/**模板路径**/
		$template_lists = $category['template_lists'];
		if($template_lists){
			$tempath = $template_lists;		
		}else{
			$tempath = 'default/article/lists';		
		}
		if(!file_exists('./template/Home/classic/'.$tempath.'.html')) $this->error('模板不存在，没有成功生成列表');
		$this->buildHtml($p,HTML_PATH.$filepath.'/p/',C('HOME_TEMPLATE').$tempath);
		if($p == 1) $this->buildHtml('index',HTML_PATH.$filepath.'/',C('HOME_TEMPLATE').$tempath);		
		echo $category['title'].'：第'.$p.'页生成完成...';
	}

	

	/* 生成文章页面html文件
	 * @strids 所有文章ID,@k 数组指针
	**/
	public function createArcHtml($strids,$k=0){
		$ids = str2arr($strids);
		$aid = $ids[$k];
		$act = $this->showArticle($aid);
		if(!$act){
			echo '文章'.$aid.'生成错误,请检查！';
			return;
		}
		$count = count($ids);
		$nextkey = $k+1;
		if($nextkey<$count){
			$this->redirect('createArcHtml', array('strids' => $strids,'k'=>$nextkey), 0.5, '开始生成文章'.$ids[$nextkey].'...');	
		}else{
			echo '文章生成完成！';
		}
	}
	/** 
	 * 文章页模板赋值渲染,并生成html页面
	 * @id int 文章ID
	 */
	public function showArticle($id){
		/* 获取文章详细信息 */
		$Document = D('Document');
		$info = $Document->detail($id);
		if(!$info){
			$this->error($Document->getError());
		}
		/**模板路径**/
		$map['id'] = $info['category_id'];
		$tempath = M('Category')->where($map)->getField('template_detail');
		if(!$tempath) $tempath = 'default/article/detail';
		if(!file_exists('./template/Home/classic/'.$tempath.'.html')){
			echo '文章ID:'.$info['id'].'：'.$tempath.'模板不存在，没有成功生成文章<br>';
			return;
		}

		//面包屑
		$breadcrumbs = $this->getBreadcrumbs($info['category_id']);
		$this->assign('breadcrumbs',$breadcrumbs);
		//当前分类列表
		$current_cate = $this->showCurrentCate($info['category_id']);
		$this->assign('currentcate',$current_cate);
		//TDK
		$caiji_title = M('CaijiConfig')->where("name='WEB_SITE_TITLE'")->getField('value');
		$this->assign('title',$info['title'].'_'.$caiji_title);
		$description = msubstr(strip_tags($info['content']),0,100); 
		$description = str_replace(PHP_EOL,'',$description);
		$this->assign('description',trim($description));
		//导航
		$catelist = M('Category')->where("pid=0 and status=1")->select();
		$this->assign('catelist',$catelist);
		//文件夹路径
		$filepath = './article/detail/';
		if(!is_dir($filepath)){
			mkdir($filepath,0777,true);		
		}
		$this->assign('info',$info);
		$act = $this->buildHtml($id,HTML_PATH.$filepath.'/',C('HOME_TEMPLATE').$tempath);
		if($act){
			echo '文章ID:'.$info['id'].'生成成功<br>';
			return true;
		}else{
			echo '文章ID:'.$info['id'].'生成失败<br>';
			return false;
		}
	}

	/**面包屑**/
	public function getBreadcrumbs($category_id){
		$filepath = R('Article/getCatepath',array($category_id));
		$arr = explode('/',$filepath);
		$arr = array_filter($arr);
		$res = '<a href="/">首页</a>>';
		foreach($arr as $list){
			$title = M('Category')->where("name='".$list."'")->getField('title');
			$link .= $list.'/';
			$res .= '<a href="/'.$link.'">'.$title.'</a>>';
		}
		return $res;
	}
	/**当前分类**/
	public function showCurrentCate($category_id){
		$M = M('Category');
		$pid = $M->where("id=$category_id and status =1")->getField('pid');
//		if(!$pid) return;
		$map['pid'] = $pid;
		$map['status'] = 1;
		$list = $M->where($map)->field('id,name,title,pid')->select();
		foreach($list as $vo){
			$link = R('Article/getCatepath',array($vo['id']));
			$res[] = array('title'=>$vo['title'],'link'=>$link,'id'=>$vo['id']);
		}
		return $res;
	}
}