<?php
namespace Home\Controller;
use OT\DataDictionary;
/**
 * 前台首页控制器
 */
class IndexController extends HomeController {

    public function index($parameter=''){
        $conf = R('Loadconfig/webconfig');
        $plan = $conf['SPIDER_PLAN'];
        if($plan == 'curl'){
            R('IndexCurl/index',array($parameter));
        }else{
            R('IndexQuery/index',array($parameter));
        }
        //C('SHOW_PAGE_TRACE',true);
    }
}