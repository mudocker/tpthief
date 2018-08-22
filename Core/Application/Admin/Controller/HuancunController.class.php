<?php
// +----------------------------------------------------------------------
// | OneThink [ WE CAN DO IT JUST THINK IT ]
// +----------------------------------------------------------------------
// | Copyright (c) 2013 http://www.onethink.cn All rights reserved.
// +----------------------------------------------------------------------
// | Author: 麦当苗儿 <zuojiazi@vip.qq.com> <http://www.zjzit.cn>
// +----------------------------------------------------------------------

namespace Admin\Controller;

/**
 * 后台用户控制器
 * @author 麦当苗儿 <zuojiazi@vip.qq.com>
 */
class HuancunController extends AdminController {
 

	/**
     * 批量保存配置
     * @author 麦当苗儿 <zuojiazi@vip.qq.com>
     */
    public function save($config){
        if($config && is_array($config)){
            $Config = M('HuancunConfig');
            if(I('POST.dirs')&&I('POST.cachetime')){
                $config['DIR_CACHE'] = $this->CacheConvert(I('POST.dirs'),I('POST.cachetime'));
            }
            foreach ($config as $name => $value) {
                $map = array('name' => $name);
                $Config->where($map)->setField(array('value'=>$value,'update_time'=>time()));
            }
        }
        $this->createHconfig();
        $this->success('保存成功！');
    }
    //缓存格式转换
    public function CacheConvert($dirs,$times){
        $dirs = array_filter($dirs);
        foreach($dirs as $k=>$mb){
            if($times[$k] == '') $this->error('时间不能为空！');
            $preg = preg_match("/^\d+$/is",$times[$k]);
            if(!$preg){
                $this->error('时间格式错误！');
            }
            if(!empty($mb)){
                $replace[] = base64_encode(stripslashes($mb)).'******'.$times[$k];
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
	/* 缓存设置 */
	public function index(){
        $list   =   M("HuancunConfig")->where(array('status'=>1,'group'=>1))->field('id,name,title,value,remark,type,extra')->order('sort')->select();
        if($list) {
            $this->assign('list',$list);
        }
        $this->assign('id',$id);
        $this->meta_title = '缓存设置';
        $this->display();
	}
	/** 缓存删除 **/
	public function del(){
		/**获取首页文件大小**/
		$indexinfo = filesize('./Runtime/Html/index.html');
		$indexsize = $this->getRealSize($indexinfo);
		$this->assign('indexsize',$indexsize);
		/**获取网站配置文件大小*/
		$webconf = filesize('./Runtime/Config/webconfig.php');
		$webconfsize = $this->getRealSize($webconf);
		$this->assign('webconfsize',$webconfsize);
		/**获取采集配置文件大小*/
		$cjconf = filesize('./Runtime/Config/cjconfig.php');
		$cjconfsize = $this->getRealSize($cjconf);
		$this->assign('cjconfsize',$cjconfsize);
		/**获取缓存配置文件大小*/
		$hconf = filesize('./Runtime/Config/hconfig.php');
		$hconfsize = $this->getRealSize($hconf);
		$this->assign('hconfsize',$hconfsize);

		$this->meta_title = '缓存删除';
		$this->display();
	}
	/**生成缓存配置文件**/
	public function createHconfig(){
		$dir = './Runtime/Config/';
		if(!is_dir($dir)) mkdir($dir,0755,true);		
		$file = $dir.'hconfig.php';
		$info = M('HuancunConfig')->where('status=1')->field('name,value')->select();
		$res = '<?php'.PHP_EOL.'/*缓存配置文件*/'.PHP_EOL.'return array('.PHP_EOL;
		foreach($info as $list){
			$res .= " '".$list['name']."' => '".$list['value']."',".PHP_EOL;
		}
		$res .= ');';
		file_put_contents($file,$res);	
	}
	/**获取缓存文件大小**/
	public function getFilesize($file){
		$path = './Runtime/Html/'.$file;
		if (!file_exists($path)) $this->error('0');
		$size = $this->getDirSize($path);		
		$res = $this->getRealSize($size);
		$this->success($res);
	}
	//获取文件夹大小
	public function getDirSize($dir)
	 { 
		$handle = opendir($dir);
		while (false!==($FolderOrFile = readdir($handle))){ 
			if($FolderOrFile != "." && $FolderOrFile != "..") 
			{ 
				if(is_dir("$dir/$FolderOrFile")){ 
					$sizeResult += $this->getDirsize("$dir/$FolderOrFile"); 
				}else{ 
					$sizeResult += filesize("$dir/$FolderOrFile"); 
				}
			} 
		}
		closedir($handle);
		return $sizeResult;
	 }
	// 单位自动转换函数
	public function getRealSize($size) { 
		$kb = 1024;   // Kilobyte
		$mb = 1024 * $kb; // Megabyte
		$gb = 1024 * $mb; // Gigabyte
		$tb = 1024 * $gb; // Terabyte
		if($size < $kb){ 
			return $size." B";
		}elseif($size < $mb){ 
			return round($size/$kb,2)." KB";
		}else if($size < $gb){ 
			return round($size/$mb,2)." MB";
		}else if($size < $tb){ 
			return round($size/$gb,2)." GB";
		}else{ 
			return round($size/$tb,2)." TB";
		}
	}

	/**删除缓存文件**/
	public function delFile($file){
		$base = './Runtime/Html';
		if($file == 'index'){
			$path = $base.'/index.html';
		}elseif($file == 'all'){
			$path = $base.'/';
		}else{
			$path = $base.'/'.$file;
		}
		$act = $this->delDirAndFile($path,1);
		if($act){
			$this->success('删除成功！');
		}else{
			$this->success('删除失败！');		
		}
	}

	/**删除配置文件**/
	public function delConf($file){
		$base = './Runtime/Config';
		$path = $base.'/'.$file.'.php';
		$act = $this->delDirAndFile($path,1);
		if($act){
			$this->success('删除成功！');
		}else{
			$this->success('删除失败！');		
		}
	}

	/**
	  +-----------------------------------------------------------------------------------------
	 * 删除目录及目录下所有文件或删除指定文件
	  +-----------------------------------------------------------------------------------------
	 * @param str $path   待删除目录路径
	 * @param int $delDir 是否删除目录，1或true删除目录，0或false则只删除文件保留目录（包含子目录）
	  +-----------------------------------------------------------------------------------------
	 * @return bool 返回删除状态
	  +-----------------------------------------------------------------------------------------
	 */
	public function delDirAndFile($path, $delDir = FALSE) {
		if (is_array($path)) {
			foreach ($path as $subPath)
				$this->delDirAndFile($subPath, $delDir);
		}
		if (is_dir($path)) {
			$handle = opendir($path);
			if ($handle) {
				while (false !== ( $item = readdir($handle) )) {
					if ($item != "." && $item != "..")
						is_dir("$path/$item") ? $this->delDirAndFile("$path/$item", $delDir) : unlink("$path/$item");
				}
				closedir($handle);
				if ($delDir)
					return rmdir($path);
			}
		} else {
			if (file_exists($path)) {
				return unlink($path);
			} else {
				return FALSE;
			}
		}
		clearstatcache();
	}

    /*
     * 删除所有缓存
     */
    public function delAll(){
        //删除首页html、CSS、JS、配置文件
        $base = './Runtime/Html';
        //首页
        $file[] = array('title'=>'首页','value'=>$base.'/index.html');
        //css
        $file[] = array('title'=>'CSS','value'=>$base.'/css');
        //JS
        $file[] = array('title'=>'JS','value'=>$base.'/js');
        //HTML
        $file[] = array('title'=>'HTML','value'=>$base.'/data');
        //配置文件
        $base2 = './Runtime/Config';
        $file[] = array('title'=>'采集配置','value'=>$base2.'/cjconfig.php');
        $file[] = array('title'=>'网站配置','value'=>$base2.'/webconfig.php');
        $file[] = array('title'=>'缓存配置','value'=>$base2.'/hconfig.php');
        $res = '';
        foreach($file as $list){
            $act = $this->delDirAndFile($list['value'],1);
            if($act){
                $res .=  $list['title'].'删除成功；';
            }else{
                $res .=  $list['title'].'删除失败；';
            }
        }
        $this->success($res);
    }
    /**
     * 目录缓存
     */
    public function dircache(){
        $list   =   M("HuancunConfig")->where(array('status'=>1,'group'=>3))->field('id,name,title,value,remark,type,extra')->order('sort')->select();
        if($list) {
            $this->assign('list',$list);
        }
        $this->meta_title = '目录缓存设置';
        $this->display();
    }
    public function dircacheFrame(){
        $Document = M('HuancunConfig');
        $res = $Document->where("name='DIR_CACHE'")->find();
        $data = array();
        if(!$res['value']){
            $num = 0;
        }else{
            $value = $res['value'];
            $arr = explode('##########',$value);
            $arr = array_filter($arr);
            foreach($arr as $list){
                $strs = explode('******',$list);
                //$find[] = trim($strs[0]);
                //$replace[] = trim($strs[1]);
                $data[] = array('find'=>trim($strs[0]),'replace'=>trim($strs[1]));
            }
            $num = count($data);
        }
        $num = $num==0?1:$num;
        $size = (intval ($num/5)+1)*5;
        $data = array_pad($data,$size,'');
        $this->assign('data',$data);
        $this->display('dircacheframe');
    }
}