<?php
if(isset($_POST['session']) AND isset($_POST['token'])){
  include_once('fun.php');
  if($id=get_my_id($_POST['session'],$_POST['token'])){
    if(isset($_POST['sex']) AND isset($_POST['radius']) AND isset($_POST['max_b']) AND isset($_POST['min_b'])){
       $min =(date("Y")-$_POST['min_b']).date("-m-d");
       $max =(date("Y")-$_POST['max_b']).date("-m-d");
       echo get_dating($id,$_POST['radius']/111*2,$_POST['sex'],$max,$min);
    }
  }
  else{
    echo "false";
  }
}
?>
 
