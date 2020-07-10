<?php
if(isset($_POST['session']) AND isset($_POST['token'])){
  include_once('fun.php');
  if($id=get_my_id($_POST['session'],$_POST['token'])){
    if(isset($_POST['member_id'])){
        echo create_chat_priv($id,$_POST['member_id']);
    }
  }
  else{
    echo "false";
  }
}
?>
 
