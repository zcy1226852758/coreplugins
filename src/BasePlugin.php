<?php

namespace think\coreplugins;

use think\facade\View; 
use think\facade\App; 

abstract class BasePlugin
{
    protected $view;
    protected $config = [];
    protected $pluginName;

    public function __construct()
    {
        $this->view = clone View::engine('Think');
        $this->pluginPath = getCorePluginPath();
        $this->config = $this->getConfig();
        $this->pluginName = $this->config["element"];
        
        
//        $this->view->config([
//            'view_path' => $this->pluginPath.
//                            "{$this->config["folder"]}/{$this->config["element"]}/view/",
//        ]);

    }
    
     /**
     * 加载模板输出 
     * @param string $template
     * @param array $vars           模板文件名
     * @return false|mixed|string   模板输出变量
     * @throws \think\Exception
     */
    protected function fetch($template = '', $vars = [])
    {
        $this->setViewConfig();
        if(empty($template)){
            $template = "/".$this->pluginName;
        }
        //echo $template;
        return $this->view->fetch($template, $vars);
    }

    /**
     * 渲染内容输出
     * @access protected
     * @param  string $content 模板内容
     * @param  array  $vars    模板输出变量
     * @return mixed
     */
    protected function display($content = '', $vars = [])
    {
        $this->setViewConfig();
        return $this->view->display($content, $vars);
    }
    
    protected function setViewConfig()
    {
        $this->view->config([
            'view_path' => $this->pluginPath.
                            "{$this->config["folder"]}/{$this->config["element"]}/view/",
        ]);
    }

    /**
     * 模板变量赋值
     * @access protected
     * @param  mixed $name  要显示的模板变量
     * @param  mixed $value 变量的值
     * @return $this
     */
    protected function assign($name, $value = '')
    {
        $this->view->assign([$name => $value]);

        return $this;
    }

    /**
     * 初始化模板引擎
     * @access protected
     * @param  array|string $engine 引擎参数
     * @return $this
     */
    protected function engine($engine)
    {
        $this->view->engine($engine);

        return $this;
    }
    
    /**
     * 获取插件配置
     * @return mixed|null
     */
    final protected function getConfig()
    {
        $_class = get_class($this);
        $class = ltrim($_class, '\\');
        $prefix = getCorePluginsPrefix();    
        $len = strlen($prefix);
        if (strncmp($prefix, $class, $len) !== 0) {
            // 否，交给下一个已注册的自动加载函数
            return;
        }
        $base_dir = getCorePluginPath();
        $relativeClass = substr($class, $len);
        // 命名空间前缀替换为基础目录，
        // 将相对类名中命名空间分隔符替换为目录分隔符，
        // 附加 .php
        $file = $base_dir . str_replace('\\', '/', strtolower($relativeClass)) . '.json';
        return json_decode(file_get_contents($file),true);
    }
}