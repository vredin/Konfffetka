<?php

function CreateNewGallery($UrlToParse, $gallery_user_key = 2, $gallery_seo_title='', $gallery_seo_description='', $gallery_seo_keywords='')
{
/*
*   1. Проверяем есть ли такой урл в базе
*   2. Добавляем новую запись о галерее в базу
*   3. Возвращаем ID новой галереи и "-1" если галерея уже есть
*/

    // проверяем на уникальность урл для парсинга среди существующих
    startup();
    $sQuerySelect = "SELECT gallery_parsed_url FROM `Gallery`";
    $ResultSelect = mysql_query($sQuerySelect) or die(mysql_error());
    // echo $NumberAffected = mysql_num_rows($ResultSelect);
    // echo '<br />';
  
    while ($row = mysql_fetch_array($ResultSelect, MYSQL_ASSOC))
    {
       similar_text($UrlToParse, $row['gallery_parsed_url'], $iSimilarity);
       //echo '% of similarity: ' . $iSimilarity . ' | ' . $UrlToParse . ' | ' . $row['gallery_parsed_url'];
       //echo '<br />'; 
    }    
    mysql_close();
    
    if ($iSimilarity > 95)
    {
        return -1;    
    }
    else 
    {

        /////добавляем запись в таблицу Gallery////////
        startup();
        $sQueryInsert = "INSERT INTO `Gallery` (`gallery_id` ,`gallery_seo_title` ,`gallery_seo_description` ,`gallery_seo_keywords` ,`gallery_user_key` ,`gallery_parsed_url` ,`add_date`, `gallery_status`) VALUES (NULL , '$gallery_seo_title', '$gallery_seo_description', '$gallery_seo_keywords', '$gallery_user_key', '$UrlToParse', NOW(), 'new');";
        mysql_query($sQueryInsert) or die(mysql_error());
        
        $sQuerySelect = "SELECT gallery_id FROM `Gallery` WHERE `gallery_parsed_url` LIKE '$UrlToParse'";
        $ResultSelect = mysql_query($sQuerySelect) or die(mysql_error());
        $aResults = mysql_fetch_array($ResultSelect);
        mysql_close();
        $iGalleryId = $aResults['gallery_id'];
        
        /////добавляем запись в таблицу Links//////
        startup();
        $GalleryUrl = 'gallery/' . $aResults['gallery_id'];
        $sQueryInsert = "INSERT INTO `Links` (`link_id` ,`link_hits` ,`link_url` ,`links_gallery_key`) VALUES (NULL , '0', '$GalleryUrl', '$iGalleryId');";
        mysql_query($sQueryInsert) or die(mysql_error());
        mysql_close();
        
        return $iGalleryId;        
    }

}

function CreateNewUser($UserLogin, $UserPassword, $UserMail)
{
/*
*   1. Проверяем на пользователя на уникальность по email
*   2. Если такого нет - создаем
*   3. Возвращаем ID пользователя если создали и -1 если такой уже существует 
*/

    startup();
    $sQuerySelect = "SELECT user_email FROM `Users` WHERE `user_email` = '$UserMail'";
    $ResultSelect = mysql_query($sQuerySelect) or die(mysql_error());
    $NumberAffected = mysql_num_rows($ResultSelect);
    mysql_close();
    
    if ($NumberAffected)
    {
        return -1;    
    }
    else
    {
        startup();
        // echo "$UserLogin | $UserPassword | $UserMail <br>";
        $sQueryInsert = "INSERT INTO `fuskator`.`Users` (`user_id`, `user_login`, `user_password`, `user_email`, `reg_date`) VALUES (NULL, '$UserLogin', '$UserPassword', '$UserMail', NOW());"; 
        mysql_query($sQueryInsert) or die(mysql_error());

        // Здесь можно заменить на функцию возврата последней примененной строки
        $sQuerySelect = "SELECT user_id FROM `Users` WHERE `user_login` LIKE '$UserLogin'";
        $ResultSelect = mysql_query($sQuerySelect) or die(mysql_error());
        $aResults = mysql_fetch_array($ResultSelect);
        //echo $aResults['user_id'] . '<br>';
        mysql_close();
        return $aResults['user_id'];    
    }

}

