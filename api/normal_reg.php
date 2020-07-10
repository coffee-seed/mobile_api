<?php
if(isset($_POST['login']) AND isset($_POST['password']) AND isset($_POST['email'])AND isset($_POST['name'])AND isset($_POST['surname'])){
  include_once('fun.php');
  echo(normal_reg($_POST['login'],$_POST['email'],$_POST['password'],$_POST['name'],$_POST['surname']));
}
?>
