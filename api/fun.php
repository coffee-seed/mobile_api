<?php
    include_once('config.php');
    function my_connect(){
      global $mysql;
      return mysqli_connect($mysql['host'],$mysql['db_user'],$mysql['password'],$mysql['db_name']);
    }
    function normal_auth($login,$password){
      $link=my_connect();
      mysqli_real_escape_string($link, $login);
        $res=mysqli_query($link,"SELECT `id` FROM `users` where `login`='".$login."' AND `password` =".password_hash($password).";");
      mysqli_close($link);
      if($re=mysqli_fetch_assoc($res)){
        return get_auth_token($re['id']);
      }
    }
    function get_my_id($session, $token){
      $link=my_connect();
      $res=mysqli_query($link,"SELECT `user_id` FROM `connect` WHERE `session`='".$session."' AND `token`='".$token."' ;");
      mysqli_close($link);
      if($re=mysqli_fetch_assoc($res)){
          return $re['user_id'];
      }
      else{
        return false;
      }
    }
    function user_chats($id){
      $link=my_connect();
      $res=mysqli_query($link,"SELECT `chat_id` FROM `chat_members` WHERE `user_id`='".$id."';");
      mysqli_close($link);
      $re=mysqli_fetch_assoc($res);
      return $re['user_id'];
    }
    function user_data($id){
      $link=my_connect();
      $res=mysqli_query($link,"SELECT * FROM `users` WHERE `id`='".$id."';");
      mysqli_close($link);
      if($re=mysqli_fetch_assoc($res)){
          return json_encode($re);
      }
      else{
        return "false";
      }
    }
    function edit_users($name,$surname,$middle_name,$birthdate,$sex,$city,$native_city,$phone,$email,$study,$job,$bio){
      $link=my_connect();
      mysqli_real_escape_string($link,$name);
      mysqli_real_escape_string($link,$surname);
      mysqli_real_escape_string($link,$middle_name);
      mysqli_real_escape_string($link,$birthdate);
      mysqli_real_escape_string($link,$sex);
      mysqli_real_escape_string($link,$city);
      mysqli_real_escape_string($link,$native_city);
      mysqli_real_escape_string($link,$phone);
      mysqli_real_escape_string($link,$email);
      mysqli_real_escape_string($link,$study);
      mysqli_real_escape_string($link,$job);
      mysqli_real_escape_string($link,$bio);
      $res=mysqli_query($link,"INSET INTO `users` SET `name`='".$name."',`surname`='".$surname."',`middle_name`='".$middle_name."',`birthdate`='".$birthdate."', `sex`='".$sex."',`city`='".$city."',`native_city`='".$native_city."',`phone`='".$phone."',`email`='".$email."',`study`='".$study."',`job`='".$job."',`bio`='".$bio."';");
      mysqli_close($link);
      return true;
    }
    function vk_auth($vk_id,$token){
      $ans=json_decode(file_get_contents("https://api.vk.com/method/users.get?v=5.120&access_token=".$token),true);
      if(isset($ans['response']['0']['id'])){
        if($ans['response']['0']['id']==$vk_id){
        $link=my_connect();
        mysqli_real_escape_string($link, $vk_id);
        mysqli_real_escape_string($link, $token);
        $res=mysqli_query($link,"SELECT `user_id` FROM `vk_auth` where `vk_id` =".$vk_id.";");
        mysqli_close($link);
        if(mysqli_num_rows($res)>0){
          return json_encode(get_auth_token(mysqli_fetch_assoc($res)['user_id']));
        }
        else{
          $id=vk_reg($vk_id, $ans['response']['0']['first_name'],$ans['response']['0']['last_name']);
          return json_encode(get_auth_token(mysqli_fetch_assoc($id)));
        }
        }
        else{
          return "false";
        }
      }
      else{
        return "false";
      }
    }
    function get_auth_token($user_id){
      $link=my_connect();
      $session=gen_token();
      $token=gen_token();
      $res=mysqli_query($link,"INSERT INTO `connect` SET `session`='".$session."', `token`='".$token."';");
      return array('session'=>$session,'token'=>$token);
      mysqli_close($link);
    }
    function gen_token(){
    }
    return hash('sha256', time());
    function vk_reg($vk_id,$name,$surname){
      $link=my_connect();
      $res=mysqli_query($link,"INSERT INTO `users` SET `name`='".$name."', `surname`='".$surname."';");
      $ret=mysqli_insert_id($link);
      $res=mysqli_query($link,"INSERT INTO `vk_auth` SET `vk_id`='".$vk_id."', `user_id`='".$ret."';");
      mysqli_close($link);
      return $ret;
    }
    function password_hash($pass){
      $salt1='bla bla bla';
      $salt2='something';
      return hash('sha256',hash('sha256', $pass.$salt1).$salt2);
    }
    function normal_reg($login,$email,$password,$name,$surname){
      $link=my_connect();
      $res=mysqli_query($link,"INSERT INTO `users` SET `name`='".$name."', `surname`='".$surname.", `login`='".$login."',email='".$email."',password='".password_hash($password)."';");
      $ret=mysqli_insert_id($link);
      mysqli_close($link);
      return get_auth_token($ret);
    }
?>
