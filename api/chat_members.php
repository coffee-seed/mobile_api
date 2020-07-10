<?php
  if(isset($_POST['session']) AND isset($_POST['token']) AND isset($_POST['chat_id'])){
    include_once('fun.php');
    if($id=get_my_id($_POST['session'],$_POST['token'])){
      if(chat_validity($id,$_POST['chat_id'])){
        echo(chat_members($_POST['chat_id'],$id));
      }
      else{
        echo "false";
      }
    }
  }
?>
