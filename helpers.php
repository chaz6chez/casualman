<?php
declare(strict_types=1);
use Utils\Handler;

if (!function_exists('classname')) {
    function classname(string $classname, bool $line = true) : string
    {
        try {
            $classname = (new ReflectionClass($classname))->getShortName();
        } catch (ReflectionException $exception) {
            $classname = '';
        }
        if ($line) {
            $classname = hump2line($classname);
        }
        return $classname;
    }
}

if (!function_exists('rand_time')) {
    function rand_time(int $min = 1800, int $limit = 430200) : int
    {
        return rand($min,($min + $limit));
    }
}

if (!function_exists('debug_helper')) {
    function debug_helper(callable $func) :void
    {
        if(defined('DEBUG') and DEBUG){
            $func();
        }
    }
}

if (!function_exists('hump2line')) {
    function hump2line(string $name) : string
    {
        return strtolower(preg_replace('/(?<=[a-z])([A-Z])/', '_$1', $name));
    }
}

if (!function_exists('line2hump')) {
    function line2hump(string $name)
    {
        return preg_replace_callback('/_+([a-z])/', function ($matches) {
            return strtoupper($matches[1]);
        }, $name);
    }
}

/**
 * 浮点检查
 */
if (!function_exists('float_checker')) {
    function float_checker($float, int $decimal_limit = 3, bool $unsigned = true) : bool
    {
        if (is_numeric($float)) {
            if ($unsigned) {
                if (bccomp((string)$float, '0', $decimal_limit) < 0) {
                    return false;
                }
            }
            return boolval(decimal_places((string)$float) <= $decimal_limit);
        }
        return true;
    }
}

/**
 * 连接检查
 */
if (!function_exists('url_checker')) {
    function url_checker($url) : bool
    {
        if (is_string($url) and $url) {
            return Handler::factory('url')->validate($url);
        }
        return true;
    }
}

/**
 * 小数位数
 */
if (!function_exists('decimal_places')) {
    function decimal_places(string $float) : int
    {
        return ($need = strrchr($float, '.'))
            ? strlen(substr($need, 1))
            : 0;
    }
}

if(!function_exists('process_time_checker')){
    /**
     * @param int $timeout 秒
     */
    function process_timeout_checker(int $timeout = 5){
        global $PROCESS_TIME;
        $PROCESS_TIME = time();
        declare(ticks=1);
        register_tick_function(function ($timeout){
            global $PROCESS_TIME;
            if(time() - $PROCESS_TIME > $timeout){
                exit("Timeout {$timeout} seconds");
            }
        },$timeout);
    }
}

/**
 * 判断是否是unix时间戳
 */
if (!function_exists('is_timestamp')){
    /**
     * @param $timestamp
     * @return int|bool
     */
    function is_timestamp($timestamp) {
        if(strtotime(date('Y-m-d H:i:s', $timestamp)) === $timestamp) {
            return $timestamp;
        } else {
            return false;
        }
    }
}

/**
 * 驼峰转下划线
 */
if(!function_exists('camel2lower')) {
    /**
     * @param $str
     * @return string
     */
    function camel2lower($str): string
    {
        return \Utils\Tools::CamelToLower($str);
    }
}

/**
 * 下划线转驼峰
 */
if(!function_exists('lower2camel')) {
    /**
     * @param $str
     * @return string
     */
    function lower2camel($str): string
    {
        return \Utils\Tools::LowerToCamel($str);
    }
}

/**
 * 创建PHP文件
 */
if(!function_exists('build_php')){
    /**
     * @param $path
     * @return string
     */
    function build_php($path): string
    {
        $content = '<?php ';
        $path = (array)$path;
        $files = [];

        foreach ($path as $p) {
            $files = array_merge($files, glob($p . '*.php'));
            $files = array_merge($files, glob($p . '*/*.php'));
        }

        foreach ($files as $f) {
            $c = php_strip_whitespace($f);
            $c = trim(str_replace(['<?php', '?>'], '', $c));
            $reg = '/^\s*(namespace\s+.+?);/sm';
            if (preg_match($reg, $c)) {
                $c = preg_replace($reg, '$1 {', $c) . "}\n";
            } else {
                $c = "namespace {" . $c . "}\n";
            }
            $content .= $c;
        }
        return $content;
    }
}

/**
 * 根据PHP各种类型变量生成唯一标识号
 */
if(!function_exists('to_guid_string')){
    /**
     * @param mixed $mix 变量
     * @return string
     */
    function to_guid_string($mix): string
    {
        if (is_object($mix)) {
            return spl_object_hash($mix);
        } elseif (is_resource($mix)) {
            $mix = get_resource_type($mix) . strval($mix);
        } else {
            $mix = serialize($mix);
        }
        return md5($mix);
    }
}

/**
 * 数组转xml
 */
if(!function_exists('array2xml')) {
    /**
     * @param $arr
     * @return string
     */
    function array2xml($arr): string
    {
        return \Utils\Tools::ArrayToXml($arr);
    }
}

/**
 * 数组与字符串之间的相互简易转换
 */
if(!function_exists('arr_str')) {
    /**
     * @param $input
     * @param string $tag
     * @return array|string
     */
    function arr_str($input,$tag = 'ARRAY') {
        if(is_array($input)){
            return $tag.serialize($input);
        }
        return unserialize(mb_substr($input,mb_strlen($tag)));
    }
}

/**
 * 数组与字符串之间的相互简易转换
 */
