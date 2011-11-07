<?php
ini_set("max_execution_time", "7200");
ob_implicit_flush(1);

require_once('config.php');
require_once('functions.php');

function getSQLResults($sQuery)
{
    startup();
    $ResultSelect = mysql_query($sQuery) or die(mysql_error());
    while ($aResults[] = mysql_fetch_array($ResultSelect, MYSQL_ASSOC)) {}
    mysql_close();  
    
    return $aResults;
}

function showTable($aContent)
{
    echo '<br />Total: ' . (sizeof($aContent) - 1) . '<br />';
    echo '<table border="1"><tr>';
    echo '<td width="40">Id</td><td width="40">Number of imgs</td><td width="600">Parsed URL</td><td width="50">Status</td><td>Edit</td>';
    echo '</tr>';
    for($i = 0; $i < (sizeof($aContent) - 1); $i++)
   {
        $PathToImgs = _sPathFullSize_ . $aContent[$i]['gallery_id'];
        $aDirContent = scandir($PathToImgs);
        $iNumberOfImages = (sizeof($aDirContent) - 2);
        echo '<tr>';
        echo '<td>' . $aContent[$i]['gallery_id'] . '</td>';
        echo '<td>' . $iNumberOfImages . '</td>';
        echo '<td><a href="'. $aContent[$i]['gallery_parsed_url'] . '">' . $aContent[$i]['gallery_parsed_url'] . '</a></td>';
        echo '<td>' . $aContent[$i]['gallery_status'] . '</td>';
        echo '<td><a href="/adminka.php?gallery=' . $aContent[$i]['gallery_id'] . '">Edit gallery</a></td>';
        echo '</tr>';
   }
   echo '</table>'; 
}
?>

<html>
<head></head>
<body>

<script language=javascript>
function doClear(theText) { if (theText.value == theText.defaultValue) { theText.value = "" } }
function doDefault(theText) { if (theText.value == "") { theText.value = theText.defaultValue } }
</script>

<table border="1">
    <tr>
        <td width="150" style="padding-left:10px;" bgcolor="green">
            <form action="adminka.php" method="post">
                <select name="GetStatus" size="5" multiple="multiple">
                    <option value="new">new</option>
                    <option value="approved">approved</option>
                    <option value="cron">cron</option>
                    <option value="blocked">blocked</option>
                    <option value="trash">trash</option>
                </select><br><br />
            <input type = "submit" name = 'submit' value = "Сделать выборку">
            </form>
        </td>
        <td width="150" style="padding:5px 10px;" bgcolor="green">
            <form action="adminka.php" method="post">
            <input size="10" value="gallery Id" type="text" name="GalleryId" id="textfield" onFocus="doClear(this)" onBlur="doDefault(this)"><br />
            <input type="radio" name="ChangeStatus" value="new">New<br>
            <input type="radio" name="ChangeStatus" value="approved">Approved<br>
            <input type="radio" name="ChangeStatus" value="cron">Cron<br>
            <input type="radio" name="ChangeStatus" value="blocked">Blocked<br>
            <input type="radio" name="ChangeStatus" value="trash">Trash<br>
            <input type = "submit" name = 'submit' value = "Изменить статус">
            </form>
        </td>
        <td width="160" style="padding:5px 10px;" bgcolor="green">
            Создать новую галерею в статусе new<br /><br />
            <form action="adminka.php" method="post">
            <input size="22" value="path to parse new gallery" type="text" name="NewGallery" id="textfield" onFocus="doClear(this)" onBlur="doDefault(this)"><br />
            <input type = "submit" name = 'submit' value = "New Gallery">
            </form>
        </td>
        <td width="150" style="padding:5px 10px;" bgcolor="green">
            Парсинг Full изображений для new галерей<br /><br />
            <form action="adminka.php" method="post">
            <input size="10" value="gallery Id" type="text" name="FullImgParsing" id="textfield" onFocus="doClear(this)" onBlur="doDefault(this)"><br />
            <input type = "submit" name = 'submit' value = "Full img parsing">
            </form>
        </td>
        <td width="150" style="padding:5px 10px;" bgcolor="green">
            Перебрать все изображения в Full папке галереи до создания превью<br /><br />
            <form action="adminka.php" method="post">
            <input size="10" value="gallery Id" type="text" name="ReOrder" id="textfield" onFocus="doClear(this)" onBlur="doDefault(this)"><br />
            <input type = "submit" name = 'submit' value = "Img rename">
            </form>
        </td>
        <td width="150" style="padding:5px 10px;" bgcolor="red">
            Добавление галереи с локальной папки<br /><br />
            <form action="adminka.php" method="post">
            <input size="10" value="gallery Id" type="text" name="ReOrder" id="textfield" onFocus="doClear(this)" onBlur="doDefault(this)"><br />
            <input type = "submit" name = 'submit' value = "Img rename">
            </form>
        </td>
    </tr>
