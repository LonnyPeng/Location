<?php

/*
 "浙江省", "安徽省", "福建省", "江西省", "山东省", "河南省", "湖北省", "湖南省", "广东省", "广西壮族自治区", "海南省", "重庆市", "四川省", "贵州省", "云南省", "西藏自治区", "陕西省", "甘肃省", "青海省", "宁夏回族自治区", "新疆维吾尔自治区",
*/
$alreadyData = array(
    "辽宁省", "吉林省", "黑龙江省", "上海市", "江苏省", "浙江省", "安徽省", "福建省", "江西省", "山东省", "河南省", "湖北省", "湖南省", "广东省", "广西壮族自治区", "海南省", "重庆市", "四川省", "贵州省", "云南省", "西藏自治区", "陕西省", "甘肃省", "青海省", "宁夏回族自治区", "新疆维吾尔自治区", 
);
$pdo = linkPdo(array('dbName' => 'db_main', 'root' => 'root', 'password' => 'root'));

$sql = "SELECT toponymy_id, toponymy_parent_id FROM t_toponymy WHERE toponymy_order = ''";
$result = $pdo->query($sql);
$data = $result->fetchAll();
if ($data) {
    foreach ($data as $row) {
        $orderArr = array();
        for ($i=0;;$i++) {
            if ($row->toponymy_parent_id) {
                $orderArr[] = $row->toponymy_parent_id;
            }
            $sql = "SELECT toponymy_parent_id FROM t_toponymy WHERE toponymy_id = %d";
            $result = $pdo->query(sprintf($sql, $row->toponymy_parent_id));
            $id = $result->fetch();
            if (!$id) {
                break;
            }
            $row->toponymy_parent_id = $id->toponymy_parent_id;
        }
        $sql = "UPDATE t_toponymy SET toponymy_order = '%s' WHERE toponymy_id = %d";
        $pdo->exec(sprintf($sql, implode(",", $orderArr), $row->toponymy_id));
    }
}

$data_0 = array();
$urlInfo = array('url' => "http://www.stats.gov.cn/tjsj/tjbz/tjyqhdmhcxhfdm/2015/index.html");
$html = curl($urlInfo);
$html = @iconv("GBK", "UTF-8", $html);
$preg = array(
    'key' => "/<a[\w\s=\"]*href[\s]*=[\s]*[\"|']{1}([\d|\/]{1,}.*?)[\"|']{1}[^>]*>/i",
    'name' => "/<a.*?\.html.*?>([^\d]*?)<.*?>/i",
    'name_01' => "/<tr class='villagetr'><td>[\d]{1,}<\/td><td>[\d]{1,}<\/td><td>(.*?)<\/td><\/tr>/i",
);
$link = array_values(array_filter(pregHtml($html, $preg['key'])));
$name = array_values(array_filter(pregHtml($html, $preg['name'])));
foreach ($link as $key => $value) {
    $data_0[$name[$key]] = array('url' => "http://www.stats.gov.cn/tjsj/tjbz/tjyqhdmhcxhfdm/2015/" . $value);
}

