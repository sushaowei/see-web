<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2016/6/11 0011
 * Time: 上午 1:01
 */

namespace see\web;


use see\base\Component;
use see\exception\NotFoundException;
use see\exception\ErrorException;

class Request extends Component
{

    private $_baseUrl;
    public function resolve()
    {
        $result = \See::$app->getUrlManager()->parseRequest($this);
        if($result){
            list($route, $params) = $result;
            $params += $this->getQueryParams();
            return [$route, $params];
        }else{
            throw new NotFoundException("Page not found.");
        }
    }

    public function getMethod()
    {
        if (isset($_SERVER['HTTP_X_HTTP_METHOD_OVERRIDE'])) {
            return strtoupper($_SERVER['HTTP_X_HTTP_METHOD_OVERRIDE']);
        }

        if (isset($_SERVER['REQUEST_METHOD'])) {
            return strtoupper($_SERVER['REQUEST_METHOD']);
        }

        return 'GET';
    }

    public function getIsGet()
    {
        return $this->getMethod() === 'GET';
    }

    public function getIsPost()
    {
        return $this->getMethod() === 'POST';
    }

    public function getIsAjax()
    {
        return isset($_SERVER['HTTP_X_REQUESTED_WITH']) && $_SERVER['HTTP_X_REQUESTED_WITH'] === 'XMLHttpRequest';
    }

    public function post($name, $defaultValue = null, $real = false)
    {
        if($real){
            $post = $_POST;
        }else{
            $post = $this->getPostParams();
        }
        return isset($post[$name]) ? $post[$name] : $defaultValue;
    }

    public function get($name, $defaultValue = null)
    {
        $get = $this->getQueryParams();
        return isset($get[$name]) ? $get[$name] : $defaultValue;
    }

    public function getQueryParams()
    {
        if(empty($_GET)){
            return [];
        }else{
            $get = $_GET;
//            array_walk_recursive($get, function(&$value,$key){
//               $value= addslashes(htmlentities($value));
//            });
            $this->SafeFilter($get);
            return $get;
        }
    }

    function SafeFilter (&$arr)
    {

        $ra=Array('/([\x00-\x08,\x0b-\x0c,\x0e-\x19])/','/script/','/javascript/','/vbscript/','/expression/','/applet/','/meta/','/xml/','/blink/','/link/','/style/','/embed/','/object/','/frame/','/layer/','/title/','/bgsound/','/base/','/onload/','/onunload/','/onchange/','/onsubmit/','/onreset/','/onselect/','/onblur/','/onfocus/','/onabort/','/onkeydown/','/onkeypress/','/onkeyup/','/onclick/','/ondblclick/','/onmousedown/','/onmousemove/','/onmouseout/','/onmouseover/','/onmouseup/','/onunload/',
            "/select\b|insert\b|update\b|delete\b|drop\b|;|\"|\'|\(|\)|\{|\}|\/\*|\*|\.\.\/|\.\/|union|into|load_file|outfile|dump/is",
            "/(<[^>]*)on[a-zA-Z]+\s*=([^>]*>)/isU",
            );

        if (is_array($arr))
        {
            foreach ($arr as $key => $value)
            {
                if (!is_array($value))
                {
                    if (!get_magic_quotes_gpc())             //不对magic_quotes_gpc转义过的字符使用addslashes(),避免双重转义。
                    {
                        $value  = addslashes($value);           //给单引号（'）、双引号（"）、反斜线（\）与 NUL（NULL 字符）加上反斜线转义
                    }
                    $value       = preg_replace($ra,'',$value);     //删除非打印字符，粗暴式过滤xss可疑字符串
                    $arr[$key]     = htmlentities(strip_tags($value)); //去除 HTML 和 PHP 标记并转换为 HTML 实体
                }
                else
                {
                    $this->SafeFilter($arr[$key]);
                }
            }
        }
    }

    public function getPostParams()
    {
        if(empty($_POST)){
            return [];
        }else{
            $post= $_POST;
            array_walk_recursive($post, function(&$value,$key){
                $value= addslashes($value);
            });
            return $post;
        }
    }

    public function getUrl()
    {
        if (isset($_SERVER['REQUEST_URI'])) {
            $url = $this->getHostInfo() . DIRECTORY_SEPARATOR . ltrim($_SERVER['REQUEST_URI'], '/' );
        } else {
            throw new ErrorException("Unable to get the request Uri");
        }
        return $url;
    }
    public function getIsSecureConnection()
    {
        return isset($_SERVER['HTTPS']) && (strcasecmp($_SERVER['HTTPS'], 'on') === 0 || $_SERVER['HTTPS'] == 1)
        || isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && strcasecmp($_SERVER['HTTP_X_FORWARDED_PROTO'], 'https') === 0;
    }
    public function getPathInfo()
    {
        return parse_url($this->getUrl());
    }

