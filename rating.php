<?php
 
require('config.php');
require('functions.php'); 
  
function GetRating($GalleryId)
{
    /*
    *   1. ѕолучаем список всех рейтингов дл€ $GalleryId
    *   2. ѕолучаем число записей
    *   3. —уммируем значение рейтинга
    *   4. ƒелим его на число записей и округл€ем до целого в большую сторону
    *   5. ¬озвращаем массив из конечного рейтинга и количество голосовавших
    */
    
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
  
function SetRating($iGalleryId, $iRating, $sUserIp)
{
    /*
    *   1. ѕровер€ем уникальный ли IP дл€ галереи $GalleryId
    *   2. ƒобавл€ем дл€ галерени $GalleryId рейтинг $iRating
    */
    
    startup();
    // echo $iGalleryId . ' | ' . $iRating . ' | ' . $sUserIp . '<br />';
    $sQuerySelect = "SELECT * FROM `Rating` WHERE `rating_ip` = '$sUserIp' AND `rating_gallery_key` = $iGalleryId";
    $ResultSelect = mysql_query($sQuerySelect) or die(mysql_error());
    $NumberAffected = mysql_num_rows($ResultSelect);
    mysql_close();
    
    if (!$NumberAffected) // если строки не найдены, добавл€ем новый рейтинг
    {
       startup();
$sQueryInsert = "INSERT INTO `Rating` (`rating_id` ,`rating_gallery_key` ,`rating_number` ,`rating_ip`) VALUES (NULL , '$iGalleryId', '$iRating', '$sUserIp');"; 
       mysql_query($sQueryInsert) or die(mysql_error());
       mysql_close();
    }
    
    
}
  
$GalleryId = 92;
SetRating($GalleryId, 9, '192.168.3.2');
// $aRating = GetRating($GalleryId);
// echo $aRating[0] . ' | ' . $aRating[1];
  
?>
