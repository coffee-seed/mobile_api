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
      $res=mysqli_query($link,"INSERT INTO `geo` SET `user_id`=".$user_id.", `latitude`=".$lat.", `longitude`=".$lon.", `date`='".$date."';");
      $res=mysqli_query($link,"SELECT * FROM `last_geo` WHERE `user_id`=".$user_id.";");
      if(mysqli_num_rows($res)>0){
      	 $res=mysqli_query($link,"UPDATE `last_geo` SET `latitude`=".$lat.", `longitude`=".$lon." WHERE `user_id`=".$user_id.";");
      }
      else{
      	$res=mysqli_query($link,"INSERT INTO `last_geo` SET `user_id`=".$user_id.", `latitude`=".$lat.", `longitude`=".$lon.";");
      }
      mysqli_close($link);
      return true;
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
      $res=mysqli_query($link,"SELECT `chat`.`id` FROM `chat` INNER JOIN `chat_members` ON `chat_members`.`chat_id`=`chat`.`id`  WHERE `chat`.`private`=1 AND `chat_members`.`user_id`=".$id." INTERSECT SELECT `chat`.`id` FROM `chat` INNER JOIN `chat_members` ON `chat_members`.`chat_id`=`chat`.`id`  WHERE `chat`.`private`=1 AND `chat_members`.`user_id`=".$uid.";");
      
      if($re=mysqli_fetch_assoc($res)){
      	  return json_encode(array(0=>$re['id']));
      }
      else{
	      $res=mysqli_query($link,"INSERT INTO `chat` SET `name`='priv',`private`=1;");
	      $chat_id=mysqli_insert_id($link);
	      $res=mysqli_query($link,"CREATE TABLE `chat_".$chat_id."_messages` LIKE `chat_example_messages`;");
	      $res=mysqli_query($link,"INSERT INTO `chat_members` SET `chat_id`='".$chat_id."' , `user_id`='".$id."';");
	      $res=mysqli_query($link,"INSERT INTO `chat_members` SET `chat_id`='".$chat_id."' , `user_id`='".$uid."';");
	      mysqli_close($link);
	      return json_encode(array(0=>$chat_id));
	  }
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
      $res=mysqli_query($link,"SELECT `id`,`birthdate`, `name`,`surname` ,`middle_name`, `photo` , `email`, `phone`, `birthdate`, `country`, `language`, `sex`, `city`,`native_city`, `status`, `bio`, `study`,`job` FROM `users`  WHERE `id`='".$id."';");
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
      $ans=json_decode(file_get_contents("https://api.vk.com/method/users.get?fields=photo_400_orig,sex,bdate,city,home_town&v=5.120&access_token=".$token),true);
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
          $name =$ans['response']['0']['first_name'];
          $surname=$ans['response']['0']['last_name'];
          if(isset($ans['response']['0']['photo_400_orig'])){
            $photo=$ans['response']['0']['photo_400_orig'];
          }
          else{
          	$photo=NULL;
          }
          if(isset($ans['response']['0']['bdate'])){
            $bdate=$ans['response']['0']['bdate'];
          }
          else{
          	$bdate=NULL;
          }
          if(isset($ans['response']['0']['city'])){
            $city=$ans['response']['0']['city']['title'];
          }
          else{
          	$city=NULL;
          }
          if(isset($ans['response']['0']['home_town'])){
            $home_town=$ans['response']['0']['home_town'];
          }
          else{
          	$home_town=NULL;
          }
          if(isset($ans['response']['0']['sex'])){
          	if($ans['response']['0']['sex']==2){
          		$sex=1;
          	}
          	elseif($ans['response']['0']['sex']==1){
          		$sex=2;
          	}
          	else{
          		$sex=NULL;
          	}
          }
          else{
          	$sex=NULL;
          }
          $id=vk_reg($vk_id,$name,$surname,$photo,$city,$home_town,$bdate,$sex,JSON_UNESCAPED_UNICODE);
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
    function vk_reg($vk_id,$name,$surname,$photo,$city,$home_town,$bdate,$sex){
      $link=my_connect();
      $res=mysqli_query($link,"INSERT INTO `users` SET `name`='".$name."', `surname`='".$surname."',`sex`=".$sex." ,`photo`='".$photo."',`city`='".$city."',`native_city`='".$home_town."',`birthdate`='".$bdate."';");
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
    function set_dating_status($user_id,$use){
      $link=my_connect();
      $res=mysqli_query($link,"SELECT `use` FROM `dating` WHERE `user_id`='".$user_id."';");
      if(mysqli_num_rows($res)<=0){
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
      $res=mysqli_query($link,"SELECT `use` FROM `dating` WHERE `user_id`='".$id."';");
      mysqli_close($link);
      if($re=mysqli_fetch_assoc($res)){
        return json_encode(array(0=>$re['use']));
      }
      else{
        return json_encode(array(0=>NULL));
      }
    }
    function get_dating($my_id,$radius,$sex,$min_b,$max_b){
      $link=my_connect();
      $res=mysqli_query($link, "SELECT `latitude`, `longitude` FROM `last_geo` WHERE `user_id`=".$my_id.";");
      if($re=mysqli_fetch_assoc($res)){
	      $ress=mysqli_query($link, "SELECT `users`.`id` `id` `last_geo`.`id` `lat`,`last_geo`.'longitude' 'lon'  from `users` INNER JOIN `dating` ON `dating`.`user_id`=`users`.`id` INNER JOIN `last_geo` ON `last_geo`.`user_id`=`users`.`id` LEFT JOIN `match`  ON (`match`.`user1`=`users`.`id` AND  `match`.`user2`=".$my_id.") OR (`match`.`user1`=".$my_id." AND  `match`.`user2`=`users`.`id`) LEFT JOIN `unmatch` ON (`unmatch`.`user1`=`users`.`id` AND `unmatch`.`user2`=".$my_id.") OR (`unmatch`.`user2`=`users`.`id` AND `unmatch`.`user1`=".$my_id.") LEFT JOIN `try_match`  ON `try_match`.`for_id`=`users`.`id` AND `try_match`.`try_id`=".$my_id." WHERE `dating`.`use`>0 AND `last_geo`.`latitude`>".$re['latitude']-$radius." AND `last_geo`.`latitude`<".$re['latitude']+$radius." AND `last_geo`.`longitude`>".$re['longitude']-$radius." AND `last_geo`.`longitude`<".$re['longitude']+$radius." AND `users`.`birthdate`>".$min_b." AND `users`.`birthdate`<".$max_b." AND `users`.`id`<>".$id." AND `users`.`sex`=".$sex.";"); 
	    $arr=array();
	    while($re=mysqli_fetch_assoc($ress)){
	    	$$radius=ceil(12745594 * asin(sqrt(
        pow(sin(deg2rad($re['lat']-$re['latitude'])/2),2)
        +
        cos(deg2rad($re['latitude'])) *
        cos(deg2rad($re['lat'])) *
        pow(sin(deg2rad($re['longitude'] -$re['lon'])/2),2))));
	    	array_push($arr,array('id'=>$re['id'],'radius'=>$radius));
	    }
	    return json_encode($arr);
	  }
	  else{
	  	return "err_need_geo";
	  }
    }
    function add_friend($mid,$uid){
      if($mid!=$uid){
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
	    else{
	    	return "false";
	    }
    }
    function show_friends($id){
      $link=my_connect();
      $res=mysqli_query($link,"SELECT * FROM `friends` WHERE `user_id`='".$id."'AND status=2;");
      $arr=array();
      while($re=mysqli_fetch_assoc($res)){
      	array_push($arr,$re['friend_id']);
      }
      mysqli_close($link);
      return json_encode($arr,JSON_UNESCAPED_UNICODE);
    }
    function show_friend_mb($id){
      $link=my_connect();
      $res=mysqli_query($link,"SELECT * FROM `friends` WHERE `user_id`='".$id."' AND status=1;");
      $arr=array();
      while($re=mysqli_fetch_assoc($res)){
      	array_push($arr,$re['friend_id']);
      }
      mysqli_close($link);
      return json_encode($arr,JSON_UNESCAPED_UNICODE);
    }
 	function users_search($query,$uid=false){
 	  $link=my_connect();
      $res=mysqli_query($link,"SELECT `id` FROM `users` WHERE (`name`='".$query."' OR `surname`='".$query."') AND `id`<>".$uid.";");
      mysqli_close($link);
      if($re=mysqli_fetch_assoc($res)){
      	$arr=array(0=>$re['id']);
      	while($re=mysqli_fetch_assoc($res)){
      		array_push($re,$re['id']);
      	}
        return json_encode($arr);
      }
      else{
        return false;
      }
 	}
?>