// функция фильтрации строк, содержащих подстроку jpg
function linkToImage($var)
{   
    $jpg = 'jpg';
    if (substr_count($var, $jpg))
        return $var;
    else 
        return;
}

function get_host($s)
{
    $s = preg_replace('#^http://#Uis', '', trim($s));
    $s = explode('/', trim($s));
    $s = trim($s[0]);
    $s = explode(':', $s);
    $s = trim($s[0]);
    return $s;
}

function get_web_page( $url )
{
    $uagent = "Mozilla/4.0 (compatible; MSIE 6.0; Windows NT 5.1; SV1; .NET CLR 1.1.4322)";

    $sRefferal = 'http://' . get_host($url);
    
    $ch = curl_init( $url );
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);   // возвращает веб-страницу
    
    curl_setopt($ch, CURLOPT_REFERER, $sRefferal);
    
    curl_setopt($ch, CURLOPT_HEADER, 0);           // не возвращает заголовки
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);   // переходит по редиректам
    curl_setopt($ch, CURLOPT_ENCODING, "");        // обрабатывает все кодировки
    curl_setopt($ch, CURLOPT_USERAGENT, $uagent);  // useragent
    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 120); // таймаут соединения
    curl_setopt($ch, CURLOPT_TIMEOUT, 120);        // таймаут ответа
    curl_setopt($ch, CURLOPT_MAXREDIRS, 10);       // останавливаться после 10-ого редиректа

    $content = curl_exec( $ch );
    $err     = curl_errno( $ch );
    $errmsg  = curl_error( $ch );
    $header  = curl_getinfo( $ch );
    curl_close( $ch );

    $header['errno']   = $err;
    $header['errmsg']  = $errmsg;
    $header['content'] = $content;
    return $header;
} 

function deleteDirectory($dir) 
{
    if (!file_exists($dir)) return true;
     if (!is_dir($dir) || is_link($dir)) return unlink($dir);
        foreach (scandir($dir) as $item) {
            if ($item == '.' || $item == '..') continue;
            if (!deleteDirectory($dir . "/" . $item)) {
                chmod($dir . "/" . $item, 0777);
                if (!deleteDirectory($dir . "/" . $item)) return false;
            };
        }
    return rmdir($dir);
} 