    public function getScheme()
    {
        $scheme = $this->getIsSecureConnection() ? 'https' : 'http';
        return $scheme;
    }

    public function getServerHost()
    {
        if (isset($_SERVER['HTTP_HOST'])) {
            $host = $_SERVER['HTTP_HOST'];
        }  else {
            throw new ErrorException("Can not get http_host");
        }
        return $host;
    }

    public function getPort()
    {
        return isset($_SERVER['SERVER_PORT']) ? $_SERVER['SERVER_PORT'] : null;
    }

    public function getHostInfo()
    {
        $scheme = $this->getScheme();
        $hostInfo = $scheme . '://' . $this->getServerHost();
        return $hostInfo;
    }

    public function getUserIp()
    {
        return isset($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] : null;
    }

    public function getRealUserIp()
    {
        if (isset($_SERVER)) {
            if (isset($_SERVER["HTTP_X_FORWARDED_FOR"])) {
                if (preg_match("/,/i", $_SERVER["HTTP_X_FORWARDED_FOR"])) {//CDN特殊处理
                    $_tmpIps = explode(',', $_SERVER["HTTP_X_FORWARDED_FOR"]);
                    $realip = $_tmpIps[0];
                } else {
                    $realip = $_SERVER["HTTP_X_FORWARDED_FOR"];
                }
            } else if (isset($_SERVER["HTTP_CLIENT_IP"])) {
                $realip = $_SERVER["HTTP_CLIENT_IP"];
            } else {
                $realip = $_SERVER["REMOTE_ADDR"];
            }
        } else {
            if (getenv("HTTP_X_FORWARDED_FOR")) {
                if (preg_match("/,/i", getenv("HTTP_X_FORWARDED_FOR"))) {//CDN特殊处理
                    $_tmpIps = explode(',', getenv("HTTP_X_FORWARDED_FOR"));
                    $realip = $_tmpIps[0];
                } else {
                    $realip = getenv("HTTP_X_FORWARDED_FOR");
                }
            } else if (getenv("HTTP_CLIENT_IP")) {
                $realip = getenv("HTTP_CLIENT_IP");
            } else {
                $realip = getenv("REMOTE_ADDR");
            }
        }
        return $realip;
    }

    public function getHeaders()
    {
        if (!function_exists('getallheaders')) {
            $headers = array();
            $copy_server = array(
                'CONTENT_TYPE'   => 'Content-Type',
                'CONTENT_LENGTH' => 'Content-Length',
                'CONTENT_MD5'    => 'Content-Md5',
            );
            foreach ($_SERVER as $key => $value) {
                if (substr($key, 0, 5) === 'HTTP_') {
                    $key = substr($key, 5);
                    if (!isset($copy_server[$key]) || !isset($_SERVER[$key])) {
                        $key = str_replace(' ', '-', ucwords(strtolower(str_replace('_', ' ', $key))));
                        $headers[$key] = $value;
                    }
                } elseif (isset($copy_server[$key])) {
                    $headers[$copy_server[$key]] = $value;
                }
            }
            if (!isset($headers['Authorization'])) {
                if (isset($_SERVER['REDIRECT_HTTP_AUTHORIZATION'])) {
                    $headers['Authorization'] = $_SERVER['REDIRECT_HTTP_AUTHORIZATION'];
                } elseif (isset($_SERVER['PHP_AUTH_USER'])) {
                    $basic_pass = isset($_SERVER['PHP_AUTH_PW']) ? $_SERVER['PHP_AUTH_PW'] : '';
                    $headers['Authorization'] = 'Basic ' . base64_encode($_SERVER['PHP_AUTH_USER'] . ':' . $basic_pass);
                } elseif (isset($_SERVER['PHP_AUTH_DIGEST'])) {
                    $headers['Authorization'] = $_SERVER['PHP_AUTH_DIGEST'];
                }
            }
            return $headers;
          
        }
        return getallheaders();
    }
    public function getScriptUrl(){
        return $_SERVER['PHP_SELF'];
    }
    
    public function getBaseUrl(){
        if($this->_baseUrl === null){
            $this->_baseUrl = trim($this->getHostInfo(), '\\/');
        }
        return $this->_baseUrl;
    }
    
    public function getScriptFile(){
        return $_SERVER['SCRIPT_FILENAME'];
    }
}