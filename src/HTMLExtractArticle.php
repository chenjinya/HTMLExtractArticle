<?php
/**
 * Created by PhpStorm.
 * User: jinya
 * Date: 2017/12/7
 * Time: 下午2:36
 */

class HTMLExtractArticle {


    const COMBINE_LINE_COUNT = 4;
    const MAX_WORD_COUNT = 100;
    const MIN_WORD_COUNT = 50;


   public function extract($uri){
       $content = $this->fget($uri);

       $encode = mb_detect_encoding($content, array("ASCII",'UTF-8',"GB2312", "GBK",'BIG5'));
        if ($encode != "UTF-8"){
            $content = iconv($encode,"UTF-8",$content);
        }

       $content = $this->valuableText($content);

       $structure = $this->analysisText($content);

       list($cursor, $headCursor, $tailCursor) = ($this->cursor($structure));

       $articles =  $this->article($structure, $cursor, $headCursor, $tailCursor);

       return implode("\n", $articles);

   }

   public function fget($uri){
       return file_get_contents($uri);
   }

   public function valuableText($html){
        $html=preg_replace("/<\!--.*?-->/si","",$html); //注释
        $html=preg_replace("/<(\!.*?)>/si","",$html); //过滤DOCTYPE
//        $html=preg_replace("/<(\/?html.*?)>/si","",$html); //过滤html标签
        $html=preg_replace("/<(\/?head.*?)>/si","",$html); //过滤head标签
        $html=preg_replace("/<(\/?meta.*?)>/si","",$html); //过滤meta标签
        $html=preg_replace("/<(\/?body.*?)>/si","",$html); //过滤body标签
        $html=preg_replace("/<(\/?link.*?)>/si","",$html); //过滤link标签
        $html=preg_replace("/<(\/?form.*?)>/si","",$html); //过滤form标签

        $html=preg_replace("/<(applet.*?)>(.*?)<(\/applet.*?)>/si","",$html); //过滤applet标签
        $html=preg_replace("/<(\/?applet.*?)>/si","",$html); //过滤applet标签

        $html=preg_replace("/<(style.*?)>(.*?)<(\/style.*?)>/si","",$html); //过滤style标签
        $html=preg_replace("/<(\/?style.*?)>/si","",$html); //过滤style标签
        $html=preg_replace("/<(title.*?)>(.*?)<(\/title.*?)>/si","",$html); //过滤title标签
        $html=preg_replace("/<(\/?title.*?)>/si","",$html); //过滤title标签
        $html=preg_replace("/<(object.*?)>(.*?)<(\/object.*?)>/si","",$html); //过滤object标签
        $html=preg_replace("/<(\/?objec.*?)>/si","",$html); //过滤object标签
        $html=preg_replace("/<(noframes.*?)>(.*?)<(\/noframes.*?)>/si","",$html); //过滤noframes标签
        $html=preg_replace("/<(\/?noframes.*?)>/si","",$html); //过滤noframes标签
        $html=preg_replace("/<(i?frame.*?)>(.*?)<(\/i?frame.*?)>/si","",$html); //过滤frame标签
        $html=preg_replace("/<(\/?i?frame.*?)>/si","",$html); //过滤frame标签
        $html=preg_replace("/<(script.*?)>(.*?)<(\/script.*?)>/si","",$html); //过滤script标签
        $html=preg_replace("/<(\/?script.*?)>/si","",$html); //过滤script标签

        return strip_tags($html);
   }


   public function analysisText($noTagHtml){
       $lines = explode("\n", $noTagHtml);
       $structure = [];
       foreach($lines as $rowNum =>$line){
           $line = trim($line);
           if($line){
               {
                   $structure[$rowNum] = [
                       'line' => $rowNum,
                       'text' => $line,
                       'wc' => mb_strlen($line)
                   ];
               }
           }
       }

       return $structure;
   }

   public function cursor($structure){

       $cursor = [];
       $list = array_values($structure);
       $lineCount = count($list) ;
       $blockCount = ceil($lineCount / self::COMBINE_LINE_COUNT);
       $headCursor = 0;
       $tailCursor = 99999;

       for($blockIndex = 0; $blockIndex < $blockCount; $blockIndex ++  ) {

           $linesWc = 0;
           $lineFrom = 0;
           $lineTo = 0;
           for($i = 0; $i < self::COMBINE_LINE_COUNT; $i++) {

               $listIndex = $blockIndex * self::COMBINE_LINE_COUNT + $i;

               $item = $list[$listIndex];
               if(!$item) {
                   break;
               }

               if(0 == $lineFrom) {
                   $lineFrom =  $item['line'];
               }
               $linesWc += $item['wc'];
               $lineTo =  $item['line'];

           }
           $cursor[$blockIndex] = [
               'wc' => $linesWc,
               'line_from' => $lineFrom,
               'line_to' => $lineTo,
           ];

           if(0 == $headCursor && $linesWc > $headCursor && $linesWc > self::MAX_WORD_COUNT) {
               $headCursor = $blockIndex;
           }

           if($headCursor > 0 && 99999 == $tailCursor && $linesWc < self::MIN_WORD_COUNT) {
               $tailCursor = $blockIndex;
           }
       }

       return [
           $cursor,
           $headCursor,
           $tailCursor
       ];
   }

   public function article($structure, $cursor, $headCursor, $tailCursor){
       $from = $cursor[$headCursor]['line_from'];
       $to = $cursor[$tailCursor]['line_to'];

       $article = [];
       for($lineNum = $from; $lineNum < $to; $lineNum++) {
           if($structure[$lineNum]['text']) {
               $article[] = ($structure[$lineNum]['text']);
           }

       }
       return $article;
   }
}