function ImageResize($type, $sFullSizeFile, $FileOutput)
{
    // тип преобразования, если не указаны размеры 
    if ($type == 0) $iOutputWidth = 150;  // квадратная 150x150 
    if ($type == 2) $iOutputWidth = 800; // пропорциональная шириной 800 
    $iQuality = 100;                    // качество jpeg по умолчанию
    $sWatermark = 'http://localhost';
    
    // создаём исходное изображение на основе 
    // исходного файла и опеределяем его размеры 
    $rSource = imagecreatefromjpeg($sFullSizeFile); 
    $iSourceWidth = imagesx($rSource); 
    $iSourceHeight = imagesy($rSource);

// если размер исходного изображения отличается от требуемого размера 
//    if ($iSourceWidth != $iOutputWidth) 
//    { 
        // проверяем не меньше ли исходный файл чем наши требования
        if ($iSourceWidth < $iOutputWidth)
        {
          copy($sFullSizeFile, $FileOutput);
          return;  
        } 

       if ($type==2) 
       { 
           // вычисление пропорций 
           $ratio = $iSourceWidth/$iOutputWidth; 
           $iDestinationWidth = round($iSourceWidth/$ratio); 
           $iDestinationHeight = round($iSourceHeight/$ratio); 
           
           // создаём пустую картинку важно именно truecolor!, иначе будем иметь 8-битный результат 
           $rDestination = imagecreatetruecolor($iDestinationWidth, $iDestinationHeight); 
           imagecopyresized($rDestination, $rSource, 0, 0, 0, 0, $iDestinationWidth, $iDestinationHeight, $iSourceWidth, $iSourceHeight);  
            
            /* отключили нанесение Watermark
            // определяем координаты вывода текста 
            $iFontSize = 2; // размер шрифта 
            $iTextPositionX = $iDestinationWidth-imagefontwidth($iFontSize)*strlen($sWatermark)-3; 
            $iTextPositionY = $iDestinationHeight-imagefontheight($iFontSize)-3; 

            // определяем каким цветом на каком фоне выводить текст 
            $iBackgroundWhite = imagecolorallocate($rDestination, 255, 255, 255);
            $iBackgroundBlack = imagecolorallocate($rDestination, 0, 0, 0); 
            $iBackgroundGray = imagecolorallocate($rDestination, 127, 127, 127); 
            if (imagecolorat($rDestination,$iTextPositionX,$iTextPositionY) > $iBackgroundGray) $color = $iBackgroundBlack; 
            if (imagecolorat($rDestination,$iTextPositionX,$iTextPositionY) < $iBackgroundGray) $color = $iBackgroundWhite; 

            imagestring($rDestination, $iFontSize, $iTextPositionX-1, $iTextPositionY-1, $sWatermark, $iBackgroundWhite-$color); 
            imagestring($rDestination, $iFontSize, $iTextPositionX+1, $iTextPositionY+1, $sWatermark, $iBackgroundWhite-$color); 
            imagestring($rDestination, $iFontSize, $iTextPositionX+1, $iTextPositionY-1, $sWatermark, $iBackgroundWhite-$color); 
            imagestring($rDestination, $iFontSize, $iTextPositionX-1, $iTextPositionY+1, $sWatermark, $iBackgroundWhite-$color); 

            imagestring($rDestination, $iFontSize, $iTextPositionX-1, $iTextPositionY, $sWatermark, $iBackgroundWhite-$color); 
            imagestring($rDestination, $iFontSize, $iTextPositionX+1, $iTextPositionY, $sWatermark, $iBackgroundWhite-$color); 
            imagestring($rDestination, $iFontSize, $iTextPositionX, $iTextPositionY-1, $sWatermark, $iBackgroundWhite-$color); 
            imagestring($rDestination, $iFontSize, $iTextPositionX, $iTextPositionY+1, $sWatermark, $iBackgroundWhite-$color); 

            imagestring($rDestination, $iFontSize, $iTextPositionX, $iTextPositionY,   $sWatermark, $color); 
            */
       //echo "Type = 2, функция ImageResize отработала<br>";
       } 

        // операции для получения квадратного файла 
        if ($type==0) 
        { 
             // создаём пустую квадратную картинку важно именно truecolor!, иначе будем иметь 8-битный результат 
             $rDestination = imagecreatetruecolor($iOutputWidth,$iOutputWidth); 

             // вырезаем квадратную серединку по x, если фото горизонтальное 
             if ($iSourceWidth > $iSourceHeight) 
             imagecopyresized($rDestination, $rSource, 0, 0,
                              round((max($iSourceWidth,$iSourceHeight)-min($iSourceWidth,$iSourceHeight))/2),
                              0, $iOutputWidth, $iOutputWidth, min($iSourceWidth,$iSourceHeight), min($iSourceWidth,$iSourceHeight)); 

             // вырезаем квадратную верхушку по y, если фото вертикальное
             if ($iSourceHeight > $iSourceWidth) 
             // ниже закомментирована функция обрезания с самого верха изображения.
             //imagecopyresized($rDestination, $rSource, 0, 0, 0, 0, $iOutputWidth, $iOutputWidth,
             //                 min($iSourceWidth,$iSourceHeight), min($iSourceWidth,$iSourceHeight)); 
             imagecopyresized($rDestination, $rSource, 0, 0, 0, round((max($iSourceWidth,$iSourceHeight)-min($iSourceWidth,$iSourceHeight))/2), $iOutputWidth, $iOutputWidth,
                              min($iSourceWidth,$iSourceHeight), min($iSourceWidth,$iSourceHeight));

             // квадратная картинка масштабируется без вырезок 
             if ($iSourceWidth==$iSourceHeight) 
             imagecopyresized($rDestination, $rSource, 0, 0, 0, 0, $iOutputWidth, $iOutputWidth, $iSourceWidth, $iSourceWidth); 
        
        //echo "Type = 0, функция ImageResize отработала<br>"; 
        } 

        // вывод картинки и очистка памяти 
        imagejpeg($rDestination, $FileOutput, $iQuality); 
        imagedestroy($rDestination); 
        imagedestroy($rSource); 
 //   }    
return;    
}

