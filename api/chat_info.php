<?php
if(isset($_POST['session']) AND isset($_POST['token'])){
  include_once('fun.php');
  if($id=get_my_id($_POST['session'],$_POST['token'])){
    if(isset($_POST['chat_id'])){
      if(chat_validity($id,$_POST['chat_id'])){
        echo chat_info($_POST['chat_id']);
      }
      else{
        echo "false";
      }
    }
    else{
      echo "false";
    }
  }
  else{
    echo "false";
  }
}
?>