$orderArr = array(18, 11);
$data_0 = curlMulti($data_0);
foreach ($data_0 as $key => $value) {
    if (!in_array($key, $alreadyData)) {
        continue;
    }
    $parentId0 = 18;
    $sql = "SELECT toponymy_id FROM t_toponymy WHERE toponymy_name = '%s' AND toponymy_status = 1 AND toponymy_order = '%s'";
    $result = $pdo->query(sprintf($sql, $key, implode(",", $orderArr)));
    $id0 = $result->fetch();
    if (!$id0) {
        $sql = "INSERT INTO t_toponymy SET toponymy_name = '%s', toponymy_parent_id = %d, toponymy_order = '%s'";
        $pdo->exec(sprintf($sql, $key, $parentId0, implode(",", $orderArr)));
        $id0 = $pdo->lastInsertId();
    } else {
        $id0 = $id0->toponymy_id;
    }
    $orderArr_0 = array_merge(array($id0), $orderArr);
    
    $data_1 = array();
    $html = @iconv("GBK", "UTF-8", $value);

    $link = array_values(array_filter(pregHtml($html, $preg['key'])));
    $name = array_values(array_filter(pregHtml($html, $preg['name'])));
    foreach ($link as $key0 => $value) {
        $data_1[$name[$key0]] = array('url' => "http://www.stats.gov.cn/tjsj/tjbz/tjyqhdmhcxhfdm/2015/" . $value);
    }

    $data_1 = curlMulti($data_1);
    foreach ($data_1 as $ke => $val) {
        $parentId1 = $id0;
        if (!in_array($ke, array("市辖区", "县", "省直辖县级行政区划", " 自治区直辖县级行政区划"))) {
            $sql = "SELECT toponymy_id FROM t_toponymy WHERE toponymy_name = '%s' AND toponymy_status = 1 AND toponymy_order = '%s'";
            $result = $pdo->query(sprintf($sql, $ke, implode(",", $orderArr_0)));
            $id1 = $result->fetch();
            if (!$id1) {
                $sql = "INSERT INTO t_toponymy SET toponymy_name = '%s', toponymy_parent_id = %d, toponymy_order = '%s'";
                $pdo->exec(sprintf($sql, $ke, $parentId1, implode(",", $orderArr_0)));
                $id1 = $pdo->lastInsertId();
            } else {
                $id1 = $id1->toponymy_id;
            }
            $orderArr_1 = array_merge(array($id1), $orderArr_0);
        } else {
            $id1 = $id0;
            $orderArr_1 = $orderArr_0;
        }

        $data_2 = array();
        $html = @iconv("GBK", "UTF-8", $val);

        $link = array_values(array_filter(pregHtml($html, $preg['key'])));
        $name = array_values(array_filter(pregHtml($html, $preg['name'])));
        foreach ($link as $ke1 => $value) {
            $num = explode("/", $value)[1];
            $data_2[$name[$ke1]] = array('url' => "http://www.stats.gov.cn/tjsj/tjbz/tjyqhdmhcxhfdm/2015/" . substr($num, 0, 2) . "/" . $value);
        }

        $data_2 = curlMulti($data_2);
        foreach ($data_2 as $k => $v) {
            $parentId2 = $id1;
            $sql = "SELECT toponymy_id FROM t_toponymy WHERE toponymy_name = '%s' AND toponymy_status = 1 AND toponymy_order = '%s'";
            $result = $pdo->query(sprintf($sql, $k, implode(",", $orderArr_1)));
            $id2 = $result->fetch();
            if (!$id2) {
                $sql = "INSERT INTO t_toponymy SET toponymy_name = '%s', toponymy_parent_id = %d, toponymy_order = '%s'";
                $pdo->exec(sprintf($sql, $k, $parentId2, implode(",", $orderArr_1)));
                $id2 = $pdo->lastInsertId();
            } else {
                $id2 = $id2->toponymy_id;
            }
            $orderArr_2 = array_merge(array($id2), $orderArr_1);

            $data_3 = array();
            $html = @iconv("GBK", "UTF-8", $v);

            $link = array_values(array_filter(pregHtml($html, $preg['key'])));
            $name = array_values(array_filter(pregHtml($html, $preg['name'])));
            foreach ($link as $k2 => $value) {
                $num = explode("/", $value)[1];
                $data_3[$name[$k2]] = array('url' => "http://www.stats.gov.cn/tjsj/tjbz/tjyqhdmhcxhfdm/2015/" . substr($num, 0, 2) . "/" . substr($num, 2, 2) . "/" . $value);
            }

            $data_3 = curlMulti($data_3);
            foreach ($data_3 as $k3 => $v3) {
                $parentId3 = $id2;
                $sql = "SELECT toponymy_id FROM t_toponymy WHERE toponymy_name = '%s' AND toponymy_status = 1 AND toponymy_order = '%s'";
                $result = $pdo->query(sprintf($sql, $k3, implode(",", $orderArr_2)));
                $id3 = $result->fetch();
                if (!$id3) {
                    $sql = "INSERT INTO t_toponymy SET toponymy_name = '%s', toponymy_parent_id = %d, toponymy_order = '%s'";
                    $pdo->exec(sprintf($sql, $k3, $parentId3, implode(",", $orderArr_2)));
                    $id3 = $pdo->lastInsertId();
                } else {
                    $id3 = $id3->toponymy_id;
                }
                $orderArr_3 = array_merge(array($id3), $orderArr_2);

                $data_3 = array();
                $html = @iconv("GBK", "UTF-8", $v3);

                $name = pregHtml($html, $preg['name_01']);
                if (!$name) {
                    continue;
                }
                foreach ($name as $v4) {
                    echo $v4 . ";";
                    $parentId4 = $id3;
                    $sql = "SELECT toponymy_id FROM t_toponymy WHERE toponymy_name = '%s' AND toponymy_status = 1 AND toponymy_order = '%s'";
                    $result = $pdo->query(sprintf($sql, $v4, implode(",", $orderArr_3)));
                    $id4 = $result->fetch();
                    if (!$id4) {
                        $sql = "INSERT INTO t_toponymy SET toponymy_name = '%s', toponymy_parent_id = %d, toponymy_order = '%s'";
                        $pdo->exec(sprintf($sql, $v4, $parentId4, implode(",", $orderArr_3)));
                    }
                }
            }
        }
    } 
}

