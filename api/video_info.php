 <?php
if(isset($_POST['session']) AND isset($_POST['token']) AND isset($_POST['video_id'])){
  include_once('fun.php');
  if($id=get_my_id($_POST['session'],$_POST['token'])){
    echo(video_info($_POST['video_id']));
  }
  else{
    echo "false";
  }
}
?>  