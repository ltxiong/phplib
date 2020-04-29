<?php
namespace Ltxiong\PhpLib;

/**
 * @Copyright (C), 2015 ltxiong
 * @Name TraditionSimpleConvert.php
 * @Author ltxiong
 * @Version Beta 1.0
 * @Date: 2015-10-24 下午4:26:11
 * @Description 繁体<=>简体 互相转换类
 * @Class List
 * 1.
 * @Function List
 * 1.
 * @History
 * <author>   <time>                <version >    <desc>
 *  ltxiong  2015-10-24 下午4:26:11 Beta 1.0       第一次建立该文件
 */

/**
 *  繁体<=>简体 互相转换类
 * @package
 * @since : 2015-10-24 下午4:26:11
 * @final : 2015-10-24 下午4:26:11
 */
class TraditionSimpleConvert
{

    /**
     * 简体中文字符串
     * @var string
     */
    public $simplified_str = "";

    /**
     * 繁体中文字符串
     * @var string
     */
    public $traditional_str = "";

    /**
     * 初始化数据
     *
     * @param $simplified_arr  简体中文字符串
     * @param $traditional_arr 繁体中文字符串
     */
    public function __construct($simplified_arr, $traditional_arr)
    {

        $this->simplified_str = implode('', $simplified_arr);
        $this->traditional_str = implode('', $traditional_arr);
    }

    /**
     * 繁体字转换成简体字
     *
     * @param string $sContent 需要处理的字符串
     *
     * @return string
     */
    public function tradition2Simple($sContent)
    {

        $simpleCN = '';
        $iContent = mb_strlen($sContent, 'UTF-8');
        for ($i = 0; $i < $iContent; $i++)
        {
            $str = mb_substr($sContent, $i, 1, 'UTF-8');
            $match = mb_strpos($this->traditional_str, $str, null, 'UTF-8');
            $simpleCN .= ($match !== false) ? mb_substr($this->simplified_str, $match, 1, 'UTF-8') : $str;
        }

        return $simpleCN;
    }

    /**
     * 简体字转换成繁体字
     *
     * @param string $sContent 需要处理的字符串
     *
     * @return string
     */
    public function simple2Tradition($sContent)
    {

        $traditionalCN = '';
        $iContent = mb_strlen($sContent, 'UTF-8');
        for ($i = 0; $i < $iContent; $i++)
        {
            $str = mb_substr($sContent, $i, 1, 'UTF-8');
            $match = mb_strpos($this->simplified_str, $str, null, 'UTF-8');
            $traditionalCN .= ($match !== false) ? mb_substr($this->traditional_str, $match, 1, 'UTF-8') : $str;
        }

        return $traditionalCN;
    }
}
//
////应用例子:
//$ts = new TraditionSimpleConvert($simplified_arr, $traditional_arr);
////繁体转简体：
//echo $ts->tradition2Simple('醜ab罷皚敗頒辦絆的人-好的搖堯遙窯人謠') . "\n";
////简体转繁体：
//echo $ts->simple2Tradition('丑ab罢皑败颁办绊的人-好的摇尧遥窑人谣') . "\n";
////简体转繁体：
//echo $ts->simple2Tradition('刘强东连呛阿里巴巴：“网上管理假货其实非常容易”') . "\n";
