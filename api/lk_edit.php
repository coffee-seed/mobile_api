<?php
if(isset($_POST['session']) AND isset($_POST['token'])){
  include_once('fun.php');
  if($id=get_my_id($_POST['session'],$_POST['token'])){
    if(isset($_POST['name']) AND isset($_POST['surname']) AND isset($_POST['middle_name']) AND isset($_POST['birthdate']) AND isset($_POST['sex']) AND isset($_POST['city']) AND isset($_POST['native_city']) AND isset($_POST['phone']) AND isset($_POST['email']) AND isset($_POST['study']) AND isset($_POST['job']) AND isset($_POST['bio'])){
      echo edit_user($_POST['name'],$_POST['surname'],$_POST['middle_name'],$_POST['birthdate'],$_POST['sex'],$_POST['city'],$_POST['native_city'],$_POST['phone'],$_POST['email'],$_POST['study'],$_POST['job'],$_POST['bio']);
    }
  }
  else{
    echo "false";
  }
}
?>
