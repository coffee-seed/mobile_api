 <?php
if(isset($_POST['session']) AND iiset($_POST['group_id']) AND isset($_POST['token']) AND isset($_FILES)){
  include_once('fun.php');
  if($id=get_my_id($_POST['session'],$_POST['token'])){
    	echo(update_group_photo($_POST['group_id'],$_POST['name'],$_FILES));
  }
  else{
    echo "false";
  }
}
?>