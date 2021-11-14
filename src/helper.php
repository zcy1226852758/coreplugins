<?php

/**
 * @File 		: helper 
 * @Encoding 	: UTF-8
 * @Created on 	: 2020年10月21日 上午11:23:08 by 张长远
 * @Copyright   : Copyright (c) 2020, CoreCMS
 * @Description	: 辅助入口文件
 */
use think\coreplugins\PluginHelp;
use think\facade\Hook;

/*
 *  插件类库自动载入
 */
spl_autoload_register(function ($_class) {
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
    $file = $base_dir . str_replace('\\', '/', $relativeClass) . '.php';

    // 如果文件存在，加载它
    if (file_exists($file)) {
        require_once $file;
    }
});

Hook::add("app_init", function() {
    $installLockFile = app()->getRuntimePath() . "install.lock";
    if (is_file($installLockFile)) {
        (app()->get(PluginHelp::class))->init();
    }
});

/**
 * 处理插件钩子
 * @param string $hook   钩子名称
 * @param mixed  $params 传入参数
 * @return void
 */
function hook($hook, $params = []) {
    $installLockFile = app()->getRuntimePath() . "install.lock";    
    
    if (is_file($installLockFile)) {
        Hook::listen($hook, $params);
    }
}

/**
 * 获取后台自定义配置
 * @staticvar array $params
 * @param type $folder
 * @param type $element
 * @return boolean|array
 */
function getCorePluginCusParam($folder, $element) {
    static $params = [];
    $key = "{$folder}-{$element}";
    if (isset($params[$key])) {
        return $params[$key];
    }
    $info = Db::name('extensions')
            ->where([
                "type" => "plugin",
                "folder" => $folder,
                "element" => $element,
                "status" => "normal"])
            ->find();

    if (!$info) {
        return false;
    }
    $params[$key] = json_decode($info["params"], true);
    return $params[$key];
}

/**
 * 获取插件的配置信息
 * @staticvar array $config
 * @param type $folder
 * @param type $elemet
 * @return boolean|array
 */
function getCorePluginConfig($folder, $element) {
    static $config = [];
    $key = "{$folder}-{$element}";
    if (isset($config[$key])) {
        return $config[$key];
    }
    $configFile = getCorePluginPath() . "{$folder}/{$element}/{$element}.json";
    if (is_file($configFile)) {
        $config[$key] = json_decode(file_get_contents($configFile), true);
        return $config[$key];
    }
    return false;
}

/**
 * 根据插件类名称,解析出folder,element,classname
 * @param type $class
 * @return type
 */
function parseCorePluginClass($class) {
    $prefix = getCorePluginsPrefix();
    $str = str_replace($prefix, "", $class);
    $arr = explode("\\", $str);
    return [
        "folder" => $arr[0],
        "element" => $arr[1],
        "classname" => $arr[2],
    ];
}

/**
 * 获取插件路径
 * @return type
 */
function getCorePluginPath() {
    return app()->getRootPath() . 'plugins/';
}

/**
 * 获取命名空间前缀
 * @return string
 */
function getCorePluginsPrefix() {
    return 'corecms\\plugins\\';
}

/**
 * 下划线命名风格转换成驼峰命名风格
 * @param $string
 * @param bool $ucfirst 转换后首字母是否大写
 * @return mixed|string
 */
function parseCamel($string, $ucfirst = false) {
    //替换过程 name_style => _s => s => S => nameStyle
    $string = preg_replace_callback('/_([a-zA-Z])/', function ($match) {
        return strtoupper($match[1]);
    }, $string);

    return $ucfirst ? ucfirst($string) : $string;
}

/**
 * 驼峰命名风格转换成下划线命名风格
 * @param $string
 * @return string
 */
function parseUnderline($string) {
    //替换过程 NameStyle => N | S => _N | _S => _Name_Style => Name_Style => name_style
    $string = strtolower(trim(preg_replace("/[A-Z]/", "_\\0", $string), "_"));

    return $string;
}

//Hook::add("app_init","\\corecms\\coreplugins\\appInit\\test");
//Hook::add("app_end","\\corecms\\coreplugins\\appEnd\\test");