<?php
    include_once('config.php');
    function my_connect(){
      global $mysql;
      return mysqli_connect($mysql['host'],$mysql['db_user'],$mysql['password'],$mysql['db_name']);
    }
    function normal_auth($login,$password){
      $link=my_connect();
      mysqli_real_escape_string($link, $login);
      $res=mysqli_query($link,"SELECT `id` FROM `users` WHERE `login`='".$login."' AND `password` ='".my_password_hash($password)."';");
      mysqli_close($link);
      if($re=mysqli_fetch_assoc($res)){
        return json_encode(get_auth_token($re['id']),JSON_UNESCAPED_UNICODE);
      }
      else{
        return "false";
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
    function chat_validity($user_id,$chat_id){
      $link=my_connect();
      $res=mysqli_query($link,"SELECT `chat_id` FROM `chat_members` WHERE `user_id`='".$user_id."' AND `chat_id`='".$chat_id."';");
      mysqli_close($link);
      if($re=mysqli_num_rows($res)>0){
        return true;
      }
      else{
        return false;
      }
    }
    function chat_members($chat_id,$uid=0){
      $link=my_connect();
      $res=mysqli_query($link,"SELECT `user_id` FROM `chat_members` WHERE `chat_id`='".$chat_id."' AND `user_id`<>".$uid.";");
      mysqli_close($link);
      if($re=mysqli_fetch_assoc($res)){
        $arr= array(0=>$re['user_id']);
        while($re=mysqli_fetch_assoc($res)){
          array_push($arr, $re['user_id']);
        }
        return json_encode($arr,JSON_UNESCAPED_UNICODE);
      }
      else{
        return false;
      }
    }
    function add_geo($user_id,$lat,$lon){
      $date = date("Y-m-d H:i:s");
      $link=my_connect();
      $res=mysqli_query($link,"INSERT INTO `geo` SET `user_id`=".$user_id.", `latitude`=".$lat.", `longitude`=".$lon.", `date`=".$date.";");
      mysqli_close($link);
    }
    function chat_messages($chat_id){
      $link=my_connect();
      $res=mysqli_query($link,"SELECT * FROM `chat_".$chat_id."_messages` ORDER BY `id` DESC LIMIT 100;");
      mysqli_close($link);
      if($re=mysqli_fetch_assoc($res)){
        $arr=array(0=>$re);
        while($re=mysqli_fetch_assoc($res)){
          array_push($arr,$re);
        }
        return json_encode($arr,JSON_UNESCAPED_UNICODE);
      }
      else{
        return "false";
      }
    }
    function chat_info($chat_id){
      $link=my_connect();
      $res=mysqli_query($link,"SELECT * FROM `chat` WHERE `id`=".$chat_id.";");
      mysqli_close($link);
      if($re=mysqli_fetch_assoc($res)){
        return json_encode($re,JSON_UNESCAPED_UNICODE);
      }
      else{
      	return false;
      }
    }
    function send_message($id,$chat_id,$text){
      $link=my_connect();
      $res=mysqli_query($link,"INSERT INTO `chat_".$chat_id."_messages` SET `sender_id`='".$id."',`text`='".$text."',`date`='".date("Y-m-d H:i:s")."';");
      mysqli_close($link);
      return true;
    }
    function user_chats($id){
      $arr=array();
      $link=my_connect();
      $res=mysqli_query($link,"SELECT `chat_id` FROM `chat_members` WHERE `user_id`='".$id."';");
      mysqli_close($link);
      while($re=mysqli_fetch_assoc($res)){
      array_push($arr,$re['chat_id']);
    }
    return json_encode($arr);
    
}    function create_chat_priv($id,$uid){
      $link=my_connect();
      $res=mysqli_query($link,"INSERT INTO `chat` SET `name`='priv' `private`=TRUE;");
      $chat_id=mysqli_insert_id($link);
      $res=mysqli_query($link,"CREATE TABLE `chat_".$chat_id."_messages` LIKE `chat_example_messages`;");
      $res=mysqli_query($link,"INSERT INTO `chat_members` SET `chat_id`='".$chat_id."' , `user_id`='".$id."';");
      $res=mysqli_query($link,"INSERT INTO `chat_members` SET `chat_id`='".$chat_id."' , `user_id`='".$uid."';");
      mysqli_close($link);
      return json_encode(array(0=>$chat_id));
    }
    function create_chat($id,$name){
      $link=my_connect();
      $res=mysqli_query($link,"INSERT INTO `chat` SET `name`='".$name."';");
      $chat_id=mysqli_insert_id($link);
      $res=mysqli_query($link,"CREATE TABLE `chat_".$chat_id."_messages` LIKE `chat_example_messages`;");
      $res=mysqli_query($link,"INSERT INTO `chat_members` SET `chat_id`='".$chat_id."' , `user_id`='".$id."';");
      mysqli_close($link);
      return json_encode(array(0=>$chat_id));
    }
    function add_chat_member($chat_id,$member_id){
      $link=my_connect();
      $res=mysqli_query($link,"SELECT `private` FROM `chat` WHERE `chat_id`='".$chat_id."';");
      $re=mysqli_fetch_assoc($res);
      if($re['private']==NULL OR $re['private']=='NULL' or $re['private']==false OR $re['private']==''){
      $res=mysqli_query($link,"INSERT INTO `chat_members` SET `chat_id`='".$chat_id."' , `user_id`='".$member_id."';");
    	  mysqli_close($link);
      	  return true;
  	  }
  	  else{
		  mysqli_close($link);
      	  return false;	  
  	  }
    }

    function user_data($id){
      $link=my_connect();
      $res=mysqli_query($link,"SELECT * FROM `users` WHERE `id`='".$id."';");
      mysqli_close($link);
      if($re=mysqli_fetch_assoc($res)){
          return json_encode($re,JSON_UNESCAPED_UNICODE);
      }
      else{
        return "false";
      }
    }
    function edit_user($id,$name,$surname,$middle_name,$birthdate,$sex,$city,$native_city,$phone,$email,$study,$job,$bio){
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
      $res=mysqli_query($link,"UPDATE `users` SET `name`='".$name."',`surname`='".$surname."',`middle_name`='".$middle_name."',`birthdate`='".$birthdate."', `sex`='".$sex."',`city`='".$city."',`native_city`='".$native_city."',`phone`='".$phone."',`email`='".$email."',`study`='".$study."',`job`='".$job."',`bio`='".$bio."' WHERE `id` =".$id.";");
      //echo"UPDATE `users` SET `name`='".$name."',`surname`='".$surname."',`middle_name`='".$middle_name."',`birthdate`='".$birthdate."', `sex`='".$sex."',`city`='".$city."',`native_city`='".$native_city."',`phone`='".$phone."',`email`='".$email."',`study`='".$study."',`job`='".$job."',`bio`='".$bio."' WHERE `id` =".$id.";";
      mysqli_close($link);
      return true;
    }
    function vk_auth($vk_id,$token){
      $ans=json_decode(file_get_contents("https://api.vk.com/method/users.get?v=5.120&access_token=".$token),true);
      $link=my_connect();
      if(isset($ans['response']['0']['id'])){
        if($ans['response']['0']['id']==$vk_id){
        mysqli_real_escape_string($link, $vk_id);
        mysqli_real_escape_string($link, $token);
        $res=mysqli_query($link,"SELECT `user_id` FROM `vk_auth` where `vk_id` =".$vk_id.";");
        mysqli_close($link);
        if(mysqli_num_rows($res)>0){
          return json_encode(get_auth_token(mysqli_fetch_assoc($res)['user_id']),JSON_UNESCAPED_UNICODE);
        }
        else{
          $id=vk_reg($vk_id, $ans['response']['0']['first_name'],$ans['response']['0']['last_name'],JSON_UNESCAPED_UNICODE);
          return json_encode(get_auth_token(($id)),JSON_UNESCAPED_UNICODE);
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
      $res=mysqli_query($link,"INSERT INTO `connect` SET `user_id`='".$user_id."', `session`='".$session."', `token`='".$token."';");
      return array('session'=>$session,'token'=>$token);
      mysqli_close($link);
    }
    function gen_token(){
      return hash('sha256', random_bytes(64));
    }
    function vk_reg($vk_id,$name,$surname){
      $link=my_connect();
      $res=mysqli_query($link,"INSERT INTO `users` SET `name`='".$name."', `surname`='".$surname."';");
      $ret=mysqli_insert_id($link);
      $res=mysqli_query($link,"INSERT INTO `vk_auth` SET `vk_id`='".$vk_id."', `user_id`='".$ret."';");
      mysqli_close($link);
      return $ret;
    }
    function my_password_hash($pass){
      $salt1='bla bla bla';
      $salt2='something';
      return hash('sha256',hash('sha256', $pass.$salt1).$salt2);
    }
    function normal_reg($login,$email,$password,$name,$surname){
      $link=my_connect();
      $res=mysqli_query($link,"INSERT INTO `users` SET `name`='".$name."', `surname`='".$surname."', `login`='".$login."',email='".$email."',password='".my_password_hash($password)."';");
      $ret=mysqli_insert_id($link);
      mysqli_close($link);
      return json_encode(get_auth_token(($ret)),JSON_UNESCAPED_UNICODE);;
    }
    /*
    */
    function set_dating_status($id,$use){
      $link=my_connect();
      $res=mysqli_query($link,"SELECT `use` FROM `dating` WHERE `user_id`='".$user_id."';");
      if(mysqli_num_rows($res)>0){
        $res=mysqli_query($link,"INSERT INTO `dating` SET `user_id`='".$user_id."',`use`='".$use."';");
      }
      else{
        $res=mysqli_query($link,"UPDATE `dating` SET `use`='".$use."' WHERE `user_id`='".$user_id."';");
      }
      mysqli_close($link);
      return true;
    }
    function dating_status($id){
      $link=my_connect();
      $res=mysqli_query($link,"SELECT `use` FROM `dating` WHERE `user_id`='".$user_id."';");
      mysqli_close($link);
      if($re=mysqli_fetch_assoc($res)){
        return $re['use'];
      }
      else{
        return false;
      }
    }
    function get_dating($id, $radius,$sex,$age){

    }
    function add_friend($mid,$uid){
      $link=my_connect();
      $res=mysqli_query($link,"SELECT * FROM `friends` WHERE `user_id`='".$mid."' AND  `friend_id`='".$uid."';");
      if($re=mysqli_fetch_assoc($res)){
         $res=mysqli_query($link,"UPDATE `friends` SET `status`=2 WHERE `user_id`='".$mid."' AND  `friend_id`='".$uid."';");
         $res=mysqli_query($link,"INSERT INTO `friends` SET `user_id`='".$uid."', `friend_id`='".$mid."',`status`=2;");
      }
      else{
         $res=mysqli_query($link,"INSERT INTO `friends` SET `user_id`='".$uid."', `friend_id`='".$mid."',`status`=1;");
      }
        mysqli_close($link);
        return true;
    }
    function show_friends($id){
      $res=mysqli_query($link,"SELECT * FROM `friends` WHERE `user_id`='".$mid."' AND  `friend_id`='".$uid."' AND status=2;");
      return json_encode(mysqli_fetch_assoc($res),JSON_UNESCAPED_UNICODE);
    }
    function show_friend_mb($id){
      $res=mysqli_query($link,"SELECT * FROM `friends` WHERE `user_id`='".$mid."' AND  `friend_id`='".$uid."' AND status=1;");
      return json_encode(mysqli_fetch_assoc($res),JSON_UNESCAPED_UNICODE);
    }
?>
