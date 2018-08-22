<?php

/**
 * 批量后台设置缓存
 */
 
function getHosts(){
    $content = file_get_contents('host.txt');
    foreach( explode("\n",$content) as $host)
        if(trim($host))
            yield $host;
}
 
function getData(){
    return [[
        
    ],[
        'config[HTML_CACHE]'=> '1',
        'config[DIR_CACHE]'=> 'Lw==******86400',
        'dirs[]'=> '/',
        'cachetime[]'=> '86400',
        'dirs[]'=> '',
        'cachetime[]'=> '',
        'dirs[]'=> '',
        'cachetime[]'=> '',
        'dirs[]'=> '',
        'cachetime[]'=> '',
        'dirs[]'=> '',
        'cachetime[]'=> ''
    ]];
}
 
function  setting(){
    foreach(getHosts() as $host){
        echo $host,"-\n";
    }
}

function curl(){
    
}

setting();