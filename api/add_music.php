 <?php
if(isset($_POST['session']) AND isset($_POST['token']) AND isset($_FILES) AND isset($_POST['name'])  AND isset($_POST['author'])){
  include_once('fun.php');
  if($id=get_my_id($_POST['session'],$_POST['token'])){
    echo(add_music($id,$_POST['name'],$_POST['author'],$_FILES));
  }
  else{
    echo "false";
  }
}
?>