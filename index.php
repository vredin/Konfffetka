<?php
//error_reporting(E_ALL);
ini_set('display_errors',1);
error_reporting(E_ALL ^E_NOTICE);

require('config.php');
require('functions.php');

    //print_r($_SERVER);
     
    if ($_SERVER['REQUEST_URI'] == '/')
    {
        $SplittedUrl[1] = 'home';  
        $SplittedUrl[2] = '1';
    }
    else
    {
        //echo '<br />';
        $SplittedUrl =  preg_split('#/#', $_SERVER['REQUEST_URI']);
        if ($SplittedUrl[2] == 1)
        {
            header("HTTP/1.1 301 Moved Permanently");
            header("Location: " . _BaseUrl_);  
        }   
    }
    
    //print_r($SplittedUrl);
        
    switch ($SplittedUrl[1]) 
    {
    case 'home':

        //include('theme/header.php'); //���������� �����, ��������� ��������
        require('theme/index.php');
        $aBlockAndFinalPage = GetPageContent(1);
        ShowPage($aBlockAndFinalPage, $SplittedUrl[2]);
        //include('theme/footer.php'); // ���������� ������
        break;    
    case 'gallery':
        //include('theme/header.php'); //���������� �����, ��������� ��������
        require('theme/single.php');
        //$aBlockAndFinalPage = GetPageContent(1);
        GalleryHitsIncrease($SplittedUrl[2]);
        ShowGallery($SplittedUrl[2]);
        //include('theme/footer.php'); // ���������� ������
        break;
    case 'user':
        echo "user";
        break;
    case 'tag':
        echo "tag";
        break;
    case 'page':
        // �������� ������� ShowPost($RequestedPage) � ������������ ��������������� ��������
        // $MainPart - ������ ��� ������� ����� ���������� ��� ��������
        include("theme/header.php"); //���������� �����, ��������� ��������
        include("theme/index.php");   // ���������� ����
        $aBlockAndFinalPage = GetPageContent($SplittedUrl[2]);
        ShowPage($aBlockAndFinalPage, $SplittedUrl[2]);
        include("theme/footer.php");  // ���������� ������
        break;
    case 'search':
        echo "search";
        break;
    default:
        header("http/1.0 404 Not found");
        echo "Nothing found";
    }
    

?>