function extract_links($html, $from_url)
{
   mb_internal_encoding('utf-8');
   mb_regex_encoding('utf-8');
 
   $from_url = get_host($from_url);
   $arr = array();
   $out = array();
 
   $html = str_replace('&nbsp;', ' ', $html);    
   $html = mb_ereg_replace('\s+', ' ', $html, 'is');
   $html = preg_replace('#<!--.*-->#Uuis', '', $html);
   $html = preg_replace('#<script[^>]*>.*</script[^>]*>#Uuis', '', $html);
   $html = preg_replace('#<style[^>]*>.*</style[^>]*>#Uuis', '', $html);
   $html = preg_replace_callback('#<noindex[^>]*>(.*)</noindex[^>]*>#Uuis', 'extract_links_callback', $html);    
 
   if (preg_match_all('#<(a|area)(\s+?[^>]*?\s+?|\s+?)href\s*=\s*(["\'`]*)\s*?([^>\s]+)\s*\3[^>]*?(/>|>(.*?)</\1>|>)#is', $html, $arr, PREG_SET_ORDER))
    {
        foreach($arr as $one)
        {
            $this_href = trim(mb_strtolower($one[4]), ' "\'');
            if ($this_href == '') $this_href = '/';
            if (substr($this_href, 0, 11) == 'javascript:') continue;
            if (substr($this_href, 0, 7) == 'mailto:') continue;
            $this_text = '';
            if (count($one)>6) $this_text = strip_tags(str_replace('<', ' <', $one[6]));
            $this_text = trim(preg_replace('#&(\#\d+|[a-z]+);#uis', ' ', $this_text));
            $this_text = trim(preg_replace('#[\'"&<>`]+#uis', ' ', $this_text));
            $this_text = trim((str_replace('•', ' ', (strip_tags(trim($this_text))))));
            $this_text = str_replace('=', ' ', $this_text);
            $this_text = preg_replace('#\s+#uis', ' ', $this_text);
                if ($this_text=='') $this_text = 'n/t';    
            $this_nofollow = (preg_match('#rel\s*=[\s"\']*nofollow#uis', $one[0]));
            $this_noindex = (preg_match('#rel\s*=[\s"\']*noindex#uis', $one[0]));
                $this_type = (!preg_match('#^http://#is', $this_href)||preg_match('#^http://'.preg_quote($from_url, '#').'#is', $this_href)||preg_match('#^http://'.preg_quote(fix_www($from_url), '#').'#is', $this_href)) ? 'int' : 'ext';
            $out[] = array('href'=>$this_href, 'text'=>$this_text, 'nofollow'=>$this_nofollow, 'noindex'=>$this_noindex, 'type'=>$this_type);        
        }
    }
    return $out;
}

function extract_links_callback($matches)
{
    return preg_replace('# href\s*=#Uuis', ' rel="noindex" href=', $matches[1]);
}

function fix_www($host)
{
    if (substr($host, 0, 4)==='www.')
    {
        $host = preg_replace('#^www.#Uis', '', $host);
    }
    else 
    {
        $host = 'www.' . $host;
    }
    return $host;
}

