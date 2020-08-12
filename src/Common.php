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



	/**
	 * @desc    方法：返回时间戳毫秒
	 * @access public
	 * @param void
	 * @return int
	 */
	public static function getMillisecond()
	{
		list($t1, $t2) = explode(' ', microtime());
		return (float)sprintf('%.0f', (floatval($t1) + floatval($t2)) * 1000);
    }
    

    /**
	 * @desc PHP 5.5.0中自带的array_column的简单兼容，详细可查看PHP手册
	 * @access public
	 * @update 2016-05-19
	 * @author 
	 * @param array $input
	 * @param string|int|null  $columnKey
	 * @param string|int|null $indexKey
	 * @return array
	 */
	public function arrayColumn(array $array, $column_key, $index_key=null){
		if (version_compare(PHP_VERSION, '5.5.0', '>='))
	    {
	        return array_column($array, $column_key, $index_key);
	    }
        $result = [];
        foreach($array as $arr) {
            if(!is_array($arr)) continue;

            if(is_null($column_key)){
                $value = $arr;
            }else{
                $value = $arr[$column_key];
            }

            if(!is_null($index_key)){
                $key = $arr[$index_key];
                $result[$key] = $value;
            }else{
                $result[] = $value;
            }
        }
        return $result; 
    }

    
    /**
	 * @desc  获取用户真实ip
	 * 根据微信获取逻辑：默认REMOTE_ADDR,当有代理时取代理真实ip
	 * @author 
	 * @date: 2016年7月19日
	 * @access public
	 * @return string
	 */
	public static function getRealIp()
	{
	    $ip = $_SERVER["REMOTE_ADDR"];//初始ip
	    $x_forwarded_for = isset($_SERVER['HTTP_X_FORWARDED_FOR']) && !empty($_SERVER['HTTP_X_FORWARDED_FOR'])
	                       ? $_SERVER['HTTP_X_FORWARDED_FOR']
	                       : '';
	    $x_real_ip = isset($_SERVER['HTTP_X_REAL_IP']) && !empty($_SERVER['HTTP_X_REAL_IP'])
	                       ? $_SERVER['HTTP_X_REAL_IP']
	                       : '';
	    //处理代理ip
	    $tmpIp = !empty($x_forwarded_for) ? $x_forwarded_for : $x_real_ip;
	    if (!empty($tmpIp))
	    {
	        $ip = $tmpIp;
	        //如果含多级，则取第一个
	        if (false !== strpos($tmpIp, ','))
	        {
	            $ipArr = explode(',', $tmpIp);
	            $ip = trim($ipArr[0]);
	        }
	    }

	    return $ip;
    }



    /**
     * linux系统探测，返回操作系统中当时时刻 cpu memory load_avg 等信息
     *
     * @return void
     */
    public static function sysLinuxInfo() 
    {
        $res = array();
        // CPU 信息
        $cpuinfo_str = @file("/proc/cpuinfo");
        if($cpuinfo_str)
        {
            $cpuinfo_str = implode("", $cpuinfo_str);
            @preg_match_all("/model\s+name\s{0,}\:+\s{0,}([\w\s\)\(\@.-]+)([\r\n]+)/s", $cpuinfo_str, $model);
            @preg_match_all("/cpu\s+MHz\s{0,}\:+\s{0,}([\d\.]+)[\r\n]+/", $cpuinfo_str, $mhz);
            @preg_match_all("/cache\s+size\s{0,}\:+\s{0,}([\d\.]+\s{0,}[A-Z]+[\r\n]+)/", $cpuinfo_str, $cache);
            @preg_match_all("/bogomips\s{0,}\:+\s{0,}([\d\.]+)[\r\n]+/", $cpuinfo_str, $bogomips);
            if (false !== is_array($model[1]))
            {
                $res['cpu']['num'] = sizeof($model[1]);
                $res['cpu']['num_text'] = str_replace(array(1, 2, 4, 8, 16), array('单', '双', '四', '八', '十六'), $res['cpu']['num']).'核';
                
                for($i = 0; $i < $res['cpu']['num']; $i++) 
                {
                    $res['cpu']['model'][] = $model[1][$i].'&nbsp;('.$mhz[1][$i].')';
                    $res['cpu']['mhz'][] = $mhz[1][$i];
                    $res['cpu']['cache'][] = $cache[1][$i];
                    $res['cpu']['bogomips'][] = $bogomips[1][$i];
                }
                $x1 = ($res['cpu']['num'] == 1) ? '' : ' ×'.$res['cpu']['num'];
                $mhz[1][0] = ' | 频率:'.$mhz[1][0];
                $cache[1][0] = ' | 二级缓存:'.$cache[1][0];
                $bogomips[1][0] = ' | Bogomips:'.$bogomips[1][0];
                $res['cpu']['model'][] = $model[1][0].$mhz[1][0].$cache[1][0].$bogomips[1][0].$x1;
                if (false !== is_array($res['cpu']['model'])) $res['cpu']['model'] = implode("<br />", $res['cpu']['model']);
                if (false !== is_array($res['cpu']['mhz'])) $res['cpu']['mhz'] = implode("<br />", $res['cpu']['mhz']);
                if (false !== is_array($res['cpu']['cache'])) $res['cpu']['cache'] = implode("<br />", $res['cpu']['cache']);
                if (false !== is_array($res['cpu']['bogomips'])) $res['cpu']['bogomips'] = implode("<br />", $res['cpu']['bogomips']);
            }
        }

        // NETWORK
        // UPTIME
        $uptime_str = @file("/proc/uptime");
        if($uptime_str)
        {
            $uptime_str = explode(' ', implode("", $uptime_str));
            $uptime_str = trim($uptime_str[0]);
            $min = $uptime_str / 60;
            $hours = $min / 60;
            $days = floor($hours / 24);
            $hours = floor($hours - ($days * 24));
            $min = floor($min - ($days * 60 * 24) - ($hours * 60));
            if ($days !== 0) $res['uptime'] = $days."天";
            if ($hours !== 0) $res['uptime'] .= $hours."小时";
            $res['uptime'] .= $min."分钟";
        }

        // MEMORY
        $meminfo_str = @file("/proc/meminfo");
        if($meminfo_str)
        {
            $meminfo_str = implode("", $meminfo_str);
            preg_match_all("/MemTotal\s{0,}\:+\s{0,}([\d\.]+).+?MemFree\s{0,}\:+\s{0,}([\d\.]+).+?Cached\s{0,}\:+\s{0,}([\d\.]+).+?SwapTotal\s{0,}\:+\s{0,}([\d\.]+).+?SwapFree\s{0,}\:+\s{0,}([\d\.]+)/s", $meminfo_str, $buf);
            preg_match_all("/Buffers\s{0,}\:+\s{0,}([\d\.]+)/s", $meminfo_str, $buffers);
            $res['mem_total'] = round($buf[1][0]/1024, 2);
            $res['mem_free'] = round($buf[2][0]/1024, 2);
            $res['mem_buffers'] = round($buffers[1][0]/1024, 2);
            $res['mem_cached'] = round($buf[3][0]/1024, 2);
            $res['mem_used'] = $res['mem_total']-$res['mem_free'];
            $res['mem_percent'] = (floatval($res['mem_total'])!=0)?round($res['mem_used']/$res['mem_total']*100,2):0;
            $res['mem_real_used'] = $res['mem_total'] - $res['mem_free'] - $res['mem_cached'] - $res['mem_buffers']; //真实内存使用
            $res['mem_real_free'] = $res['mem_total'] - $res['mem_real_used']; //真实空闲
            $res['mem_real_percent'] = (floatval($res['mem_total'])!=0)?round($res['mem_real_used']/$res['mem_total']*100,2):0; //真实内存使用率
            $res['mem_cached_percent'] = (floatval($res['mem_cached'])!=0)?round($res['mem_cached']/$res['mem_total']*100,2):0; //Cached内存使用率
            $res['swap_total'] = round($buf[4][0]/1024, 2);
            $res['swap_free'] = round($buf[5][0]/1024, 2);
            $res['swap_used'] = round($res['swap_total']-$res['swap_free'], 2);
            $res['swap_percent'] = (floatval($res['swap_total'])!=0)?round($res['swap_used']/$res['swap_total']*100,2):0;    
        }

        // LOAD AVG
        $loadavg_str = @file("/proc/loadavg");
        if ($loadavg_str)
        {
            $loadavg_str = explode(' ', implode("", $loadavg_str));
            $loadavg_str = array_chunk($loadavg_str, 4);
            $res['load_avg'] = implode(' ', $loadavg_str[0]);
        }
        return $res;
    }

    /**
     * 获取操作系统硬件信息
     *
     * @return void
     */
    public static function getServerUsedStatus()
    {
        $fp = popen('top -b -n 2 | grep -E "^(Cpu|Mem|Tasks)"', "r");//获取某一时刻系统cpu和内存使用情况
        $rs = "";
        while(!feof($fp))
        {
            $rs .= fread($fp, 1024);
        }
        pclose($fp);
        $sys_info = explode("\n", $rs);
        $tast_info = explode(",", $sys_info[3]);//进程 数组
        $cpu_info = explode(",", $sys_info[4]);  //CPU占有量  数组
        $mem_info = explode(",", $sys_info[5]); //内存占有量 数组
    
        //正在运行的进程数
        $tast_running = trim(trim($tast_info[1], 'running'));
        //CPU占有量
        $cpu_usage = trim(trim($cpu_info[0], 'Cpu(s): '), '%us');  //百分比
    
        //内存占有量
        $mem_total = trim(trim($mem_info[0], 'Mem: '), 'k total'); 
        $mem_used = trim($mem_info[1], 'k used');
        $mem_usage = round(100 * intval($mem_used) / intval($mem_total), 2);  //百分比

        /*硬盘使用率 begin*/
        $fp = popen('df -lh | grep -E "^(/)"', "r");
        $rs = fread($fp, 1024);
        pclose($fp);
        $rs = preg_replace("/\s{2,}/", " ", $rs);  //把多个空格换成 “_”
        $hd = explode(" ", $rs);
        $hd_avail = trim($hd[3], 'G'); //磁盘可用空间大小 单位G
        $hd_usage = trim($hd[4], '%'); //挂载点 百分比
        //print_r($hd);
        /*硬盘使用率 end*/  
    
        //检测时间
        $fp = popen("date +\"%Y-%m-%d %H:%M\"", "r");
        $rs = fread($fp, 1024);
        pclose($fp);
        $detection_time = trim($rs);
    
        /*获取IP地址  begin*/
        $fp = popen('ifconfig em2 | grep -e "inet addr"', 'r');
        $rs = fread($fp, 1024);
        pclose($fp);
        $rs = preg_replace("/\s{2,}/", " ", trim($rs));  //把多个空格换成 “_”
        $rs = explode(" ", $rs);
        /*获取IP地址 end*/

        return  array(
            'cpu_usage' => $cpu_usage,
            'mem_usage' => $mem_usage,
            'hd_avail' => $hd_avail,
            'hd_usage' => $hd_usage,
            'tast_running' => $tast_running,
            'detection_time' => $detection_time,
            'ip_info' => $rs
        );
    }


    /**
     * 根据目标ID(int或者字符串) 进行分库分表处理 尽量通过hash打散，避免用户过于集中于某个库或某个表
     * 需要控制转换的16进制长度，目标转换的字符串长度不超过16位
     * @param int/string $target_columns  目标列(分表分库的业务列)
     * @param int $target_db_num   拆分库的数量
     * @param int $target_tab_num  拆分表的数量
     * @return void
     */
    public static function getDBTableSuffix($target_columns, $target_db_num, $target_tab_num)
    {
        $target_columns_md5 = md5($target_columns);
        $group_db_int = intval(substr($target_columns_md5, 0, 4) . substr($target_columns_md5, -4), 16);
        $target_db_suffix = $group_db_int % $target_db_num + 1;

        $group_tab_int = intval(substr($target_columns_md5, 0, 8) . substr($target_columns_md5, -7), 16);
        $target_tab_suffix = $group_tab_int % $target_tab_num + 1;
        return array(
            'db_suffix' => $target_db_suffix,
            'tab_suffix' => $target_tab_suffix
        );
    }

    /**
     * @desc  im:十进制数转换成三十六机制数
     * @param (int)$num 十进制数
     * return 返回：三十六进制数
    */
    public static function getChar($num) 
    {
        $num = intval($num);
        if ($num <= 0) return false;
        $charArr = array("0", "1", "2", "3", "4", "5", "6", "7", "8", "9", "A", 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J', 'K', 'L', 'M', 'N', 'O', 'P', 'Q', 'R', 'S', 'T', 'U', 'V', 'W', 'X', 'Y', 'Z');
        $char = '';
        do {
            $key = ($num - 1) % 36;
            $char = $charArr[$key] . $char;
            $num = floor(($num - $key) / 36);
        } while ($num > 0);
        return $char;
    }


    /**
     * @desc  im:十进制数转换成62机制数
     * @param (int)$num 十进制数
     * return 返回：62进制数
    */
    public static function getChar2($num)
    {
        $num = intval($num);
        if ($num <= 0) return false;
        $charArr = array("0", "1", "2", "3", "4", "5", "6", "7", "8", "9", "A", 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J', 'K', 'L', 'M', 'N', 'O', 'P', 'Q', 'R', 'S', 'T', 'U', 'V', 'W', 'X', 'Y', 'Z', "a", "b", "c", "d", "e", "f", "g", "h", "i", "j", "k", "l", "m", "n", "o", "p", "q", "r", "s", "t", "u", "v", "w", "x", "y", "z");
        $char = '';
        do {
            $key = ($num - 1) % 62;
            $char = $charArr[$key] . $char;
            $num = floor(($num - $key) / 62);
        } while ($num > 0);
        return $char;
    }


    /**
     * @desc  im:三十六进制数转换成十机制数
     * @param (string)$char 三十六进制数
     * return 返回：十进制数
     */
    public static function getNum($char)
    {
        $array = array("0", "1", "2", "3", "4", "5", "6", "7", "8", "9", "A", "B", "C", "D","E", "F", "G", "H", "I", "J", "K", "L","M", "N", "O","P", "Q", "R", "S", "T", "U", "V", "W", "X", "Y","Z");
        $len = strlen($char);
        $sum = 0;
        for($i = 0; $i < $len; $i++)
        {
            $index = array_search($char[$i], $array);
            $sum += ($index + 1) * pow(36, $len - $i - 1);
        }
        return $sum;
    }


    /**
     * @desc  im:62进制数转换成十机制数
     * @param (string)$char 62进制数
     * return 返回：十进制数
     */
    public static function getNum2($char)
    {
        $array = array("0", "1", "2", "3", "4", "5", "6", "7", "8", "9", "A", "B", "C", "D","E", "F", "G", "H", "I", "J", "K", "L","M", "N", "O","P", "Q", "R", "S", "T", "U", "V", "W", "X", "Y","Z", "a", "b", "c", "d", "e", "f", "g", "h", "i", "j", "k", "l", "m", "n", "o", "p", "q", "r", "s", "t", "u", "v", "w", "x", "y", "z");
        $len = strlen($char);
        $sum = 0;
        for($i = 0; $i < $len; $i++)
        {
            $index = array_search($char[$i], $array);
            $sum += ($index + 1) * pow(62, $len - $i - 1);
        }
        return $sum;
    }


    /**
     * 根据输入的url，采用crc32算法生成6位短码
     * 思路：采用 0~9 a~z A~Z 总计 62个字符进行组合生成短码，对于输入的url采用crc32计算出crc32值
     * 再将crc32值求余数，余数当成相应字符的unicode，再将相应的unicode按照一定规则转成相应的字符
     * 每次操作都按照相应的crc32值相除再取整数，往复操作知道取整为0退出该操作，返回最终生成的字符
     *
     * @param string $url 需要生成短码的url
     * @return array 加锁成功与否以及额外信息，返回的 参数列表如下所示：
     *   $short_url_data['origin_url']  type:string 输入的原始连接
     *   $short_url_data['short_url']  type:string 根据输入的连接生成的6位短码连接字符串
     */
    public function shortUrl($url)
    {
        $short_url_data = array(
            'short_url' => '', 
            'origin_url' => $url
        );
        $x = sprintf('%u', crc32($url));
        $str = '';
        $len = 0;
        // 按照0~1 a~z A~Z 总计 62个字符来处理，固定产生6位长度
        while($x > 0 && $len < 6)
        {
            // 将unicode数据对62求余，根据余数进行接下来的处理
            $s = $x % 62;
            if($s > 35)
            {
                // a~z ASCII码 为 97～122号为26个小写英文字母
                $s = chr($s + 61);
            }
            elseif($s > 9 && $s <= 35)
            {
                // A~Z ASCII码 为 65～90为26个大写英文字母
                $s = chr($s + 55);
            }
            else
            {
                // 0~9 此时不做 unicode chr 转换也可以  ASCII码 为 48～57为0到9十个阿拉伯数字
                $s = chr($s + 48);
            }
            $str .= $s;
            if($x < 62)
            {
                $x += random_int(62, 124);
            }
            $x = floor($x/62);
            $len++;
        }
        $short_url_data['short_url'] = $str;
        return $short_url_data;
    }

}
