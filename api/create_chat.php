<?php
  if(isset($_POST['session']) AND isset($_POST['token'])AND isset($_POST['name'])){
    include_once('fun.php');
    if($id=get_my_id($_POST['session'],$_POST['token'])){
      echo(create_chat($id,$_POST['name']));
    }
    else{
      echo "auth_fail";
    }
  }
  else{
    echo "ne_params";
  }
?>