// данная функция собирает все jpg со страницы в теге img
function ParseURL2($sUrlToParse)
{

    $aPageContent2 = file_get_contents($sUrlToParse);
    
    preg_match_all("/(<img )(.+?)( \/)?(>)/",$aPageContent2,$aTempArray);
    
    $counter = 0;
    foreach ($aTempArray[2] as $value)
    {
        if (preg_match("/(src=)('|\")(.+?)('|\")/", $value, $matches) == 1)
        $aLinksTemp[$counter] = $matches[3];
        $counter++;
    }
   
    $aSplittedUrlToParse = preg_split('#/#',$sUrlToParse);
    $iLastElement = sizeof($aSplittedUrlToParse) - 1;
    
    //echo 'собираем урл из частей, кроме последней части<br>';
    $sCuttedUrl = 'http://';
    for($i = 2; $i < $iLastElement; $i++)
    {
        $sCuttedUrl .= $aSplittedUrlToParse[$i] . '/';
    }

    $counter = 0;
    for ($i = 0; $i < sizeof($aLinksTemp); $i++)
    {
        // работаем только с ссылками, содержащими jpg
        if(substr_count($aLinksTemp[$i], 'jpg'))
        {        
            // если ссылка на картинку относительная, то
            if(!substr_count($aLinksTemp[$i], 'http'))
            {
                $aFinalImgLinks[$counter] = $sCuttedUrl . $aLinksTemp[$i];
                $counter++;
            }
            else // а если ссылка на картинку абсолютная
            {
                $aFinalImgLinks[$counter] = $aLinksTemp[$i];
                $counter++;  
            }

        }
    } 
return $aFinalImgLinks;
}

// данная функция собирает со страницы все ссылки на jpg в теге <a href></a>
function ParseURL($sUrlToParse)
{
    $aPageContent = get_web_page($sUrlToParse); //my function
    //$file_content = file_get_contents($url);

    $aLinks = extract_links($aPageContent['content'], $sUrlToParse);

    $aSplittedUrlToParse = preg_split('#/#',$sUrlToParse);
    $iLastElement = sizeof($aSplittedUrlToParse) - 1;

    //echo 'собираем урл из частей, кроме последней части<br>';
    $sCuttedUrl = 'http://';
        for($i = 2; $i < $iLastElement; $i++)
        {
            $sCuttedUrl .= $aSplittedUrlToParse[$i] . '/';
        }


    $counter = 0;
    for ($i = 0; $i < sizeof($aLinks); $i++)
    {
        // работаем только с ссылками, содержащими jpg
        if(substr_count($aLinks[$i]['href'], 'jpg'))
        {        
            // если ссылка на картинку относительная, то
            if(!substr_count($aLinks[$i]['href'], 'http'))
            {
                $aFinalImgLinks[$counter] = $sCuttedUrl . $aLinks[$i]['href'];
                //echo $aFinalImgLinks[$counter] . '<br />';
                $counter++;
                //echo $aLinks[$i]['href'] . '<br />';}   
            }
            else // а если ссылка на картинку абсолютная
            {
                $aFinalImgLinks[$counter] = $aLinks[$i]['href'];
                //echo $aFinalImgLinks[$counter] . '<br />';
                $counter++;  
            }

        }
    }  
return $aFinalImgLinks;  
}


