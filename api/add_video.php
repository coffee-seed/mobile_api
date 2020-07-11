 <?php
if(isset($_POST['session']) AND isset($_POST['token']) AND isset($_FILES) AND isset($_POST['name']) AND isset($_POST['text'])){
  include_once('fun.php');
  if($id=get_my_id($_POST['session'],$_POST['token'])){
    	echo(add_video($id,$_POST['name'],$_POST['text'],$_FILES));
  }
  else{
    echo "false";
  }
}
?>