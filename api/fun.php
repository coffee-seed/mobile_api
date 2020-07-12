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
      mysqli_real_escape_string($link, $session);
      mysqli_real_escape_string($link, $token);
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
      mysqli_real_escape_string($link, $user_id);
      mysqli_real_escape_string($link, $chat_id);
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
      mysqli_real_escape_string($link, $chat_id);
      mysqli_real_escape_string($link, $uid);
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
      mysqli_real_escape_string($link, $user_id);
      mysqli_real_escape_string($link, $lat);
      mysqli_real_escape_string($link, $lon);
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
      mysqli_real_escape_string($link, $chat_id);
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
      mysqli_real_escape_string($link, $chat_id);
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
      mysqli_real_escape_string($link, $id);
      mysqli_real_escape_string($link, $chat_id);
      mysqli_real_escape_string($link, $text);
      $res=mysqli_query($link,"INSERT INTO `chat_".$chat_id."_messages` SET `sender_id`='".$id."',`text`='".$text."',`date`='".date("Y-m-d H:i:s")."';");
      mysqli_close($link);
      return true;
    }
    function user_chats($id){
      $arr=array();
      $link=my_connect();
      mysqli_real_escape_string($link, $id);
      $res=mysqli_query($link,"SELECT `chat_id` FROM `chat_members` WHERE `user_id`='".$id."';");
      mysqli_close($link);
      	while($re=mysqli_fetch_assoc($res)){
      	array_push($arr,$re['chat_id']);
      }
      return json_encode($arr);
	}    
	function create_chat_priv($id,$uid){
      $link=my_connect();
      mysqli_real_escape_string($link, $id);
      mysqli_real_escape_string($link, $uid);
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
      mysqli_real_escape_string($link, $id);
      mysqli_real_escape_string($link, $name);
      $res=mysqli_query($link,"INSERT INTO `chat` SET `name`='".$name."';");
      $chat_id=mysqli_insert_id($link);
      $res=mysqli_query($link,"CREATE TABLE `chat_".$chat_id."_messages` LIKE `chat_example_messages`;");
      $res=mysqli_query($link,"INSERT INTO `chat_members` SET `chat_id`='".$chat_id."' , `user_id`='".$id."';");
      mysqli_close($link);
      return json_encode(array(0=>$chat_id));
    }
    function add_chat_member($chat_id,$member_id){
      $link=my_connect();
      mysqli_real_escape_string($link, $chat_id);
      mysqli_real_escape_string($link, $member_id);
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
      mysqli_real_escape_string($link, $id);
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
      mysqli_close($link);
      return true;
    }
    function vk_auth($vk_id,$token){
      $ans=json_decode(file_get_contents("https://api.vk.com/method/users.get?fields=photo_400_orig,sex,bdate,city,home_town&v=5.120&access_token=".$token),true);
      $link=my_connect();
      mysqli_real_escape_string($link, $vk_id);
      mysqli_real_escape_string($link, $token);
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
            $bdate=ate('Y-m-d', strtotime($ans['response']['0']['bdate']));
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
      mysqli_real_escape_string($link, $user_id);
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
      mysqli_real_escape_string($link, $vk_id);
      mysqli_real_escape_string($link, $name);
      mysqli_real_escape_string($link, $surname);
      mysqli_real_escape_string($link, $photo);
      mysqli_real_escape_string($link, $city);
      mysqli_real_escape_string($link, $home_town);
      mysqli_real_escape_string($link, $bdate);
      mysqli_real_escape_string($link, $sex);
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
      mysqli_real_escape_string($link, $login);
      mysqli_real_escape_string($link, $email);
      mysqli_real_escape_string($link, $password);
      mysqli_real_escape_string($link, $name);
      mysqli_real_escape_string($link, $surname);
      $res=mysqli_query($link,"INSERT INTO `users` SET `name`='".$name."', `surname`='".$surname."', `login`='".$login."',email='".$email."',password='".my_password_hash($password)."';");
      $ret=mysqli_insert_id($link);
      mysqli_close($link);
      return json_encode(get_auth_token(($ret)),JSON_UNESCAPED_UNICODE);;
    }
    function set_dating_status($user_id,$use){
      $link=my_connect();
      mysqli_real_escape_string($link, $user_id);
      mysqli_real_escape_string($link, $use);
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
      mysqli_real_escape_string($link, $id);
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
      mysqli_real_escape_string($link, $my_id);
      mysqli_real_escape_string($link, $radius);
      mysqli_real_escape_string($link, $sex);
      mysqli_real_escape_string($link, $min_b);
      mysqli_real_escape_string($link, $max_b);
      $res=mysqli_query($link, "SELECT `latitude`, `longitude` FROM `last_geo` WHERE `user_id`=".$my_id.";");
      if($ree=mysqli_fetch_assoc($res)){
	      $ress=mysqli_query($link, "SELECT `users`.`id` `id`, `last_geo`.`latitude` `lat`,`last_geo`.`longitude` `lon`  from `users` INNER JOIN `dating` ON `dating`.`user_id`=`users`.`id` INNER JOIN `last_geo` ON `last_geo`.`user_id`=`users`.`id` LEFT JOIN `match`  ON (`match`.`user1`=`users`.`id` AND  `match`.`user2`=".$my_id.") OR (`match`.`user1`=".$my_id." AND  `match`.`user2`=`users`.`id`) LEFT JOIN `unmatch` ON (`unmatch`.`user1`=`users`.`id` AND `unmatch`.`user2`=".$my_id.") OR (`unmatch`.`user2`=`users`.`id` AND `unmatch`.`user1`=".$my_id.") LEFT JOIN `try_match`  ON `try_match`.`for_id`=`users`.`id` AND `try_match`.`try_id`=".$my_id." WHERE `dating`.`use`>0 AND `last_geo`.`latitude`>".
	      	floatval(floatval($ree['latitude'])-floatval($radius))." AND `last_geo`.`latitude`<".floatval(floatval($ree['latitude'])+floatval($radius))." AND `last_geo`.`longitude`>".floatval(floatval($ree['longitude'])-floatval($radius))." AND `last_geo`.`longitude`<".floatval(floatval($ree['longitude'])+floatval($radius))." AND `users`.`birthdate`>'".$min_b."' AND `users`.`birthdate`<'".$max_b."' AND `users`.`id`<>".$my_id." AND `users`.`sex`=".$sex.";");
	    $arr=array();
	    while($re=mysqli_fetch_assoc($ress)){
	    	$radius=ceil(12745594 * asin(sqrt(
        pow(sin(deg2rad($re['lat']-$ree['latitude'])/2),2)
        +
        cos(deg2rad($ree['latitude'])) *
        cos(deg2rad($re['lat'])) *
        pow(sin(deg2rad($ree['longitude'] -$re['lon'])/2),2))));
	    	array_push($arr,array('id'=>$re['id'],'radius'=>$radius));
	    }
	    return json_encode($arr);
	  }
	  else{
	  	return "err_need_geo";
	  }
    }
    function get_matches($id){
      $link=my_connect();
      mysqli_real_escape_string($link, $id);
      $arr=array();
      $res=mysqli_query($link,"SELECT `user1` `id` FROM `match` WHERE `user2`='".$id."';");
	  while($re=mysqli_fetch_assoc($res)){
       	array_push($arr, $re['id']);
      }
      $res=mysqli_query($link,"SELECT `user2` `id` FROM `match` WHERE `user1`='".$id."';");
      mysqli_close($link);
      while($re=mysqli_fetch_assoc($res)){
       	array_push($arr, $re['id']);
      }
      return json_encode($arr);
    }
    function set_matches($uid,$mid,$query){
      $link=my_connect();
      mysqli_real_escape_string($link, $uid);
      mysqli_real_escape_string($link, $mid);
      mysqli_real_escape_string($link, $query);
      if($query){
	      $res=mysqli_query($link,"SELECT `id` FROM `try_match` WHERE `try_id`=".$mid." AND  `for_id`='".$uid."';");
	      if($re=mysqli_fetch_assoc($res)){
	      	$res=mysqli_query($link,"DELETE FROM `try_match` WHERE `for_id`=".$uid." AND `try_id`=".$mid.";");
	      	$res=mysqli_query($link,"DELETE FROM `try_match` WHERE `for_id`=".$mid." AND `try_id`=".$uid.";");
	        $res=mysqli_query($link,"INSERT INTO `match` SET `user1`='".$uid."',`user2`='".$mid."';");
	  	  }
	  	  else{
	  	  	$res=mysqli_query($link,"INSERT INTO `try_match` SET `try_id`=".$uid.", `for_id`='".$mid."';");
	  	  }
  	  }
  	  else{
  	  	  $res=mysqli_query($link,"INSERT INTO `unmatch` SET `user1`='".$uid."',`user2`='".$mid."';");
  	  }
      mysqli_close($link);
      return "true";
    }
    function add_music($id,$name,$author,$files){
		if(substr($files['music']['name'],strlen($files['music']['name'])-4,strlen($files['music']['name'])-1)==".mp3" AND substr($files['photo']['name'],strlen($files['photo']['name'])-4,strlen($files['photo']['name'])-1)==".png") { 
	    	$f=gen_token();
	    	move_uploaded_file($files['music']['tmp_name'], '../music/'.$f.".mp3");
	    	$ff=gen_token();
	    	move_uploaded_file($files['photo']['tmp_name'], '../img/'. $ff.".png");
	    	$link=my_connect();
	    	mysqli_real_escape_string($link, $id);
	    	mysqli_real_escape_string($link, $name);
	    	mysqli_real_escape_string($link, $author);
	        $res=mysqli_query($link,"INSERT INTO `music` SET `author_name`='".$author."',`name`='".$name."',`photo`='https://salamport.newpage.xyz/img/".$ff.".png' , `file`='https://salamport.newpage.xyz/music/".$f.".mp3';");
	        mysqli_close($link);
			    return "true";
			} else {
			    return "false";
			}
    }
    function add_friend($mid,$uid){
      if($mid!=$uid){
	      $link=my_connect();
	      mysqli_real_escape_string($link, $mid);
	      mysqli_real_escape_string($link, $uid);
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
	    	mysqli_close($link);
	    	return "false";
	    }
    }
    function show_friends($id){
      $link=my_connect();
      mysqli_real_escape_string($link, $id);
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
      mysqli_real_escape_string($link, $id);
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
 	  mysqli_real_escape_string($link, $query);
 	  mysqli_real_escape_string($link, $uid);
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
 	function show_all_music(){
 		$link=my_connect();
 		$res=mysqli_query($link,"SELECT `id` FROM `music` ORDER BY `id` DESC LIMIT 100 ;");
 		$arr=array();
      	while($re=mysqli_fetch_assoc($res)){
      		array_push($arr,$re['id']);
      	}
      	mysqli_close($link);
        return json_encode($arr);
 	}
 	function music_info($id){
 		$link=my_connect();
 		mysqli_real_escape_string($link, $id);
 		$res=mysqli_query($link,"SELECT * FROM `music` WHERE `id`=".$id.";");
 		mysqli_close($link);
      	if($re=mysqli_fetch_assoc($res)){
      		return json_encode($re);
      	}
      	else{
        	return false;
        }
 	}
 	function search_music($name){
 		$link=my_connect();
 		mysqli_real_escape_string($link, $name);
 		$res=mysqli_query($link,"SELECT `id` FROM `music` WHERE `name` LIKE '%".$name."%' LIMIT 100 ;");
 		$arr=array();
      	while($re=mysqli_fetch_assoc($res)){
      		array_push($arr,$re['id']);
      	}
      	mysqli_close($link);
        return json_encode($arr);
 	}
 	function add_video($id,$name,$text,$files){
		if(substr($files['video']['name'],strlen($files['video']['name'])-4,strlen($files['video']['name'])-1)==".mp4") { 
	    $f=gen_token();
	    move_uploaded_file($files['video']['tmp_name'], '../video/'.$f.".mp4");
	    $link=my_connect();
	    mysqli_real_escape_string($link, $id);
	    mysqli_real_escape_string($link, $name);
	    mysqli_real_escape_string($link, $text);
	    $res=mysqli_query($link,"INSERT INTO `videos` SET `author`='".$id."',`text`='".$text."',`name`='".$name."', `file`='https://salamport.newpage.xyz/video/".$f.".mp4';");
	    mysqli_close($link);
		    return "true";
		} 
		else {
			return "false";
		}
    }
    function show_all_video(){
 		$link=my_connect();
 		$res=mysqli_query($link,"SELECT `id` FROM `videos` ORDER BY `id` DESC LIMIT 100 ;");
 		$arr=array();
      	while($re=mysqli_fetch_assoc($res)){
      		array_push($arr,$re['id']);
      	}
      	mysqli_close($link);
        return json_encode($arr);
 	}
 	function video_info($id){
 		$link=my_connect();
 		mysqli_real_escape_string($link, $id);
 		$res=mysqli_query($link,"SELECT * FROM `videos` WHERE `id`=".$id.";");
 		mysqli_close($link);
      	if($re=mysqli_fetch_assoc($res)){
      		return json_encode($re);
      	}
      	else{
        	return false;
        }
 	}
 	function search_video($name){
 		$link=my_connect();
 		mysqli_real_escape_string($link, $name);
 		$res=mysqli_query($link,"SELECT `id` FROM `videos` WHERE `name` LIKE '%".$name."%' LIMIT 100 ;");
 		$arr=array();
      	while($re=mysqli_fetch_assoc($res)){
      		array_push($arr,$re['id']);
      	}
      	mysqli_close($link);
        return json_encode($arr);
 	}
 	function update_profile_photo($id,$files){
		if(substr($files['photo']['name'],strlen($files['photo']['name'])-4,strlen($files['photo']['name'])-1)==".png") { 
	    $f=gen_token();
	    move_uploaded_file($files['photo']['tmp_name'], '../photo/'.$f.".png");
	    $link=my_connect();
	    mysqli_real_escape_string($link, $id);
	    $res=mysqli_query($link,"UPDATE `users` SET `photo`='https://salamport.newpage.xyz/photo/".$f.".png';");
	    mysqli_close($link);
		    return "true";
		} 
		else {
			return "false";
		}
    }
 	function create_group($id,$name){
 	  $link=my_connect();
 	  mysqli_real_escape_string($link, $id);
 	  mysqli_real_escape_string($link, $name);
      $res=mysqli_query($link,"INSERT INTO `groups` SET `name`='".$name."', `author`='".$id."';");
      $ret=mysqli_insert_id($link);
      mysqli_close($link);
      return $ret;
 	}
 	function group_info($group_id){
 		$link=my_connect();
 		mysqli_real_escape_string($link, $group_id);
 		$res=mysqli_query($link,"SELECT * FROM `groups` WHERE `id`=".$group_id.";");
 		mysqli_close($link);
      	if($re=mysqli_fetch_assoc($res)){
      		return json_encode($re,JSON_UNESCAPED_UNICODE);
      	}
      	else{
        	return false;
        }		
 	}
 	function group_list(){
 		$link=my_connect();
 		$res=mysqli_query($link,"SELECT `id` FROM `videos` ORDER BY `id` DESC LIMIT 100 ;");
 		$arr=array();
      	while($re=mysqli_fetch_assoc($res)){
      		array_push($arr,$re['id']);
      	}
      	mysqli_close($link);
        return json_encode($arr);
 	}
 	function update_group_photo($id,$files){
		if(substr($files['photo']['name'],strlen($files['photo']['name'])-4,strlen($files['photo']['name'])-1)==".png") { 
	    $f=gen_token();
	    move_uploaded_file($files['photo']['tmp_name'], '../photo/'.$f.".png");
	    $link=my_connect();
	    mysqli_real_escape_string($link, $id);
	    $res=mysqli_query($link,"UPDATE `groups` SET `photo`='https://salamport.newpage.xyz/photo/".$f.".png';");
	    mysqli_close($link);
		    return "true";
		} 
		else {
			return "false";
		}
    }
    function search_groups($name){
 		$link=my_connect();
 		mysqli_real_escape_string($link, $name);
 		$res=mysqli_query($link,"SELECT `id` FROM `groups` WHERE `name` LIKE '%".$name."%' LIMIT 100 ;");
 		$arr=array();
      	while($re=mysqli_fetch_assoc($res)){
      		array_push($arr,$re['id']);
      	}
      	mysqli_close($link);
        return json_encode($arr);
 	}
 	function edit_groups($id,$gid,$name,$text){
 		$link=my_connect();
 		mysqli_real_escape_string($link, $id);
 		mysqli_real_escape_string($link, $gid);
 		mysqli_real_escape_string($link, $name);
 		mysqli_real_escape_string($link, $text);
 		if(check_group_owner($id,$gid)){
 			$res=mysqli_query($link,"INSERT INTO `groups` SET `text`='".$text."', `name`='".$name."', WHERE `id`='".$id."';");
 		}
 		mysqli_close($link);
        return json_encode($arr);
 	}
 	function sub_group($id,$gid,$query){
 		$link=my_connect();
 		mysqli_real_escape_string($link, $id);
 		mysqli_real_escape_string($link, $gid);
 		mysqli_real_escape_string($link, $query);
 		$res=mysqli_query($link,"SELECT `id` FROM `groups_members` WHERE `member_id` ='".$id."' AND `group_id`='".$gid."';");
 		if(mysqli_num_rows($res)>0){
 			if($query){
 				return false;
 			}
 			else{
 				$res=mysqli_query($link,"DELETE FROM `groups_members` WHERE `member_id`='".$id."' AND `groupt_id`='".$gid."';");
 				return true;
 			}
 		}
 		else{
 			if($query){
 				$res=mysqli_query($link,"INSERT INTO `groups_members` SET `member_id`='".$id."',`group_id`='".$gid."';");
 				return true;
 			}
 			else{
 				return false;
 			}
 		}
 	}
 	function check_group_owner($id,$gid){
 		$link=my_connect();
 		mysqli_real_escape_string($link, $id);
 		mysqli_real_escape_string($link, $gid);
 		$res=mysqli_query($link,"SELECT `id` FROM `groups` WHERE `author` ='".$id."' AND `id`='".$gid."';");
 		if(mysqli_num_rows($res)>0){
 			return true;
 		}
 		else{
 			return false;
 		}
 	}
 	function show_subs($id){
 		$link=my_connect();
 		mysqli_real_escape_string($link, $id);
 		$res=mysqli_query($link,"SELECT `group_id` `id` FROM `groups_members` WHERE `member_id` ='".$id."';");
 		$arr=array();
      	while($re=mysqli_fetch_assoc($res)){
      		array_push($arr,$re['id']);
      	}
      	mysqli_close($link);
        return json_encode($arr);
 	}
 	function show_followers($id){
 		$link=my_connect();
 		mysqli_real_escape_string($link, $id);
 		$res=mysqli_query($link,"SELECT `chat_id` FROM `groups_members` WHERE `member_id` ='".$id."';");
 		$arr=array();
      	while($re=mysqli_fetch_assoc($res)){
      		array_push($arr,$re['id']);
      	}
      	mysqli_close($link);
        return json_encode($arr);
 	}
 	function create_post($id,$gid,$text){
 		if(check_group_owner($id,$gid)){
 			$link=my_connect();
 			mysqli_real_escape_string($link, $id);
 			mysqli_real_escape_string($link, $gid);
 			mysqli_real_escape_string($link, $text);
 			$res=mysqli_query($link,"INSERT INTO `posts` SET `group_id`='".$gid."',`member_id`='".$id."',`text`='".$text."';");
			$rid=mysqli_insert_id($link);
		    mysqli_close($link);
	        return json_encode(array(0=>$rid));  
      	}
      	else{
        	return false;
        }
 	}
 	function post_view($id){
 		$link=my_connect();
 		mysqli_real_escape_string($link, $id);
 		$res=mysqli_query($link,"SELECT * FROM `posts` WHERE `id`='".$id."';");
	    mysqli_close($link);
	    return json_encode(mysqli_fetch_assoc($res),JSON_UNESCAPED_UNICODE);
 	}
 	function feed($id){
 		$link=my_connect();
 		mysqli_real_escape_string($link, $id);
 		$res=mysqli_query($link,"SELECT `posts`.`id` `id` FROM `posts` INNER JOIN `groups_members` ON `groups_members`.`group_id`=`posts`.`group_id`  WHERE `groups_members`.`member_id`='".$id."';");
	    $arr=array();
      	while($re=mysqli_fetch_assoc($res)){
      		array_push($arr,$re['id']);
      	}
      	mysqli_close($link);
        return json_encode($arr);
 	}
 	function group_posts($id){
 		$link=my_connect();
 		mysqli_real_escape_string($link, $id);
 		$res=mysqli_query($link,"SELECT `id` FROM `posts`  WHERE `group_id`='".$id."';");
	    $arr=array();
      	while($re=mysqli_fetch_assoc($res)){
      		array_push($arr,$re['id']);
      	}
      	mysqli_close($link);
        return json_encode($arr);
 	}
?>