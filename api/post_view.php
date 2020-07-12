<?php
if(isset($_POST['session']) AND isset($_POST['token']) AND isset($_POST['post_id'])){
  include_once('fun.php');
  if($id=get_my_id($_POST['session'],$_POST['token'])){
    echo(post_view($_POST['post_id']));
  }
  else{
    echo "false";
  }
}
?>
