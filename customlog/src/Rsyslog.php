<?php

namespace Ltxiong\CustomLog;

/**
 * @desc 远程写 rsyslog 日志类
 * @example
 */
class Rsyslog
{
    
    /**
     * @desc 日志服务器地址
     * Syslog destination server
     * @var string
     * @access private
     */
    private $_host;

    /**
     * @desc 日志服务器 端口
     * Standard syslog port is 514
     * @var int
     * @access private
     */
    private $_port;

    /**
     * @desc 链接超时时间
     * @var int
     * @access private
     */
    private $_timeout = 1;

    /**
     * 数据存储是以“字节”(Byte)为单位，数据传输大多是以“位”(bit，又名“比特”)为单位，一个位就代表一个0或1
     * 消息默认长度(字节数) -- 每8个位(bit，简写为b)组成一个字节(Byte，简写为B)，是最小一级的信息单位。
     * 1024字节也就是1kb
     * 1KiB（Kibibyte）=1024byte (二进制) 1KB（Kilobyte）=1000byte  (十进制)
     * 
     */
    private $_msg_default_len = 1024;

    /**
     * 消息最大长度(字节数 1024 * 3)
     */
    private $_msg_max_len = 3072;

	/**
	 * 
	 * 日志的标示ID列表
	 * @var array
	 * @access protected
	 * 
	 */
	private $_log_id_arr;

    /**
     * 套接字传输器/传输协议
     */
    private $_socket_transports = array(
        'tcp', 
        'udp', 
        'ssl', 
        'sslv2', 
        'sslv3', 
        'tls'
    );

    /**
     * @desc  构造函数 可输入策略数组，定义内容
     * @access public
     * @param void
     * @return void
     */
    public function __construct($host, $port, $log_id_arr)
    {
        $this->_host = $host;
        $this->_port = $port;
		$this->_log_id_arr = $log_id_arr;
    }

    /**
     * 发送消息之前，进行消息前置处理，包括消息体长度处理
     */
    private function ProcessMessage($message, $msg_prefix, $msg_len = 1024)
    {
        if(empty($message) || !is_string($message))
        {
            return '';
        }
        $msg_len = intval($msg_len);
        $msg_len = $msg_len > $this->_msg_max_len ? $this->_msg_max_len : ($msg_len < 0 ? $this->_msg_default_len : $msg_len);
		// 毫秒时间戳
        $log_send_time = intval(microtime(true) * 1000);
        // 消息最前面带空格，要么消息最前面带上<>开头和结尾的特殊字符，
        // 例如： <190>Apr BLOG_LTX3_KKK 1xxxxxxxxxx 22020/04/20/11:32:43，实际消息内容为 1xxxxxxxxxx 22020/04/20/11:32:43
        $message = substr("<190>" . date("F j Y g:i a") . " $msg_prefix $log_send_time $message", 0, $msg_len);
        return $message;
    }

    /**
     * 发送消息之前，进行消息前缀处理
     */
    private function GetMessagePrefix($log_id)
    {
		if (!isset($this->_log_id_arr[$log_id]))
		{
			return '';
		}
        return $this->_log_id_arr[$log_id];
    }

    /**
     * 发送消息到远程rsyslog 服务器
     */
    public function Send($message, $log_id, $msg_len = 1024, $socket_transport = 'tcp')
    {
		if (empty($message))
		{
			return false;
        }
        $socket_transport = strtolower($socket_transport);
        if(!in_array($socket_transport, $this->_socket_transports))
        {
            return false;
        }
        // 消息内容前缀
        $msg_prefix = $this->GetMessagePrefix($log_id);
        if(empty($msg_prefix))
        {
            return false;
        }
        // 对消息内容进行处理
        $message = $this->ProcessMessage($message, $msg_prefix, $msg_len);

        $errno = "";
        $errstr = "";
        // 打开套接字描述符 
        $fp = fsockopen("$socket_transport://" . $this->_host, $this->_port, $errno, $errstr, $this->_timeout);
        if ($fp) {
            // 发送消息
            fwrite($fp, $message);
            // 关闭连接
            fclose($fp);
            return true;
        }
        return false;
    }

}