/*
function ParseURL($sUrl)
{
    
    //$sUrl = 'http://www.mywifebitch.com/fhg/3/';
    //$sUrl = 'http://galleries.gals4free.net/gals/angel-rivas-hot-ass-fuck-hard/Angel-Rivas_[02-20].jpg';
    //$sUrl = 'http://www.deflorationtgp.com/content/Lily_Cross/lily_cross_[51-100].jpg';

    // находим диапазон
    $sPattern = '/(\[(.*)\])/i';
    $iCount = preg_match_all($sPattern, $sUrl, $aRangeRaw);
    // echo $iCount . '<br>';

    // проверяем наличие диапазона в урле на парсинг
    if ($iCount == 1)
    {
        // вывод количества диапазонов и сам диапазон
        // echo $iCount . ' | ' . $aRangeRaw[2][0]; 
        $sRange = $aRangeRaw[2][0];
        $aRange = explode("-", $sRange);

        // разбиваем исходную строку на массив для подмены диапазона конечными значениями
        $aSymbols = array("[" => "#", "]" => "#");
        $sFormattedUrl = strtr($sUrl, $aSymbols);
        $aUrl = preg_split("/#/", $sFormattedUrl);
        // print_r($aUrl);
        // echo '<br>';

        // проверяем нули вначале и в конце диапазона
        // первые [] это диапазоны, вторые [] это первый элемент строки
        $aRange[0][0] == 0 ? $iRangeZeroStart = 1 : $iRangeZeroStart = 2;
        $aRange[1][0] == 0 ? $iRangeZeroEnd = 4 : $iRangeZeroEnd = 8;
        
        $iIndicator = $iRangeZeroStart + $iRangeZeroEnd;   
        // echo 'Zero indicator = ' . $iIndicator = $iRangeZeroStart + $iRangeZeroEnd;

        switch($iIndicator)
        {
            case 5:
                // echo 'Данный функционал в разработке<br>';
                break;
            case 6:
                // echo 'Данный функционал в разработке<br>';
                break;
            case 9:
                // echo 'Данный функционал в разработке<br>';
                break;
            case 10:
                $iCounter = 0;
                for ($i = $aRange[0]; $i < $aRange[1] + 1; $i++)
                {
                    //echo $aUrl[0] . $iCounter . $aUrl[2] . '<br>';
                    $aFinalArray[$iCounter] = $aUrl[0] . $i . $aUrl[2];
                    $iCounter++;
                }
                break;
        }
    // echo 'ParseURL: Список изображений сформирован<br>';
    // print_r($aFinalArray);
    // echo '<br>';
    return $aFinalArray;
    } 
    else 
    {
        
        $aRawContent = get_web_page($sUrl);

        $content = $aRawContent['content'];
        $pattern = '/href="(.*)"><img/i';
        // $pattern = '#([^\s]+(?=\.(jpg))\.\2)#Uis';
        $count = preg_match_all($pattern, $content, $images_array);

        // убираем из массива элементы не содержащие jpg
        $filtered_image_array = array_filter($images_array['1'], "linkToImage");

        // счетчик финального массива
        $iCounterFinalArray = 0;
        
        // формируем код для скачивания изображения
        for($counter = 0; $counter < sizeof($filtered_image_array); $counter++)
        {
            $substring = 'http://'; 
            // echo 'мы внутри for';       
            
                    
            if (!substr_count($value, $substring)) // если в урле нет http, то..
            {
                if ($filtered_image_array[$counter] == '')
                    continue;
                    
                //echo $filtered_image_array[$counter] = $sUrl . $filtered_image_array[$counter] . '<br>';
                $aFinalArray[$iCounterFinalArray] = $sUrl . $filtered_image_array[$counter];
                $iCounterFinalArray++;  
            }
        }
//    echo 'ParseURL: Список изображений сформирован<br>';
//    print_r($aFinalArray);
//    echo '<br>';       
    return $aFinalArray;           
    } // end if на проверку диапазона в урле

}
*/

function UploadImages($aLinks, $sGalleryId)
//function UploadImages($aLinks, $sGalleryId, $sPathFullSize, $sPathThumbs150, $sPathThumbs800)
{

    $sPathFullSize = _sPathFullSize_ . $sGalleryId;
    $sPathThumbs150 = _sPathThumbs150_ . $sGalleryId;
    $sPathThumbs800 = _sPathThumbs800_ . $sGalleryId;

    
    // проверка на наличие директории
    /*
    if (file_exists($sPathFullSize)) 
    {
        echo 'Такая директория уже существует, удаляю ее';
        deleteDirectory($sPathFullSize);
    }
    */
    //else
    //{
        mkdir($sPathFullSize, 0777);
        mkdir($sPathThumbs150, 0777);
        mkdir($sPathThumbs800, 0777);
        
//        print_r($aLinks);
//        echo '<br />';
//        echo sizeof($aLinks);
//        echo '<br />';
        
        for($i = 0; $i < sizeof($aLinks); $i++)
        {
            // сначала проверяем изображение и его размер, затем загружаем
            $aImageInfo = getimagesize($aLinks[$i]);

            // проверяем размеры, если менее 400 по одной из сторон, удаляем.
            if (($aImageInfo[0] > 400) and ($aImageInfo[1] > 400))
            {
                if ($aImageInfo[2] == 2) // проверяем jpg ли
                {
                    $fp = fopen( $sPathFullSize . '/' . $i . '.jpg', 'w');
                    fwrite($fp, file_get_contents($aLinks[$i]));
                    // echo "File #$i has uploaded. Its size if $aImageInfo[3], type of image is $aImageInfo[2], <br>";
                    fclose($fp);
                    sleep(3);
                }
                
            }
               
        } // end FOR   
    //}  // end else
//echo "UploadImages: загрузка изображений в галерею $sGalleryId осуществлен<br>"; 
//echo '~~~~~~~~~~~~~~~<br>'; 
return;        
}

