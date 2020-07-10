<?php
if(isset($_POST['session']) AND isset($_POST['token'])){
  include_once('fun.php');
  if($id=get_my_id($_POST['session'],$_POST['token'])){
    if(isset($_POST['sex']) AND isset($_POST['radius']) AND isset($_POST['max_b']) AND isset($_POST['min_b'])){
       echo get_dating($id,$_POST['radius'],$_POST['sex'],$_POST['min_b'],$_POST['max_b']);
    }
  }
  else{
    echo "false";
  }
}
?>
 