echo "Lonny";

/**
 * Link MYSQL
 *
 * @param array array('host' => '127.0.0.1', 'port' => 3306, 'dbName' => 'mysql', 'root' => 'root', 'password' => '')
 * @return array
 */
function linkPdo($info = array('host' => '127.0.0.1', 'port' => 3306, 'dbName' => 'mysql', 'root' => 'root', 'password' => ''))
{
    if (!isset($info['host'])) {
        $info['host'] = '127.0.0.1';
    }
    if (!isset($info['port'])) {
        $info['port'] = 3306;
    }
    if (!isset($info['dbName'])) {
        $info['dbName'] = 'mysql';
    }
    if (!isset($info['root'])) {
        $info['root'] = 'root';
    }
    if (!isset($info['password'])) {
        return false;
    }

    $options = array(
        PDO::ATTR_PERSISTENT => true,
        PDO::MYSQL_ATTR_FOUND_ROWS => true,
    );

    $pdo = new PDO("mysql:host={$info['host']}:{$info['port']};dbname={$info['dbName']}", $info['root'], $info['password'], $options);
    
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_OBJ);

    return $pdo;
}

/**
 * Use the curl virtual browser
 *
 * @param array $urlInfo = array('url' => "https://www.baidu.com/", 'params' => array('key' => 'test'), 'cookie' => 'cookie')
 * @param string $type = 'GET|POST'
 * @param boolean $info = false|true
 * @return string|array
 */