function GetThumbByGalId($iGalleryId, $sPathThumbs150)
{     
    $sPathToPreview = $sPathThumbs150 . $iGalleryId;
    $aDirContent = scandir($sPathToPreview);

    $maxElement = sizeof($aDirContent) - 3;
    
    // echo $sPathToPreview . ' | ' . sizeof($aDirContent) . ' | ' . $maxElement;
    $iPreviewThumb = rand(2, $maxElement);
    return $iPreviewThumb;
}


function GetPageContent($RequestedPage)
{
        
    $GalleriesPerPage = _GalleriesPerPage_;
    //$iCurrentPage = $_GET['page']; 
    $iCurrentPage = $RequestedPage;
    $iCurrentPage = intval($iCurrentPage);
    
    startup();    
    $sQueryCount = "SELECT COUNT(*) FROM Gallery WHERE `gallery_status` = 'approved';";
    $ResultCount = mysql_query($sQueryCount) or die(mysql_error());
    $aResultsCount = mysql_fetch_array($ResultCount);
    mysql_close();
    $iTotalGalleries = $aResultsCount[0];

    // поделив количество галерей всего на количество галерей на одной странице и 
    //приведя к целому - получим количество страниц
    $iFinalPage = intval(($iTotalGalleries - 1) / $GalleriesPerPage) + 1; 

    // Если значение $page меньше единицы или отрицательно 
    // переходим на первую страницу 
    // А если слишком большое, то переходим на последнюю 
    if(empty($iCurrentPage) or $iCurrentPage < 0) $iCurrentPage = 1; 
    if($iCurrentPage > $iFinalPage) $iCurrentPage = $iFinalPage; 

    // Вычисляем начиная к какого номера следует выводить сообщения 
    // 1:0-20; 2:20-40; 3:40-60
    $GalleryStartFrom = $iCurrentPage * $GalleriesPerPage - $GalleriesPerPage; 
    
    startup();
    $sQuerySelect = "SELECT * FROM `Gallery` WHERE `gallery_status` = 'approved' ORDER BY `gallery_id` DESC LIMIT $GalleryStartFrom, $GalleriesPerPage";

    $ResultSelect = mysql_query($sQuerySelect) or die(mysql_error());

    while ( $aResults[] = mysql_fetch_array($ResultSelect)) {}
    mysql_close();  
    
    if($iCurrentPage != $iFinalPage)
        $GalleriesPerPage = _GalleriesPerPage_;
    else
        $GalleriesPerPage = sizeof($aResults) - 1;
    
    //echo '<br />' . $GalleriesPerPage . '<br />';
    
    for ($i = 0; $i < $GalleriesPerPage; $i++)
    {
        $ThumbId = GetThumbByGalId($aResults[$i][0], _sPathThumbs150_);
        //$ThumbId = 1;

        $GalId = $aResults[$i]['gallery_id'];
        $SeoTitle = $aResults[$i]['gallery_seo_title'];
        //$iRating = GetRating($GalId);
        //echo ' | ' . $GalId . ' | ';
        $aListOfGalleries[$i] = "<li><a href=\"" . _BaseUrl_ . 'gallery/' . $GalId;
        //$ListOfGalleries .= "\"><img width=\"150\" height=\"150\" src=\"" . _BaseUrl_ . 'gallery/thumbs150/' . $GalId . '/' . $ThumbId . '.jpg';
        $aListOfGalleries[$i] .= "\"><img width=\"150\" height=\"150\" src=\"" . _BaseUrl_ . 'gallery/thumbs150/'. $GalId . '/' . $ThumbId . '.jpg';
        $aListOfGalleries[$i] .= "\" alt=\"$SeoTitle\" title=\"$SeoTitle\" /></a></li>";
        $aListOfGalleries[$i] .= "\n"; 
    }              

    $aReturnParams[0] = $aListOfGalleries;
    $aReturnParams[1] = $iFinalPage;    
    return $aReturnParams;
}

