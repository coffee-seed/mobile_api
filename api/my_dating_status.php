 <?php
if(isset($_POST['session']) AND isset($_POST['token'])){
  include_once('fun.php');
  if($id=get_my_id($_POST['session'],$_POST['token'])){
    echo(dating_status($id));
  }
  else{
    echo "false";
  }
}
?>