function curl($urlInfo, $type = "GET", $info = false) {
    $type = strtoupper(trim($type));

    if (isset($urlInfo['cookie'])) {
        $cookie = $urlInfo['cookie'];
        unset($urlInfo['cookie']);
    }

    if ($type == "POST") {
        $url = $urlInfo['url'];
        $data = $urlInfo['params'];
    } else {
        $urlArr = parse_url($urlInfo['url']);

        if (isset($urlInfo['params'])) {
            $params = "";
            foreach ($urlInfo['params'] as $key => $row) {
                if (is_array($row)) {
                    foreach ($row as $value) {
                        if ($params) {
                            $params .= "&" . $key . "=" . $value;
                        } else {
                            $params .= $key . "=" . $value;
                        }
                    }
                } else {
                    if ($params) {
                        $params .= "&" . $key . "=" . $row;
                    } else {
                        $params .= $key . "=" . $row;
                    }
                }
            }

            if (isset($urlArr['query'])) {
                if (preg_match("/&$/", $urlArr['query'])) {
                    $urlArr['query'] .= $params;
                } else {
                    $urlArr['query'] .= "&" . $params;
                }
            } else {
                $urlArr['query'] = $params;
            }
        }

        if (isset($urlArr['host'])) {
            if (isset($urlArr['scheme'])) {
                $url = $urlArr['scheme'] . "://" . $urlArr['host'];
            } else {
                $url = $urlArr['host'];
            }

            if (isset($urlArr['port'])) {
                $url .= ":" . $urlArr['port'];
            }
            if (isset($urlArr['path'])) {
                $url .= $urlArr['path'];
            }
            if (isset($urlArr['query'])) {
                $url .= "?" . $urlArr['query'];
            }
            if (isset($urlArr['fragment'])) {
                $url .= "#" . $urlArr['fragment'];
            }
        } else {
            $url = $urlInfo['url'];
        }
    }
    
    $httpHead = array(
        "Accept:text/html,application/xhtml+xml,application/xml;q=0.9,image/webp,*/*;q=0.8",
        "Cache-Control:no-cache",
        "Connection:keep-alive",
        "Pragma:no-cache",
        "Upgrade-Insecure-Requests:1",
    );
    
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    if (isset($cookie)) {
        curl_setopt($ch, CURLOPT_COOKIE , $cookie);
    }
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $httpHead);
    curl_setopt($ch, CURLOPT_ENCODING , "gzip");
    if ($type == "POST") {
        curl_setopt($ch, CURLOPT_POST, 1);
        @curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
    } else {
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'GET');
    }
    curl_setopt($ch, CURLOPT_USERAGENT, "Mozilla/5.0 (Windows NT 6.1; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/49.0.2623.112 Safari/537.36");
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
    curl_setopt($ch, CURLOPT_MAXREDIRS, 5);
    curl_setopt($ch, CURLOPT_HEADER, 0);
    curl_setopt($ch, CURLOPT_NOBODY, 0);
    $result = curl_exec($ch);
    $curlInfo = curl_getinfo($ch);
    curl_close($ch); 
    
    if ($info) {
        return $curlInfo;
    } else {
        return $result;
    }
}

/**
 * Use the curl multi virtual browser
 *
 * @param array $urlInfos = array(
 *     array('url' => "https://www.baidu.com/", 'params' => array('key' => 'test'), 'cookie' => 'cookie', 'type' => 'GET'),
 *     array('url' => "https://www.google.com/", 'params' => array('key' => 'test'), 'cookie' => 'cookie', 'type' => 'POST'),
 * )
 * @param string $type = 'GET|POST'
 * @param boolean $info = false|true
 * @return string|array
 */
