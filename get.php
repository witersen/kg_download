<?php
/*
 * @Author: witersen
 * @Date: 2021-09-24 22:15:06
 * @LastEditors: witersen
 * @LastEditTime: 2021-09-24 22:45:03
 * @Description: QQ:1801168257
 */

define('BASE_PATH', __DIR__);

//保存链接的文本地址
$url_file = BASE_PATH . "/url.txt";

//读取链接到数组
$file = fopen($url_file, "r") or exit("无法打开文件!");
$file_content = array();
while (!feof($file)) {
    $line = trim(fgets($file));
    if ($line != "") {
        array_push($file_content, $line);
    }
}
fclose($file);

//处理
foreach ($file_content as $key => $value) {
    //抓取页面
    $page_content = curl_request($value, true);
    //匹配信息
    // preg_match_all("/\"playurl\":\"(.*).m4a(.*)\"/", $page_content, $preg_array);
    // preg_match_all("/\"playurl\":\".*.m4a.*\",\"playurl_video/", $page_content, $preg_array);
    preg_match("/playurl\":\"(.*?)\"/", $page_content, $preg_array);
    //媒体地址
    $m4a_url = $preg_array[1];
    //获取媒体
    $m4a_content = curl_request($m4a_url, true);
    //拼接文件地址
    $m4a_file = $key . ".m4a";
    //保存
    file_put_contents(BASE_PATH . "/" . $m4a_file, $m4a_content);
}

/**
 * 请求
 */
function curl_request($url, $is_set_header = false)
{
    //初始化
    $curl = curl_init();

    //设置请求url
    curl_setopt($curl, CURLOPT_URL, $url);

    //设置true会将头文件的信息作为数据流输出 否则作为字符串输出
    curl_setopt($curl, CURLOPT_HEADER, false);

    //设置true会不输出body部分 此时请求类型被转变为head请求
    curl_setopt($curl, CURLOPT_NOBODY, false);

    //设置请求头数组内容 默认不设置
    $is_set_header ? curl_setopt($curl, CURLOPT_HTTPHEADER, get_forge_header()) : "";

    //设置true会将curl_exec()获取的信息以字符串返回 否则会直接输出
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);

    //设置true会在页面发生301或者302时自动进行跳转抓取
    curl_setopt($curl, CURLOPT_FOLLOWLOCATION, true);

    //将请求类型改为get 如果为探测状态 可避免因为请求类型为head造成的探测失误
    curl_setopt($curl, CURLOPT_CUSTOMREQUEST, "GET");

    //设置请求超时时间
    curl_setopt($curl, CURLOPT_TIMEOUT, 20);

    //设置false将不检查证书
    curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);

    //设置false将不检查证书
    curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);

    //执行
    $preg_array = curl_exec($curl);

    //关闭
    curl_close($curl);

    return $preg_array;
}

/**
 * 获取模拟http请求头的内容
 */
function get_forge_header()
{
    //构造随机ip
    $ip_long = array(
        array('607649792', '608174079'), //36.56.0.0-36.63.255.255
        array('1038614528', '1039007743'), //61.232.0.0-61.237.255.255
        array('1783627776', '1784676351'), //106.80.0.0-106.95.255.255
        array('2035023872', '2035154943'), //121.76.0.0-121.77.255.255
        array('2078801920', '2079064063'), //123.232.0.0-123.235.255.255
        array('-1950089216', '-1948778497'), //139.196.0.0-139.215.255.255
        array('-1425539072', '-1425014785'), //171.8.0.0-171.15.255.255
        array('-1236271104', '-1235419137'), //182.80.0.0-182.92.255.255
        array('-770113536', '-768606209'), //210.25.0.0-210.47.255.255
        array('-569376768', '-564133889'), //222.16.0.0-222.95.255.255
    );

    //获取随机数字
    $rand_key = mt_rand(0, 9);

    //将长整型转换为IP地址
    $ip = long2ip(mt_rand($ip_long[$rand_key][0], $ip_long[$rand_key][1]));

    //模拟http请求header头
    $header = array(
        "Connection: Keep-Alive",
        "Accept: text/html, application/xhtml+xml, */*",
        "Pragma: no-cache",
        "Accept-Language: zh-Hans-CN,zh-Hans;q=0.8,en-US;q=0.5,en;q=0.3",
        "User-Agent: Mozilla/5.0 (compatible; MSIE 10.0; Windows NT 6.2; WOW64; Trident/6.0)",
        'CLIENT-IP:' . $ip,
        'X-FORWARDED-FOR:' . $ip
    );

    return $header;
}
