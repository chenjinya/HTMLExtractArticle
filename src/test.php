<?php
/**
 * Created by PhpStorm.
 * User: jinya
 * Date: 2017/12/8
 * Time: 下午4:52
 */

require __DIR__ . '/HTMLExtractArticle.php';
function t($str){
    echo "\n\e[32m$str\033[0m\n";
}

$ha = new HTMLExtractArticle();

$content = $ha->extract('http://news.163.com/17/1212/10/D5EUNRFH00018AOQ_all.html');


echo $content;
//
//var_dump($originStructure[$startLineNum]);
//var_dump($originStructure[$endLineNum ]);
//
