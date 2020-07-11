<?php
if(isset($_POST['session']) AND isset($_POST['token']) AND isset($_POST['name']) AND isset($_POST['text']) AND isset($_POST['group_id'])){
  include_once('fun.php');
  if($id=get_my_id($_POST['session'],$_POST['token'])){
    echo(search_groups($id,$_POST['group_id'],$_POST['name'],$_POST['text']));
  }
  else{
    echo "false";
  }
}
?>