</table>

<hr>

<?php

//print_r($_POST);
if (!$_POST['SeoTags'] == '')
{
   $iGalleryId = $_POST['SeoTags'];
   $sSeoTitle = $_POST['SeoTitle'];
   $sSeoDescription = $_POST['SeoDescription'];
   $sSeoKeywords =  $_POST['SeoKeywords'];  
   
    startup();
    $sQueryUpdate = "UPDATE `Gallery` SET `gallery_seo_title` = '$sSeoTitle', `gallery_seo_description` = '$sSeoDescription', `gallery_seo_keywords` = '$sSeoKeywords' WHERE `gallery_id` = $iGalleryId;"; 
    $ResultSelect = mysql_query($sQueryUpdate) or die(mysql_error());
    if ($ResultSelect)
        echo 'updated';
    else
        echo 'not updated';
    mysql_close();
}


if (!$_POST['ReOrder'] == '')
{
    $GalleryId = $_POST['ReOrder'];
    
    $sPathFullSize = _sPathFullSize_;
    
    echo 'Путь к галерее: ' . $sPathToFull = $sPathFullSize . $GalleryId;
    echo '<br />';
    $aDirContent = scandir($sPathToFull);
    
    //print_r($aDirContent);
    //echo '<br />';
    
    // перемещаем все файлы во временную папку с переименовыванием
    $TempPath = $sPathToFull . '/temp';
    mkdir($TempPath, 0777);
    $counter = 0;
    for ($i = 2; $i < sizeof($aDirContent); $i++)
    {
        $OldFile = $sPathToFull . '/' .$aDirContent[$i];
        $NewFile = $TempPath . '/' . $counter . '.jpg';
        copy($OldFile, $NewFile);
        unlink($OldFile);
        $counter++;
    }
    
    // перемещаем обратно без изменений в именах
    //$counter=0;
    $aDirContent = scandir($TempPath);
    echo $TempPath . '<br />';
    //print_r($aDirContent);
    echo '<br /><br />';
    for($i = 2; $i < sizeof($aDirContent); $i++)
    {           
        $OldFile = $TempPath . '/' . $aDirContent[$i];
        //echo "Old file: $OldFile <br />";
        $NewFile = $sPathToFull . '/' .$aDirContent[$i];
        //echo "New file: $NewFile <br /><br />";
        copy($OldFile, $NewFile);
    }  
    deleteDirectory($TempPath);
    
}


if (!$_POST['FullImgParsing'] == '')
{
    $GalleryId = $_POST['FullImgParsing'];
    $sQuerySelect = "SELECT `gallery_parsed_url` FROM `Gallery` WHERE `gallery_id` = $GalleryId;";
    $aResults = getSQLResults($sQuerySelect);
    
    $aImgLinks = ParseURL($aResults[0]['gallery_parsed_url']);
    UploadImages($aImgLinks, $GalleryId);
    
    $aImgLinks = ParseURL2($aResults[0]['gallery_parsed_url']);
    UploadImages($aImgLinks, $GalleryId);
    
}

if (!$_POST['NewGallery'] == '')
{
    //print_r($_POST);
    $sPathToParse =  $_POST['NewGallery'];
    $iGalleryId = CreateNewGallery($sPathToParse);
    if ($iGalleryId == -1)
        echo 'Gallery WAS NOT created';
    else
    {
        echo 'Gallery with id: ' . $iGalleryId . ' was added';
        $aImgLinks = ParseURL($sPathToParse);
        //echo '<br />';
        //print_r($aImgLinks);
    }
        
}

if (!$_POST['ChangeStatus'] == '')
{
    //print_r($_POST);
    $sChangeStatus =  $_POST['ChangeStatus'];
    $iGalleryId = $_POST['GalleryId'];
    
    if(($sChangeStatus == 'approved') or ($sChangeStatus == 'cron'))
    {
        $PathToImgs = _sPathFullSize_ . $iGalleryId;
        $aDirContent = scandir($PathToImgs);
        $iNumberOfImages = (sizeof($aDirContent) - 2);
        
        $type = 0;
        for($counter = 0; $counter < $iNumberOfImages; $counter++)
        {
            $sPathFullSize1 = $PathToImgs . '/' . $counter . '.jpg';
            $FileOutput = _sPathThumbs150_ . $iGalleryId . '/' . $counter . '.jpg';
            //echo $type . ' | ' . $sPathFullSize1 . ' | ' . $FileOutput . '<br />';
            ImageResize($type, $sPathFullSize1, $FileOutput); 
        }

        $type = 2;
        for($counter = 0; $counter < $iNumberOfImages; $counter++)
        {
            $sPathFullSize1 = $PathToImgs . '/' . $counter . '.jpg';
            $FileOutput = _sPathThumbs800_ . $iGalleryId . '/' . $counter . '.jpg';
            //echo $type . ' | ' . $sPathFullSize1 . ' | ' . $FileOutput . '<br />';
            ImageResize($type, $sPathFullSize1, $FileOutput); 
        }
    }
    
    startup();
    $sQueryUpdate = "UPDATE `Gallery` SET `gallery_status` = '$sChangeStatus' WHERE `gallery_id` = $iGalleryId;";
    $ResultSelect = mysql_query($sQueryUpdate) or die(mysql_error());
    if ($ResultSelect)
        echo 'updated';
    else
        echo 'not updated';
    mysql_close();
}

