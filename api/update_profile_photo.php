 <?php
if(isset($_POST['session']) AND isset($_POST['token']) AND isset($_FILES)){
  include_once('fun.php');
  if($id=get_my_id($_POST['session'],$_POST['token'])){
    	echo(update_profile_photo($id,$_POST['name'],$_FILES));
  }
  else{
    echo "false";
  }
}
?>