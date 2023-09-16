<?php
    date_default_timezone_set('Asia/Shanghai');
    
    $logfile  = 'log.txt';
    if (file_exists($logfile)){
        unlink($logfile);
    }
    update_iptv('iptv2_part2.m3u', 'http://mmitv.top/igmp/multicast.php?id=list&ext=list', $logfile);
    update_iptv('fanmingming.m3u', 'https://live.fanmingming.com/tv/m3u/global.m3u', $logfile);
    
    function update_iptv($m3u_file, $url, $logfile){
        try{
            $content = "Info: ";
            $content .= date('Y-m-d H:i:s');
            $content .= ": 开始获取“".$m3u_file."”中对应的原始频道列表...\n";
            file_put_contents($logfile, $content, FILE_APPEND);
            $channellist = file_get_contents($url);
            $channellist = str_replace('<br>', "\n", $channellist);
            $channellist_file = substr($m3u_file, 0, strripos($m3u_file, '.')).'.txt';
            $channellist_file_suffix = substr($url, strripos($url, '.') + 1);
            $myfile = fopen($channellist_file, 'w') or die('无法打开文件！');
            fwrite($myfile, $channellist);
            fclose($myfile);
            $content = "Info: ";
            $content .= date('Y-m-d H:i:s');
            $content .= ": 成功获取“".$m3u_file."”中对应的原始频道列表！\n";
            file_put_contents($logfile, $content, FILE_APPEND);
            $pattern1 = '/\-|[HD].*|（.*/i';
            $pattern2 = '/[HD].*|（.*/i';
            $pattern3 = '/^.*/i';
            $shouldreplacecount = 0;
            $replacecount = 0;
            $content = "Info: ";
            $content .= date('Y-m-d H:i:s');
            $content .= ": 开始替换“".$m3u_file."”中的频道播放地址...\n";
            file_put_contents($logfile, $content, FILE_APPEND);
            $channelarry = array();
            if (file_exists($m3u_file)){
                $m3u_file_arr = file($m3u_file);
                $channellist_file_arr = file($channellist_file);
                //逐行读取文件内容
                for($i = 0; $i < count($m3u_file_arr); $i++){
                    if(strpos($m3u_file_arr[$i], '#EXTINF') !== false){
                        $shouldreplacecount++;
                        $m3u_channelname = trim(substr($m3u_file_arr[$i], strripos($m3u_file_arr[$i], ',') + 1));
                        $m3u_channelname1 = preg_replace($pattern1, '', $m3u_channelname);
                        $m3u_channelname2 = preg_replace($pattern2, '', $m3u_channelname);
                        if($m3u_channelname1 == '爱上4K'){
                            $m3u_channelname1 = '4K测试';
                        }else if($m3u_channelname1 == '北京IPTV4K'){
                            $m3u_channelname1 = '4K超清';
                        }else if($m3u_channelname1 == '华数4K影视'){
                            $m3u_channelname1 = '4K乐享';
                        }else if($m3u_channelname1 == '杭州影视'){
                            $m3u_channelname1 = '杭州4影视';
                        }else if($m3u_channelname1 == '湖南爱晚'){
                            $m3u_channelname1 = '湖南公共';
                        }else if($m3u_channelname1 == 'CCTV16.4K'){
                            $m3u_channelname1 = 'CCTV-16 4K';
                        }else if($m3u_channelname1 == 'CCTV4K'){
                            $m3u_channelname1 = 'CCTV-4K 超高清';
                        }else if($m3u_channelname1 == '秒看天下'){
                            $m3u_channelname1 = '看天下精选';
                        }
                        if($m3u_channelname1 == $m3u_channelname2){
                            array_push($channelarry, $m3u_channelname1);
                        }else{
                            array_push($channelarry, $m3u_channelname1, $m3u_channelname2);
                        }
                        for($j = 0; $j < count($channellist_file_arr); $j++){
                            $channellist_channelname = '';
                            $channellist_channelurl = '';
                            if($channellist_file_suffix === 'm3u'){
                                if(strpos($channellist_file_arr[$j], '#EXTINF') !== false){
                                    $channellist_channelname = trim(substr($channellist_file_arr[$j], strripos($channellist_file_arr[$j], ',') + 1));
                                    $channellist_channelurl = $channellist_file_arr[$j + 1];
                                }
                            }else{
                                 if(strpos($channellist_file_arr[$j], 'http') !== false){
                                     $channellist_channelname = trim(substr($channellist_file_arr[$j], 0, strripos($channellist_file_arr[$j], ',')));
                                     $channellist_channelurl = trim(substr($channellist_file_arr[$j], strripos($channellist_file_arr[$j], ',') + 1));
                                 }
                            }
                            if($channellist_channelname !== ''){
                                if($channellist_channelname == $m3u_channelname1 || $channellist_channelname == $m3u_channelname2 || (strpos($channellist_channelname, $m3u_channelname1) !== false || strpos($channellist_channelname, $m3u_channelname2) !== false || strpos($m3u_channelname1, $channellist_channelname) !== false || strpos($m3u_channelname2, $channellist_channelname) !== false) && strpos($channellist_channelname, '4K测试') === false && strpos($channellist_channelname, 'CCTV4') === false){
                                        $m3u_file_arr[$i + 1] = str_replace("\n", '', preg_replace($pattern3, $channellist_channelurl, $m3u_file_arr[$i + 1]))."\n";
                                    $replacecount++;
                                    $channelarry = array_flip($channelarry);
                                    unset($channelarry[$m3u_channelname1]);
                                    unset($channelarry[$m3u_channelname2]);
                                    $channelarry = array_flip($channelarry);
                                    break;
                                }
                            }
                        }
                    }
                }
                file_put_contents($m3u_file, $m3u_file_arr);
            }else{
                $content = "Info: ";
                $content .= date('Y-m-d H:i:s');
                $content .= "：m3u文件不存在！\n";
                file_put_contents($logfile, $content, FILE_APPEND);
            }
            $content = "Info: ";
            $content .= date('Y-m-d H:i:s');
            $content .= ": “".$m3u_file."”频道播放地址替换完成！\n";
            $content = "Info: ";
            $content .= date('Y-m-d H:i:s');
            $content .= "：应替换数：".$shouldreplacecount;
            $content .= "；实际替换数：".$replacecount."\n";
            file_put_contents($logfile, $content, FILE_APPEND);
            if(count($channelarry) > 0){
                foreach ($channelarry as $value){
                    $content = "Info: ";
                    $content .= date('Y-m-d H:i:s');
                    $content .= "：“".$value."”没有替换！（频道名称不匹配！）\n";
                    file_put_contents($logfile, $content, FILE_APPEND);
                }
            }
            $content = "Info: ";
            $content .= date('Y-m-d H:i:s');
            $content .= "：开始复制“".$m3u_file."”文件至xteve...\n";
            file_put_contents($logfile, $content, FILE_APPEND);
            $destination = '/home/stefan/docker/xteve/';
            //copy($m3u_file, $destination.$m3u_file);
            $command = 'cp -p '.$m3u_file.' '.$destination;
            exec($command);
            $content = "Info: ";
            $content .= date('Y-m-d H:i:s');
            $content .= "：复制“".$m3u_file."”文件至xteve完成！\n";
            file_put_contents($logfile, $content, FILE_APPEND);
            $content = "Info: ";
            $content .= date('Y-m-d H:i:s');
            $content .= "：“".$m3u_file."”文件操作完成！\n";
            file_put_contents($logfile, $content, FILE_APPEND);
            echo "“".$m3u_file."”文件操作完成!\n";
        }catch(Exception $e){
            $errormessage = $e -> getMessage();
            echo "Error: ".$errormessage;
            $content = "Error: ";
            $content .= date('Y-m-d H:i:s');
            $content .= ": ".$errormessage."\n";
            file_put_contents($logfile, $content, FILE_APPEND);
        }
    }
?>