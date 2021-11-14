<?php

/**
 * @File 		: PluginHelp 
 * @Encoding 	: UTF-8
 * @Created on 	: 2020年10月21日 下午12:52:38 by 张长远
 * @Copyright   : Copyright (c) 2020, CoreCMS
 * @Description	: 
 */

namespace think\coreplugins;

use think\Db;
use think\facade\Hook;

class PluginHelp
{
    //基础路径
    private $basePath;
    
    public function __construct() 
    {
        
        $this->basePath = getCorePluginPath(); 
        if(!is_dir($this->basePath) && is_writeable(app()->getRootPath())){
            @mkdir($this->basePath,0777,true);
        }
    }
    
    /**
     * 初始化钩子调用信息
     * 
     */
    public function init()
    {
        $list = Db::name('extensions')->where(["type"=>"plugin","status"=>"normal"])->select();
        
        if(count($list)<=0){
            return;
        }
        
        $eventList = [];
        
        foreach($list as $plugin){
            $file = $this->basePath.
                "{$plugin["folder"]}/{$plugin["element"]}/{$plugin["element"]}.json";
            if(file_exists($file) == false){
                continue;
            }
            $config = json_decode(file_get_contents($file),true);
            $class = getCorePluginsPrefix().
                "{$plugin["folder"]}\\{$plugin["element"]}\\". ucfirst($plugin["element"]);
            //解析事件
            $events = explode(",", $config["event"]);
            foreach($events as $ev){
                $arr = explode(":",$ev);
                if(!isset($arr[1])){
                    $arr[1] = 0;
                }
                if(!isset($eventList[$arr[0]])){
                    $eventList[$arr[0]] = [];
                }
                //把事件对应的类放到事件的数组里
                $eventList[$arr[0]][] = [
                    "sort"=>$arr[1],
                    "class" => $class,
                ];
            }
                //Hook::add($config["event"],$class);
        }
        
        //对事件排序,并绑定事件
        foreach($eventList as $event=>$items){
            $this->sortEvent($items);
            $classList = array_column($items, "class");
            Hook::add($event,$classList);
        }
        
        
    }
        
    /**
     * 对事件下的类按照sort进行排序
     * @param type $list
     */
    private function sortEvent(&$list)
    {
        $sort = [];
        foreach($list as $k=>$v){
            $sort[$k] = $v["sort"];
        }
        
        array_multisort($sort,SORT_DESC,$list);
    }
 
    

    
}