function curlMulti($urlInfos = array()) {
    $curlArray = $data =  array();
    $httpHead = array(
        "Accept:text/html,application/xhtml+xml,application/xml;q=0.9,image/webp,*/*;q=0.8",
        "Cache-Control:no-cache",
        "Connection:keep-alive",
        "Pragma:no-cache",
        "Upgrade-Insecure-Requests:1",
    );
    $mh = curl_multi_init();

    foreach($urlInfos as $key => $urlInfo) {
        if (isset($urlInfo['type'])) {
            $type = strtoupper(trim($urlInfo['type']));
            unset($urlInfo['type']);
        } else {
            $type = 'GET';
        }

        if (isset($urlInfo['cookie'])) {
            $cookie = $urlInfo['cookie'];
            unset($urlInfo['cookie']);
        }

        if ($type == "POST") {
            $url = $urlInfo['url'];
            $data = $urlInfo['params'];
        } else {
            $urlArr = parse_url($urlInfo['url']);

            if (isset($urlInfo['params'])) {
                $params = "";
                foreach ($urlInfo['params'] as $ke => $row) {
                    if (is_array($row)) {
                        foreach ($row as $value) {
                            if ($params) {
                                $params .= "&" . $ke . "=" . $value;
                            } else {
                                $params .= $ke . "=" . $value;
                            }
                        }
                    } else {
                        if ($params) {
                            $params .= "&" . $ke . "=" . $row;
                        } else {
                            $params .= $ke . "=" . $row;
                        }
                    }
                }

                if (isset($urlArr['query'])) {
                    if (preg_match("/&$/", $urlArr['query'])) {
                        $urlArr['query'] .= $params;
                    } else {
                        $urlArr['query'] .= "&" . $params;
                    }
                } else {
                    $urlArr['query'] = $params;
                }
            }

            if (isset($urlArr['host'])) {
                if (isset($urlArr['scheme'])) {
                    $url = $urlArr['scheme'] . "://" . $urlArr['host'];
                } else {
                    $url = $urlArr['host'];
                }

                if (isset($urlArr['port'])) {
                    $url .= ":" . $urlArr['port'];
                }
                if (isset($urlArr['path'])) {
                    $url .= $urlArr['path'];
                }
                if (isset($urlArr['query'])) {
                    $url .= "?" . $urlArr['query'];
                }
                if (isset($urlArr['fragment'])) {
                    $url .= "#" . $urlArr['fragment'];
                }
            } else {
                $url = $urlInfo['url'];
            }
        }

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        if (isset($cookie)) {
            curl_setopt($ch, CURLOPT_COOKIE , $cookie);
        }
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $httpHead);
        curl_setopt($ch, CURLOPT_ENCODING , "gzip");
        if ($type == "POST") {
            curl_setopt($ch, CURLOPT_POST, 1);
            @curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        } else {
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'GET');
        }
        curl_setopt($ch, CURLOPT_USERAGENT, "Mozilla/5.0 (Windows NT 6.1; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/49.0.2623.112 Safari/537.36");
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
        curl_setopt($ch, CURLOPT_MAXREDIRS, 5);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_NOBODY, 0);

        $curlArray[$key] = $ch;
        curl_multi_add_handle($mh, $curlArray[$key]);
    }

    $running = 0;
    do {
        usleep(10000);
        curl_multi_exec($mh, $running);
    } while($running > 0);

    foreach($urlInfos as $key => $urlInfo) {
        $data[$key] = curl_multi_getcontent($curlArray[$key]);
        curl_multi_remove_handle($mh, $curlArray[$key]);
    }
    curl_multi_close($mh);

    return $data;
}

/**
 * Regular match HTML
 *
 * @param string $html = ""
 * @param string $preg = ""
 * @param boolean $status = true|false
 * @return string
 */
function pregHtml($html = "", $preg = "", $status = true)
{
    $pregInit = array(
        'clear' => "/\f|\n|\r|\t|\v/",
        'spaces' => "/[ ]{2,}/",
        'css' => "/<style[^>]*>.+?<\/style>/i",
        'js' => "/<script[^>]*>.+?<\/script>/i",
        'nojs' => "/<noscript[^>]*>.+?<\/noscript>/i",
        'notes' => "/<!.*?>/",
    );

    //init
    $html = trim($html);
    foreach ($pregInit as $key => $value) {
        switch ($key) {
            case 'clear':
                $html = preg_replace($value, "", $html);
                break;
            case 'spaces':
                $html = preg_replace($value, " ", $html);
                break;
            default:
                if ($status) {
                    $src = pregHtml($html, $value, false);
                    if (is_array($src)) {
                        foreach ($src as $val) {
                            $html = str_replace($val, "", $html);
                        }
                    } else {
                        $html = str_replace($src, "", $html);
                    }
                }
                break;
        }
    }

    if (!$preg) {
        return $html;
    }

    //action
    preg_match_all($preg, $html, $pregArr);

    if (isset($pregArr[1])) {
        if (count($pregArr[1]) == 1) {
            $pregArr = $pregArr[1][0];
        } else {
            $pregArr = $pregArr[1];
        }
    } else {
        if (count($pregArr[0]) == 1) {
            $pregArr = $pregArr[0][0];
        } else {
            $pregArr = $pregArr[0];
        }
    }

    return is_array($pregArr) ? array_unique($pregArr) : array($pregArr);
}