if (!$_POST['GetStatus'] == '')
{
    $sStatus = $_POST['GetStatus'];
    
    $sQuerySelect = '';
    if ($sStatus == 'new')
    {
        $sQuerySelect = "SELECT * FROM `Gallery` WHERE `gallery_status` = 'new'";
        $aContent = getSQLResults($sQuerySelect); 
        showTable($aContent);       
    }

    if ($sStatus == 'approved')
    {
        $sQuerySelect = "SELECT * FROM `Gallery` WHERE `gallery_status` = 'approved'";
        $aContent = getSQLResults($sQuerySelect); 
        showTable($aContent);        
    }
    
    if ($sStatus == 'cron')
    {
        $sQuerySelect = "SELECT * FROM `Gallery` WHERE `gallery_status` = 'cron'";
        $aContent = getSQLResults($sQuerySelect);        
        showTable($aContent);
    }        
    
    if ($sStatus == 'blocked')
    {
        $sQuerySelect = "SELECT * FROM `Gallery` WHERE `gallery_status` = 'blocked'";
        $aContent = getSQLResults($sQuerySelect);        
        showTable($aContent);
    }   

    if ($sStatus == 'trash')
    {
        $sQuerySelect = "SELECT * FROM `Gallery` WHERE `gallery_status` = 'trash'";
        $aContent = getSQLResults($sQuerySelect);        
        showTable($aContent);
    }        
}

if (!$_GET['gallery'] == '')
{
    $iGalleryId  = $_GET['gallery'];
    
    $sPathFullSize = _sPathFullSize_;
    $sPathThumbs150 = _sPathThumbs150_;
    $sPathThumbs800 = _sPathThumbs800_;
    
    echo 'Путь к галерее: ' . $sPathToFull = $sPathFullSize . $iGalleryId;
    echo '<br />';
    $aDirContent = scandir($sPathToFull);
    
    startup();
    $sQuerySelect = "SELECT * FROM `Gallery` WHERE `gallery_id` = $iGalleryId;";
    $ResultSelect = mysql_query($sQuerySelect) or die(mysql_error());
    while ($aResults[] = mysql_fetch_array($ResultSelect, MYSQL_ASSOC)) {}
    mysql_close(); 
    
    $sSeoTitle = $aResults[0]['gallery_seo_title'];
    $sSeoDescription = $aResults[0]['gallery_seo_description'];
    $sSeoKeywords = $aResults[0]['gallery_seo_keywords'];
    //print_r($aResults);
    //echo '<br />';
    
    echo '<form action="adminka.php" method="post" name="SeoTags">';
    echo '<input size="100" value="'. $sSeoTitle .'" type="text" name="SeoTitle" id="textfield"><br />';
    echo '<input size="100" value="'. $sSeoDescription .'" type="text" name="SeoDescription" id="textfield"><br />';
    echo '<input size="100" value="'. $sSeoKeywords .'" type="text" name="SeoKeywords" id="textfield"><br />';
    echo '<input type="hidden" name="SeoTags" value="' . $iGalleryId . '">';
    echo '<input type = "submit" name = "submit" value = "Update Seo tags">';
    echo '</form>';
    
    
    echo '<table border = "1">';
    for ($i = 2; $i < sizeof($aDirContent); $i++)
    {
        $sFullSize = '/gallery/full/' . $iGalleryId . '/' . $aDirContent[$i];
        $sPathToUnlink = $sPathFullSize . $iGalleryId . '/' . $aDirContent[$i];
        echo '<tr>';
        echo '<td width="300">' . $sFullSize . '</td>';
        echo '<td width="400"><form action="adminka.php" method="post"><input size="50" value="'. $sPathToUnlink .'" type="text" name="PathToDelete" id="textfield"><br><input type = "submit" name = "submit" value = "удалить"></form></td>';
        echo "<td><img src=\"" . $sFullSize . "\" width=\"500\"></td>";
        echo '</tr>';
    }
    echo '</table>';   
}
    
if (!$_POST['PathToDelete'] == '')
{
   $PathToDelete = $_POST['PathToDelete'];
    echo $PathToDelete;
    $bResult = unlink($PathToDelete);
/*    if ($bResult)
        echo 'deleted';
    else
        echo 'not deleted';*/
}

?>

</body>
</html>