<?php
if(isset($_POST['session']) AND isset($_POST['token'])){
  include_once('fun.php');
  if($id=get_my_id($_POST['session'],$_POST['token'])){
    if(isset($_POST['lat']) AND isset($_POST['lon'])){
      echo add_geo($id,$_POST['lat'],$_POST['lon']);
    }
  }
  else{
    echo "false";
  }
}
?>
