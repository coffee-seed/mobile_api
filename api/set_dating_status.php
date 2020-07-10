 <?php
if(isset($_POST['session']) AND isset($_POST['token']) AND isset($_POST['use'])){
  include_once('fun.php');
  if($id=get_my_id($_POST['session'],$_POST['token'])){
    echo(set_dating_status($id,$_POST['use']));
  }
  else{
    echo "false";
  }
}
?>