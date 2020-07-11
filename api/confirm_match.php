 <?php
if(isset($_POST['session']) AND isset($_POST['token']) AND isset($_POST['id']) AND isset($_POST['query'])){
  include_once('fun.php');
  if($id=get_my_id($_POST['session'],$_POST['token'])){
    echo(set_matches($id,$_POST['id'],$_POST['query']));
  }
  else{
    echo "false";
  }
}
?> 