if(!function_exists('arr_uri')) {
    /**
     * @param array|string $input
     * @return array|string
     */
    function arr_uri($input) {
        if(is_array($input)){
            $uri = '';
            foreach ($input as $k => $v){
                $uri .= "&{$k}={$v}";
            }
            return ltrim($uri,'&');
        }
        $input = explode('&',$input);
        $array = [];
        foreach ($input as $v){
            $v = explode('=',$v);
            if (count($v) > 1) $array[$v[0]] = $v[1];
        }
        return $array;
    }
}

/**
 * 对象转数组
 */
if(!function_exists('object2array')) {
    /**
     * @param $object
     * @return array
     */
    function object2array($object): array
    {
        return json_decode(json_encode($object), true);
    }
}

/**
 * 判断是否是序列化字符串
 */
if(!function_exists('is_serialize')){

    /**
     * @param $data
     * @return bool
     */
    function is_serialized($data): bool
    {
        if (is_array($data)) {
            return false;
        }
        $data = trim($data);
        if ('N;' == $data)
            return true;
        if (!preg_match('/^([adObis]):/', $data, $badions))
            return false;
        switch ($badions[1]) {
            case 'a' :
            case 'O' :
            case 's' :
                if (preg_match("/^{$badions[1]}:[0-9]+:.*[;}]\$/s", $data))
                    return true;
                break;
            case 'b' :
            case 'i' :
            case 'd' :
                if (preg_match("/^{$badions[1]}:[0-9.E-]+;\$/", $data))
                    return true;
                break;
        }
        return false;
    }
}

/**
 * 获取当前毫秒时间
 */
if(!function_exists('get_millisecond')){
    /**
     * @return float
     */
    function get_millisecond() {
        list($t1, $t2) = explode(' ', microtime());
        return (float)sprintf('%.0f', (floatval($t1) + floatval($t2)) * 1000);
    }
}

/**
 * 获取内存占用
 */
if(!function_exists('get_memory_used')){
    /**
     * @return float
     */
    function get_memory_used(){
        return \Utils\Tools::getMemoryUsed();
    }
}

/**
 * 获取变量名
 */
if(!function_exists('get_variable_name')){
    /**
     * @param $var
     * @param null|object|array $scope
     * @return false|int|string
     */
    function get_variable_name(&$var, $scope = null){
        $scope = $scope==null? $GLOBALS : $scope;
        $tmp = $var;
        $var = 'tmp_value_'.mt_rand();
        if(is_object($scope)){
            $scope = object2array($scope);
        }
        $name = array_search($var, $scope,true);
        $var = $tmp;
        return $name;
    }
}

/**
 * 多维数组分类排序
 */
if(!function_exists('array_group_sort')){

    /**
     * @param array $array
     * @param string $key
     * @param int $type 降序：SORT_DESC 升序 SORT_ASC
     * @return array|bool
     */
    function array_group_sort(array $array,$key,$type = SORT_ASC) {
        $column = array_column($array,$key);
        if(!$column){
            return false;
        }
        if(array_multisort($column,$type,$array)){
            return $array;
        }
        return false;
    }
}

/**
 * 二维数组搜索
 */
if(!function_exists('search_in_array')){

    /**
     * @param array $array 数组
     * @param array $where ['关键字'=>'值']
     * @param bool $get 是否获取该数组值
     * @return array|false|int|mixed|string
     */
    function search_in_array(array $array,array $where,$get = false) {
        $key = key($where);
        $val = $where[$key];
        $column = array_column($array,$key);
        if($column){
            $arrayKey = array_search($val,$column);
            if($get){
                return $array[$arrayKey];
            }
            return $arrayKey;
        }
        return $column;
    }
}

/**
 * 获取错误码
 */
if(!function_exists('error_code')){

    /**
     * @param $msg
     * @return mixed|null
     */
    function error_code($msg) {
        $err = explode('|',$msg);
        if(is_array($err) and count($err) > 1){
            return $err[0];
        }
        return $msg;
    }
}

/**
 * 获取提示信息
 */
if(!function_exists('notice_msg')){

    /**
     * @param $msg
     * @return mixed|null
     */
    function notice_msg($msg) {
        $data = explode('|',$msg);
        if(is_array($data) and count($data) > 1){
            return $data[1];
        }
        return $msg;
    }
}

/**
 * 内容解密
 */
if(!function_exists('base64_urlencode')){
    /**
     * @param $string
     * @return mixed|string
     */
    function base64_urlencode($string) {
        $data = base64_encode($string);
        $data = str_replace(
            ['+','/','='],
            ['-','_',''],
            $data
        );
        return $data;
    }
}


/**
 * 内容加密
 */
if(!function_exists('base64_urldecode')){

    /**
     * @param $string
     * @return bool|string
     */
    function base64_urldecode($string) {
        $data = str_replace(
            ['-','_'],
            ['+','/'],
            $string
        );
        $mod4 = strlen($data) % 4;
        if ($mod4) {
            $data .= substr('====', $mod4);
        }
        return base64_decode($data);
    }
}

/**
 * 环境变量获取
 */
if(!function_exists('env')){
    /**
     * @param null $name
     * @param null $default
     * @return array|bool|false|mixed|null|string
     */
    function env($name = null,$default = null) {
        return \Kernel\Env::get($name,$default);
    }
}


/**
 * 数组key中下划线替换为中横线
 */
if(!function_exists('array_key_replace')){

    /**
     * @param array $array
     * @return array
     */
    function array_key_replace(array $array) {
        $result = [];
        foreach ($array as $key => $value){
            $k = str_replace('_','-',$key);
            $result[$k] = $value;
        }
        return $result;
    }
}