<?php
 
require('config.php');
require('functions.php'); 
  
function GetRating($GalleryId)
{
    /*
    *   1. �������� ������ ���� ��������� ��� $GalleryId
    *   2. �������� ����� �������
    *   3. ��������� �������� ��������
    *   4. ����� ��� �� ����� ������� � ��������� �� ������ � ������� �������
    *   5. ���������� ������ �� ��������� �������� � ���������� ������������
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
    *   1. ��������� ���������� �� IP ��� ������� $GalleryId
    *   2. ��������� ��� �������� $GalleryId ������� $iRating
    */
    
    startup();
    // echo $iGalleryId . ' | ' . $iRating . ' | ' . $sUserIp . '<br />';
    $sQuerySelect = "SELECT * FROM `Rating` WHERE `rating_ip` = '$sUserIp' AND `rating_gallery_key` = $iGalleryId";
    $ResultSelect = mysql_query($sQuerySelect) or die(mysql_error());
    $NumberAffected = mysql_num_rows($ResultSelect);
    mysql_close();
    
    if (!$NumberAffected) // ���� ������ �� �������, ��������� ����� �������
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