/*
function GetRating($GalleryId)
{
    
    //*   1. Получаем список всех рейтингов для $GalleryId
    //*   2. Получаем число записей
    //*   3. Суммируем значение рейтинга
    //*   4. Делим его на число записей и округляем до целого в большую сторону
    //*   5. Возвращаем массив из конечного рейтинга и количество голосовавших
    
    startup();
    $sQuerySelect = "SELECT rating_number FROM `Rating` WHERE `rating_gallery_key` = '$GalleryId'";
    $ResultSelect = mysql_query($sQuerySelect) or die(mysql_error());
    
    $RowsCounter = 0;
    $TotalRating = 0;
    while ($row = mysql_fetch_array($ResultSelect, MYSQL_ASSOC)) 
    {
        $RowsCounter++;
        $TotalRating += $row['rating_number'];
    }
    mysql_close();

    //echo $GalleryId . ' | ' . $RowsCounter++ . ' | ' . $TotalRating;
    
    $FinalRating[0] = ceil($TotalRating / $RowsCounter);
    $FinalRating[1] = $RowsCounter;
    
    return $FinalRating;
} 
*/

/*
function SetRating($iGalleryId, $iRating, $sUserIp)
{

    //*   1. Проверяем уникальный ли IP для галереи $GalleryId
    //*   2. Добавляем для галерени $GalleryId рейтинг $iRating
    
    
    startup();
    // echo $iGalleryId . ' | ' . $iRating . ' | ' . $sUserIp . '<br />';
    $sQuerySelect = "SELECT * FROM `Rating` WHERE `rating_ip` = '$sUserIp' AND `rating_gallery_key` = $iGalleryId";
    $ResultSelect = mysql_query($sQuerySelect) or die(mysql_error());
    $NumberAffected = mysql_num_rows($ResultSelect);
    mysql_close();
    
    if (!$NumberAffected) // если строки не найдены, добавляем новый рейтинг
    {
       startup();
$sQueryInsert = "INSERT INTO `Rating` (`rating_id` ,`rating_gallery_key` ,`rating_number` ,`rating_ip`) VALUES (NULL , '$iGalleryId', '$iRating', '$sUserIp');"; 
       mysql_query($sQueryInsert) or die(mysql_error());
       mysql_close();
    }
       
}
*/

function GetRandomGallery()
{
    startup();
    $sQuerySelectAllPosts = "SELECT * FROM `Gallery` WHERE `gallery_status` = 'approved'";
    $ResultsSelect = mysql_query($sQuerySelectAllPosts) or die(mysql_error());

    while ($aResults[] = mysql_fetch_array($ResultsSelect)) {}    
    
    mysql_close();
    //print_r($aResults);
    //echo '<br />';
    $iRandomIndex = rand(0, sizeof($aResults));
    $iRandomGallery = $aResults[$iRandomIndex][0]; 
    
    $sRandomGallery = _BaseUrl_ . 'gallery/' . $iRandomGallery;
    
    return $sRandomGallery;
}

function GalleryHitsIncrease($iGalleryId)
{
    startup();
    $sQuerySelect = "SELECT * FROM `Links` WHERE `links_gallery_key` = $iGalleryId";
    $ResultSelect = mysql_query($sQuerySelect) or die(mysql_error());
    while ( $aResults[] = mysql_fetch_array($ResultSelect)) {}
    mysql_close();    
    
    $iGalleryHits = $aResults[0][1];
    $iGalleryHits = $iGalleryHits + 1;
    
    startup();
    $sQueryUpdate = "UPDATE `Links` SET `link_hits` = $iGalleryHits WHERE `links_gallery_key` = $iGalleryId";
    $ResultSelect = mysql_query($sQueryUpdate) or die(mysql_error());
    mysql_close();
}

?>