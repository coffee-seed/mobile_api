<?php
if(isset($_POST['session']) AND isset($_POST['token'])AND isset($_POST['friend_id'])){
  include_once('fun.php');
  if($id=get_my_id($_POST['session'],$_POST['token'])){
    echo(add_friend($id,$_POST['friend_id']));
  }
  else{
    echo "false";
  }
}
?>
