<?php

function GetRating($iGalleryId)
{
    
    //*   1. �������� ������ ���� ��������� ��� $GalleryId
    //*   2. �������� ����� �������
    //*   3. ��������� �������� ��������
    //*   4. ����� ��� �� ����� ������� � ��������� �� ������ � ������� �������
    //*   5. ���������� ������ �� ��������� �������� � ���������� ������������
    
    startup();
    $sQuerySelect = "SELECT rating_number FROM `Rating` WHERE `rating_gallery_key` = '$iGalleryId'";
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
  
function SetRating($iRating, $iGalleryId, $sUserIp)
{

    //*   1. ��������� ���������� �� IP ��� ������� $GalleryId
    //*   2. ��������� ��� �������� $GalleryId ������� $iRating
    
    
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
  
// $iRating =$_GET['type'];
// $iGalleryId = $_GET['id'];
// $sUserIp = $_GET['ip'];

echo 777;

/*
if (!$iRating == '')
{
    
    SetRating($iRating, $iGalleryId, $sUserIp);
    $iFinalRating = GetRating($iGalleryId);
    echo $iFinalRating[1];
    
}
else
{
    $iFinalRating = GetRating($iGalleryId);
    echo $iFinalRating[1];
}
*/

/*
//��������� ������� � ����
if($type=="rulez") mysql_query("UPDATE posts SET votes=votes+1 WHERE id='$id'");
if($type=="sux") mysql_query("UPDATE posts SET votes=votes-1 WHERE id='$id'");

//�������� ����� ������� � ������� ���
$row=mysql_fetch_array(mysql_query("SELECT votes FROM posts WHERE id='$id'"));
echo $row['votes'];
*/

//� ����� �������� ������ �������
//echo rand(1,10);  
  
  
?>
