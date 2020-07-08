<?php
  if(isset($_POST['vk_id']) AND isset($_POST['token'])){
    include_once('fun.php');
    echo(vk_auth($_POST['vk_id'],$_POST['token']));
  }
  //file_put_contents('log.txt',json_encode($_POST));
?>
