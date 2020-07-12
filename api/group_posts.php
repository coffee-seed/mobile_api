 <?php
if(isset($_POST['session']) AND isset($_POST['token']) AND isset($_POST['group_id'])){
  include_once('fun.php');
  if($id=get_my_id($_POST['session'],$_POST['token'])){
    echo(group_posts($_POST['group_id']));
  }
  else{
    echo "false";
  }
}
?>  