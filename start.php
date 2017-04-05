<?php
/**
 * Created by PhpStorm.
 * User: qing
 * Date: 17-2-12
 * Time: 下午1:59
 */
require_once "simple_html_dom.php";

$cookie = "JSESSIONID={$argv[1]}";
$result = "消费时间,消费金额,卡内余额,消费地点\r\n";

$page = 1;

while (true){
    echo "Fetching page $page...\n";
    $html = get_url("http://app.scnu.edu.cn/ecard/consump.html?page=$page", $cookie);
    //echo $html;exit();
    if (!$html)
        exit("Network Error!!");
    if (strpos($html, "暂无数据") !== false)
        break;
    $dom = new simple_html_dom();
    $dom->load($html);
    $trs = $dom->find("table", 0)->find("tr");
    foreach ($trs as $tr){
        $arr = [];
        $tds = $tr->find("td");
        //忽略表头
        if (!$tds)
            continue;
        foreach ($tds as $td){
            //过滤结果中的标签
            $arr[] = strip_tags($td->innertext);
        }
        $result .= implode(",", $arr) . "\r\n";
    }
    $page++;
}

file_put_contents("result.csv", $result);

echo "Finished\n";

function get_url($url, $cookie=''){
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_HTTPHEADER, array('User-Agent: Mozilla/5.0 (Linux; U; Android 4.1.2; zh-cn; MB526 Build/JZO54K) AppleWebKit/530.17 (KHTML, like Gecko) FlyFlow/2.4 Version/4.0 Mobile Safari/530.17 baidubrowser/042_1.8.4.2_diordna_458_084/alorotoM_61_2.1.4_625BM/1200a/39668C8F77034455D4DED02169F3F7C7%7C132773740707453/1','Referer: http://app.scnu.edu.cn/ecard/consump.html'));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_COOKIE, $cookie);
    $result = curl_exec($ch);
    $httpCode = curl_getinfo($ch,CURLINFO_HTTP_CODE);
    if ($httpCode != 200) return false;
    curl_close($ch);
    return $result;
}
