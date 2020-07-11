<?php
  if(isset($_POST['session']) AND isset($_POST['token']) AND isset($_POST['group_id']) AND isset($_POST['text'])){
    include_once('fun.php');
    if($id=get_my_id($_POST['session'],$_POST['token'])){
      echo(create_post($id,$_POST['group_id'],$_POST['text']));
    }
    else{
      echo "auth_fail";
    }
  }
  else{
    echo "ne_params";
  }
?>
