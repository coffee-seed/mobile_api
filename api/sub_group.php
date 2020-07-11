<?php
if(isset($_POST['session']) AND isset($_POST['token']) AND isset($_POST['query']) AND isset($_POST['group_id'])){
  include_once('fun.php');
  if($id=get_my_id($_POST['session'],$_POST['token'])){
    echo(sub_group($id,$_POST['group_id'],$_POST['query']));
  }
  else{
    echo "false";
  }
}
?> 
