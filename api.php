<?php
$serverip="http://192.168.1.18/";
if($_GET['uri']){
    echo file_get_contents($serverip.$_GET['uri']);
}else{
    echo file_get_contents($serverip."/");
}