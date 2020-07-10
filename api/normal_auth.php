<?php
if(isset($_POST['login']) AND isset($_POST['password'])){
  include_once('fun.php');
  echo(normal_auth($_POST['login'],$_POST['password']));
}
?>
