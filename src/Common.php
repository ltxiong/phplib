<?php
namespace Ltxiong\PhpLib;


/**
 * Class Common
 */
class Common
{

    /**
     * 返回客户端的IP地址
     *
     * @param boolean $is_int 所否返回整形IP地址
     *
     * @return int/string $ip 用户IP地址
     */
    public static function getIp($is_int = false)
    {

        if (!empty($_SERVER['REMOTE_ADDR']))
        {
            $ip = $_SERVER["REMOTE_ADDR"];
        }
        else if (!empty($_SERVER["HTTP_CLIENT_IP"]))
        {
            $ip = $_SERVER["HTTP_CLIENT_IP"];
        }

        if (!empty($_SERVER['HTTP_X_FORWARDED_FOR']))
        {
            $ips = explode(", ", $_SERVER['HTTP_X_FORWARDED_FOR']);
            if ($ip)
            {
                array_unshift($ips, $ip);
                $ip = false;
            }
            for ($i = 0; $i < count($ips); $i++)
            {
                if (!preg_match("/^(0|10|127|172\.16|192\.168)\./", $ips[$i]))
                {
                    $ip = $ips[$i];
                    break;
                }
            }
        }
        $ip = $ip ? $ip : $_SERVER['REMOTE_ADDR'];
        $ip = $ip ? $ip : '0.0.0.0';

        return $is_int ? bindec(decbin(ip2long($ip))) : $ip;
    }

    /**
     * 将简体字字符串转换成繁体字字符串
     *
     * @param string $source_str 需要转换的字符串
     *
     * @return string
     */
    public static function convert2Traditional($source_str)
    {

        $tool = new TraditionSimpleConvert(SimplifiedTraditionalData::$simplified_arr, SimplifiedTraditionalData::$traditional_arr);
        //简体转繁体：
        $rs = $tool->simple2Tradition($source_str);

        return $rs;
    }

    /**
     * 获取分页数据
     *
     * @param int $page
     * @param int $page_size
     *
     * @return string
     */
    public static function getPageLimit($page = 1, $page_size = 15)
    {

        if (!is_numeric($page))
        {
            $page = 1;
        }
        if (!is_numeric($page_size))
        {
            $page_size = 15;
        }
        $offset = ($page - 1) * $page_size;

        return "$offset, $page_size";
    }

    /**
     * @desc 普通字符截取..
     * @access public
     *
     * @param string $str
     * @param int $len
     *
     * @return string
     */
    public static function cutstr($str, $length = 10, $after = '')
    {

        $len = mb_strlen($str, 'utf-8');
        $str = mb_substr($str, 0, $length, 'utf-8');
        $str .= $len > $length ? $after : '';

        return $str;
    }


    /**
     * 判断是否为电话号码
     *
     * @param int $email 电话号码
     *
     * @return boolean
     */
    public static function isMobile($modile)
    {

        return preg_match('/^(13|14|15|17|18)\d{9}$/', $modile) > 0;
    }

    /**
     * 产生随机数字
     * @access public
     *
     * @param int $length 随机码长度
     *
     * @return string
     */
    public static function getRandomNum($length)
    {

        $hash = '';
        $chars = '123456789';
        $max = strlen($chars) - 1;
        for ($i = 0; $i < $length; $i++)
        {
            $hash .= $chars[mt_rand(0, $max)];
        }

        return $hash;
    }

    /**
     * 产生随机数字或者字母
     * @access public
     *
     * @param int $length 随机码长度
     *
     * @return string
     */
    public static function getRandomString($length)
    {

        $hash = '';
        $chars = '123456789abcdefghijklmnpqrstuvwxyzABCDEFGHIJKLMNPQRSTUVWXZY';
        $max = strlen($chars) - 1;
        for ($i = 0; $i < $length; $i++)
        {
            $hash .= $chars[mt_rand(0, $max)];
        }

        return $hash;
    }

    /**
     * 处理内容
     * @access public
     *
     * @param string $content 帖子内容
     * @param int $length 处理帖子内容
     *
     * @return void
     */
    public static function substringContent($content, $length = 0)
    {

        $len = mb_strlen($content);
        $content = mb_substr($content, 0, $length, 'utf-8');
        if ($len > $length)
        {
            $content .= '……';
        }

        $content = self::handleFace($content);

        return $content;
    }

    /**
     * substring()  截取字符串
     * $string - 字符串
     * $length - 长度
     * $dot - 连接字符串
     */
    public static function substring($str, $length, $dot = '', $start = 0)
    {

        $str = htmlspecialchars($str);
        $i = 0;
        // 完整排除之前的UTF8字符
        while ($i < $start)
        {
            $ord = ord($str{$i});
            if ($ord < 192)
            {
                $i++;
            }
            elseif ($ord < 224)
            {
                $i += 2;
            }
            else
            {
                $i += 3;
            }
        }
        // 开始截取
        $result = '';
        while ($i < $start + $length && $i < strlen($str))
        {
            $ord = ord($str{$i});
            if ($ord < 192)
            {
                $result .= $str{$i};
                $i++;
            }
            elseif ($ord < 224)
            {
                $result .= $str{$i} . $str{$i + 1};
                $i += 2;
            }
            else
            {
                $result .= $str{$i} . $str{$i + 1} . $str{$i + 2};
                $i += 3;
            }
        }
        if ($i < strlen($str))
        {
            $result .= $dot;
        }

        return $result;
    }

