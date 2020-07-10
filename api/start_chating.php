<?php
  if(isset($_POST['session']) AND isset($_POST['token'])AND isset($_POST['user_id'])){
    include_once('fun.php');
    if($id=get_my_id($_POST['session'],$_POST['token'])){
      echo(create_chat_priv($id,$_POST['user_id']));
    }
  }
?>
