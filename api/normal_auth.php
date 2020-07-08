<?php
if(isset($_POST['login']) AND isset($_POST['password'])){
  include_once('fun.php');
  echo(nornal_auth($_POST['login'],$_POST['password']);
}
?>