    /**
     * @desc   curl处理请求
     * @access public
     *
     * @param string $url 请求地址
     * @param array $data post 数组
     * @param int $timeout 过期时间
     *
     * @return array
     */
    public static function curlPost($url, $data = array(), $timeout = 30)
    {

        $ssl = substr($url, 0, 8) == "https://" ? true : false;
        $ch = curl_init();
        $opt = array(CURLOPT_URL => $url, CURLOPT_HEADER => 0, CURLOPT_RETURNTRANSFER => 1, CURLOPT_TIMEOUT => $timeout);
        if (!empty($data))
        {
            $opt[CURLOPT_POST] = 1;
            $opt[CURLOPT_POSTFIELDS] = $data;
        }
        if ($ssl)
        {
            $opt[CURLOPT_SSL_VERIFYHOST] = 1;
            $opt[CURLOPT_SSL_VERIFYPEER] = false;
        }
        curl_setopt_array($ch, $opt);
        $data = curl_exec($ch);
        curl_close($ch);

        return $data;
    }

    public static function httpGet($url, $data = null, $method = 'GET', $headers = array(), $timeout = 10)
    {

        if (strtoupper($method) == 'GET' AND $data)
        {
            $url .= (strstr($url, '?') ? '&' : '?') . http_build_query($data);
        }
        $ch = curl_init();

        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_TIMEOUT, $timeout);
        curl_setopt($ch, CURLOPT_USERAGENT, 'LAMABANG-APP-SYSTEM');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        if (strtoupper($method) == 'POST')
        {
            curl_setopt($ch, CURLOPT_POST, count($data));
            curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        }

        $scheme = parse_url($url, PHP_URL_SCHEME);
        if ($scheme == 'https')
        {
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        }
        $start_time = microtime(true);
        $content = (string)curl_exec($ch);
        curl_close($ch);

        return $content;
    }

    /**
     * 格式化查询条件
     *
     * @param array $whereArr =array(array('字段', '值', '操作符'),...)
     *
     * @return string
     */
    public static function formatWhere($whereArr)
    {

        $where = '';
        if (!empty($whereArr))
        {
            foreach ((array)$whereArr as $value)
            {
                list($prefix, $suffix) = (!empty($value[2]) && strtoupper($value[2]) == 'LIKE') ? array('%', '%') : array('', '');
                if (!empty($value[2]) && strtoupper($value[2]) == "IN")
                {
                    $where .= (empty($where) ? ' WHERE ' : ' AND ') . $value[0] . " IN ({$value[1]}) ";
                }
                else
                {
                    $where .= (empty($where) ? ' WHERE ' : ' AND ') . $value[0] . ' ' . (empty($value[2]) ? '=' : $value[2]) . " '" . $prefix . $value[1] . $suffix . "' ";
                }
            }
        }

        return $where;
    }


    /**
     * 命令行传递进来的参数格式为 a=12&b=3445
     * 解析由命令行传递进来的函数参数，转化成统一的 key => value 数组格式
     * 
     */
    public static function parseCliReqArgv($cli_req_argv)
    {
        $req_args_arr = array();
        $request_args_arr = isset($cli_req_argv) && $cli_req_argv ? explode('&', $cli_req_argv) : array();
        if (count($request_args_arr) == 0)
        {
            return $req_args_arr;
        }
        foreach($request_args_arr as $v)
        {
            $tmp_arr = explode('=', $v);
            if(count($tmp_arr) != 2)
            {
                continue;
            }
            $req_args_arr[$tmp_arr[0]] = $tmp_arr[1];
        }
        return $req_args_arr;
    }

    /**
     * 命令行传递进来的参数格式为 request_uri=/newindex/index
     * 获取由命令行传递的 request_uri 参数，并且进行解析，解析成 module/controller/action 通用格式，转化成统一的 key => value 数组格式
     * 
     */
    public static function parseYafCliReqUri($cli_req_uri)
    {
        $module = 'index';
        $controller = '';
        $action = '';
        $request_uri_arr = isset($cli_req_uri) && $cli_req_uri ? explode('/', str_replace('request_uri=', '', $cli_req_uri)) : array();
        if($request_uri_arr[0] == '')
        {
            unset($request_uri_arr[0]);
            $request_uri_arr = array_values($request_uri_arr);            
        }
        if(count($request_uri_arr) == 2)
        {
            $controller = strtolower($request_uri_arr[0]);
            $action = strtolower($request_uri_arr[1]);
        }
        else if (count($request_uri_arr) == 3)
        {
            $module = strtolower($request_uri_arr[0]);
            $controller = strtolower($request_uri_arr[1]);
            $action = strtolower($request_uri_arr[2]);
        }
        else
        {
            $controller = 'error';
            $action = 'error';
        }
        return array('module' => $module, 'controller' => $controller, 'action' => $action);
    }

}
