<?php
require_once('connection.php');
//creates users
function create_user($university,$firstName,$lastName,$userName,$collegeEmail,$token){

	global $connect;

		try{
			$connect->beginTransaction();
			$stmt = $connect->prepare("SELECT college_id FROM collegeList WHERE uni_name = ?");
			$stmt->bindParam(1,$university,PDO::PARAM_STR);
			$stmt->execute();
			$collegeId = $stmt->fetch(PDO::FETCH_ASSOC)['college_id'];

			$stmt = $connect->prepare("INSERT INTO collegestudent(`firstName`,`lastName`,`userName`,`email`, `token`, `collegeId`)  VALUES(?,?,?,?,?,?)");
			$stmt->bindParam(1,$firstName,PDO::PARAM_STR);
			$stmt->bindParam(2,$lastName,PDO::PARAM_STR);
			$stmt->bindParam(3,$userName,PDO::PARAM_STR);
			$stmt->bindParam(4,$collegeEmail,PDO::PARAM_STR);
			$stmt->bindParam(5,$token,PDO::PARAM_STR);
			$stmt->bindParam(6,$collegeId,PDO::PARAM_INT);
			$stmt->execute();
			$connect->commit();
			return findUserByEmail($collegeEmail);
		}catch(Exception $e){
			throw $e;
		}

}
function create_profile($userId,$majorId){

	global $connect;

		try{
			$connect->beginTransaction();
			$stmt = $connect->prepare("INSERT INTO profile_about_me(`student_id`,`major_id`)  VALUES(?,?)");
			$stmt->bindParam(1,$userId,PDO::PARAM_INT);
			$stmt->bindParam(2,$majorId,PDO::PARAM_INT);
			$stmt->execute();
			$connect->commit();
			return true;;
		}catch(Exception $e){
			throw $e;
		}

}
function create_community($collegeId,$categoryId,$userId,$communityName,$communityMessage,$communityDescription,$communityType,$communityColor){
	global $connect;
	if ($categoryId == 21) {
		$communityCategory = "story";
	}else{
		$communityCategory = "group";
	}
		
		try{
			$connect->beginTransaction();
			$stmt = $connect->prepare("INSERT INTO communities(`college_id`,`category_id`,`creator_id`,`community_name`, `community_message`, `community_description`, `community_category`,`community_type`,`community_color`)  VALUES(?,?,?,?,?,?,?,?,?)");
			$stmt->bindParam(1,$collegeId,PDO::PARAM_INT);
			$stmt->bindParam(2,$categoryId,PDO::PARAM_INT);
			$stmt->bindParam(3,$userId,PDO::PARAM_INT);
			$stmt->bindParam(4,$communityName,PDO::PARAM_STR);
			$stmt->bindParam(5,$communityMessage,PDO::PARAM_STR);
			$stmt->bindParam(6,$communityDescription,PDO::PARAM_STR);
			$stmt->bindParam(7,$communityCategory,PDO::PARAM_STR);
			$stmt->bindParam(8,$communityType,PDO::PARAM_STR);
			$stmt->bindParam(9,$communityColor,PDO::PARAM_STR);
			$stmt->execute();
			$connect->commit();

			$connect->beginTransaction();
			$stmt = $connect->prepare("SELECT community_id,community_category,creator_id FROM communities WHERE `creator_id` = ? order by date_created desc limit 1");
			$stmt->bindParam(1,$userId,PDO::PARAM_INT);
			$stmt->execute();
			$connect->commit();			
			$result = $stmt->fetch(PDO::FETCH_ASSOC);

			join_community($result['community_id'],$result['creator_id'],1);
			add_community_admin($result['community_id'],$result['creator_id'],2);

			return $result;
		}catch(Exception $e){
			throw $e;
		}

}
function update_community($communityId,$categoryId,$communityName,$communityMessage,$communityDescription,$communityType,$communityColor,$date){
	global $connect;
	try {
		$connect->beginTransaction();
		$stmt = $connect->prepare("UPDATE communities SET category_id = ?, community_name = ?, community_message = ?, community_description = ?, community_type = ?,community_color = ?,last_update = ?  WHERE community_id = ?");
		$stmt->bindParam(1,$categoryId,PDO::PARAM_INT);
		$stmt->bindParam(2,$communityName,PDO::PARAM_STR);
		$stmt->bindParam(3,$communityMessage,PDO::PARAM_STR);
		$stmt->bindParam(4,$communityDescription,PDO::PARAM_STR);
		$stmt->bindParam(5,$communityType,PDO::PARAM_STR);
		$stmt->bindParam(6,$communityColor,PDO::PARAM_STR);
		$stmt->bindParam(7,$date,PDO::PARAM_STR);
		$stmt->bindParam(8,$communityId,PDO::PARAM_INT);
		$stmt->execute();
		$connect->commit();
		return true;		
	} catch (Exception $e) {
		throw $e;
	}	
}
function create_major($collegeId,$majorId,$majorName,$message){

	global $connect;

		try{
			$connect->beginTransaction();
			$stmt = $connect->prepare("INSERT INTO majors(`majorList_id`,`college_id`,`category_id`,`community_name`,`community_message`)  VALUES(?,?,23,?,?)");
			$stmt->bindParam(1,$majorId,PDO::PARAM_INT);
			$stmt->bindParam(2,$collegeId,PDO::PARAM_INT);
			$stmt->bindParam(3,$majorName,PDO::PARAM_STR);
			$stmt->bindParam(4,$message,PDO::PARAM_STR);
			$stmt->execute();
			$connect->commit();
			return true;;
		}catch(Exception $e){
			throw $e;
		}

}
function create_discussion($discussionRoomId,$discussionTopicId,$collegeId,$userId,$discussionTitle,$discussionPost){
	global $connect;
		try{
			$connect->beginTransaction();
			$stmt = $connect->prepare("INSERT INTO discussion_post(`discussion_room_id`,`d_topic_id`,`college_id`, `student_id`, `discussion_title`,`discussion_post`)  VALUES(?,?,?,?,?,?)");
			$stmt->bindParam(1,$discussionRoomId,PDO::PARAM_INT);
			$stmt->bindParam(2,$discussionTopicId,PDO::PARAM_INT);
			$stmt->bindParam(3,$collegeId,PDO::PARAM_INT);
			$stmt->bindParam(4,$userId,PDO::PARAM_INT);
			$stmt->bindParam(5,$discussionTitle,PDO::PARAM_STR);
			$stmt->bindParam(6,$discussionPost,PDO::PARAM_STR);
			$stmt->execute();
			$connect->commit();

			$connect->beginTransaction();
			$stmt = $connect->prepare("SELECT d_post_id FROM discussion_post WHERE `student_id` = ? order by post_date desc limit 1");
			$stmt->bindParam(1,$userId,PDO::PARAM_INT);
			$stmt->execute();
			$connect->commit();			
			return $stmt->fetch(PDO::FETCH_ASSOC);
		}catch(Exception $e){
			throw $e;
		}
}
function create_community_discussion($collegeId,$communityId=NULL,$majorId=NULL,$storyId=NULL,$userId,$discussionTitle,$discussionPost){
	global $connect;
	if (!is_null($communityId)) {
		try{
			$connect->beginTransaction();
			$stmt = $connect->prepare("INSERT INTO community_discussions(`college_id`,`community_id`, `student_id`,`c_discussion_post`)  VALUES(?,?,?,?)");
			$stmt->bindParam(1,$collegeId,PDO::PARAM_INT);
			$stmt->bindParam(2,$communityId,PDO::PARAM_INT);
			$stmt->bindParam(3,$userId,PDO::PARAM_INT);
			$stmt->bindParam(4,$discussionPost,PDO::PARAM_STR);
			$stmt->execute();
			$connect->commit();

			$connect->beginTransaction();
			$stmt = $connect->prepare("SELECT c_discussion_id FROM community_discussions WHERE `student_id` = ? order by post_date desc limit 1;");
			$stmt->bindParam(1,$userId,PDO::PARAM_INT);
			$stmt->execute();
			$connect->commit();			
			return $stmt->fetch(PDO::FETCH_ASSOC);
		}catch(Exception $e){
			throw $e;
		}
	}elseif(!is_null($majorId)){
		try{
			$connect->beginTransaction();
			$stmt = $connect->prepare("INSERT INTO community_discussions(`college_id`,`major_id`, `student_id`,`c_discussion_post`)  VALUES(?,?,?,?)");
			$stmt->bindParam(1,$collegeId,PDO::PARAM_INT);
			$stmt->bindParam(2,$majorId,PDO::PARAM_INT);
			$stmt->bindParam(3,$userId,PDO::PARAM_INT);
			$stmt->bindParam(4,$discussionPost,PDO::PARAM_STR);
			$stmt->execute();
			$connect->commit();

			$connect->beginTransaction();
			$stmt = $connect->prepare("SELECT c_discussion_id FROM community_discussions WHERE `student_id` = ? order by post_date desc limit 1;");
			$stmt->bindParam(1,$userId,PDO::PARAM_INT);
			$stmt->execute();
			$connect->commit();			
			return $stmt->fetch(PDO::FETCH_ASSOC);
		}catch(Exception $e){
			throw $e;
		}
	}elseif(!is_null($storyId)){
		try{
			$connect->beginTransaction();
			$stmt = $connect->prepare("INSERT INTO community_discussions(`college_id`,`community_id`, `student_id`,`c_discussion_title`,`c_discussion_post`)  VALUES(?,?,?,?,?)");
			$stmt->bindParam(1,$collegeId,PDO::PARAM_INT);
			$stmt->bindParam(2,$storyId,PDO::PARAM_INT);
			$stmt->bindParam(3,$userId,PDO::PARAM_INT);
			$stmt->bindParam(4,$discussionTitle,PDO::PARAM_STR);
			$stmt->bindParam(5,$discussionPost,PDO::PARAM_STR);
			$stmt->execute();
			$connect->commit();

			$connect->beginTransaction();
			$stmt = $connect->prepare("SELECT c_discussion_id FROM community_discussions WHERE `student_id` = ? order by post_date desc limit 1;");
			$stmt->bindParam(1,$userId,PDO::PARAM_INT);
			$stmt->execute();
			$connect->commit();			
			return $stmt->fetch(PDO::FETCH_ASSOC);
		}catch(Exception $e){
			throw $e;
		}
	}
}
//creates event
function create_event($eventTypeId,$collegeId,$userId,$communityId,$eventTypeB,$eventTitle,$eventDescription,$eventLocation,$eventDate,$eventTime){
	global $connect;
	if (is_null($communityId)) {
		try{
			$connect->beginTransaction();
			$stmt = $connect->prepare("INSERT INTO events(`event_type_id`, `college_id`, `student_id`, `event_access`, `event_title`, `event_description`, `event_location`, `event_date`, `event_time`)  VALUES(?,?,?,?,?,?,?,?,?)");
			$stmt->bindParam(1,$eventTypeId,PDO::PARAM_INT);
			$stmt->bindParam(2,$collegeId,PDO::PARAM_INT);
			$stmt->bindParam(3,$userId,PDO::PARAM_INT);
			$stmt->bindParam(4,$eventTypeB,PDO::PARAM_STR);
			$stmt->bindParam(5,$eventTitle,PDO::PARAM_STR);
			$stmt->bindParam(6,$eventDescription,PDO::PARAM_STR);
			$stmt->bindParam(7,$eventLocation,PDO::PARAM_STR);
			$stmt->bindParam(8,$eventDate,PDO::PARAM_STR);
			$stmt->bindParam(9,$eventTime,PDO::PARAM_STR);
			$stmt->execute();
			$connect->commit();

			$connect->beginTransaction();
			$stmt = $connect->prepare("SELECT event_id FROM events WHERE `student_id` = ? order by date_created desc limit 1;");
			$stmt->bindParam(1,$userId,PDO::PARAM_INT);
			$stmt->execute();
			$connect->commit();			
			return $stmt->fetch(PDO::FETCH_ASSOC);
		}catch(Exception $e){
			throw $e;
		}
	}else{
		try{
			$connect->beginTransaction();
			$stmt = $connect->prepare("INSERT INTO events(`event_type_id`, `college_id`, `student_id`, `community_id`,`event_access`, `event_title`, `event_description`, `event_location`, `event_date`, `event_time`)  VALUES(?,?,?,?,?,?,?,?,?,?)");
			$stmt->bindParam(1,$eventTypeId,PDO::PARAM_INT);
			$stmt->bindParam(2,$collegeId,PDO::PARAM_INT);
			$stmt->bindParam(3,$userId,PDO::PARAM_INT);
			$stmt->bindParam(4,$communityId,PDO::PARAM_INT);
			$stmt->bindParam(5,$eventTypeB,PDO::PARAM_STR);
			$stmt->bindParam(6,$eventTitle,PDO::PARAM_STR);
			$stmt->bindParam(7,$eventDescription,PDO::PARAM_STR);
			$stmt->bindParam(8,$eventLocation,PDO::PARAM_STR);
			$stmt->bindParam(9,$eventDate,PDO::PARAM_STR);
			$stmt->bindParam(10,$eventTime,PDO::PARAM_STR);
			$stmt->execute();
			$connect->commit();

			$connect->beginTransaction();
			$stmt = $connect->prepare("SELECT event_id,community_id FROM events WHERE `student_id` = ? order by date_created desc limit 1;");
			$stmt->bindParam(1,$userId,PDO::PARAM_INT);
			$stmt->execute();
			$connect->commit();			
			return $stmt->fetch(PDO::FETCH_ASSOC);
		}catch(Exception $e){
			throw $e;
		}		
	}

}

function create_review($collegeId,$reviewCategoryId,$ratingId,$userId,$reviewDescription){
	global $connect;
		try{
			$connect->beginTransaction();
			$stmt = $connect->prepare("INSERT INTO reviews(`college_id`,`review_category_id`,`review_rating_id`, `student_id`, `review_description`)  VALUES(?,?,?,?,?)");
			$stmt->bindParam(1,$collegeId,PDO::PARAM_INT);
			$stmt->bindParam(2,$reviewCategoryId,PDO::PARAM_INT);
			$stmt->bindParam(3,$ratingId,PDO::PARAM_INT);
			$stmt->bindParam(4,$userId,PDO::PARAM_INT);
			$stmt->bindParam(5,$reviewDescription,PDO::PARAM_STR);
			$stmt->execute();
			$connect->commit();
			return true;
		}catch(Exception $e){
			throw $e;
		}	
}
//adds 
function add_favorite($typeId, $userId, $favoriteType){
	global $connect;
	switch ($favoriteType) {
		case 'community-discussion':
			$x = 'c_discussion_id';
			break;
		case 'community':
			$x = 'community_id';
			break;
		case 'discussion':
			$x = 'discussion_id';
			break;
		case 'event':
			$x = 'event_id';
			break;
	}
		try {
			$connect->beginTransaction();
			$stmt = $connect->prepare("INSERT INTO favorites(`user_id`,`$x`) VALUES(?,?)");
			$stmt->bindParam(1,$userId, PDO::PARAM_INT);
			$stmt->bindParam(2,$typeId,PDO::PARAM_INT);
			$stmt->execute();
			$connect->commit();
			return true;			
		} catch (Exception $e) {
			throw $e;
		}
}
function check_interest($categoryId,$userId){
	global $connect;
		try {
			$connect->beginTransaction();
			$stmt = $connect->prepare("SELECT interest_id from interests WHERE category_id = ? AND student_id = ?");
			$stmt->bindParam(1,$categoryId, PDO::PARAM_INT);
			$stmt->bindParam(2,$userId, PDO::PARAM_INT);
			$stmt->execute();
			$connect->commit();
			return $stmt->fetch(PDO::FETCH_ASSOC);			
		} catch (Exception $e) {
			throw $e;
		}
}
function check_major($collegeId,$majorId){
	global $connect;
		try {
			$connect->beginTransaction();
			$stmt = $connect->prepare("SELECT major_id from majors WHERE college_id = ? AND majorList_id = ?");
			$stmt->bindParam(1,$collegeId, PDO::PARAM_INT);
			$stmt->bindParam(2,$majorId,PDO::PARAM_INT);
			$stmt->execute();
			$connect->commit();
			return $stmt->fetch(PDO::FETCH_ASSOC);			
		} catch (Exception $e) {
			throw $e;
		}
}
function check_favorite($typeId, $userId, $favoriteType){
	global $connect;
	switch ($favoriteType) {
		case 'community-discussion':
			$x = 'c_discussion_id';
			break;
		case 'community':
			$x = 'community_id';
			break;
		case 'discussion':
			$x = 'discussion_id';
			break;
		case 'event':
			$x = 'event_id';
			break;
	}
		try {
			$connect->beginTransaction();
			$stmt = $connect->prepare("SELECT favorite_id from favorites WHERE user_id = ? AND $x = ?");
			$stmt->bindParam(1,$userId, PDO::PARAM_INT);
			$stmt->bindParam(2,$typeId,PDO::PARAM_INT);
			$stmt->execute();
			$connect->commit();
			return $stmt->fetch(PDO::FETCH_ASSOC);			
		} catch (Exception $e) {
			throw $e;
		}
}
function delete_favorite($typeId, $userId, $favoriteType){
	global $connect;
	switch ($favoriteType) {
		case 'community-discussion':
			$x = 'c_discussion_id';
			break;
		case 'community':
			$x = 'community_id';
			break;
		case 'discussion':
			$x = 'discussion_id';
			break;
		case 'event':
			$x = 'event_id';
			break;
	}
		try {
			$connect->beginTransaction();
			$stmt = $connect->prepare("DELETE FROM `favorites`  WHERE user_id = ? AND $x = ?");
			$stmt->bindParam(1,$userId, PDO::PARAM_INT);
			$stmt->bindParam(2,$typeId,PDO::PARAM_INT);
			$stmt->execute();
			$connect->commit();
			return true;			
		} catch (Exception $e) {
			throw $e;
		}
}

//unfollow school
function unfollow_school($collegeId,$userId){
	global $connect;
		try {
			$connect->beginTransaction();
			$stmt = $connect->prepare("DELETE FROM `school_followers`  WHERE user_id = ? AND college_id = ?");
			$stmt->bindParam(1,$userId, PDO::PARAM_INT);
			$stmt->bindParam(2,$collegeId,PDO::PARAM_INT);
			$stmt->execute();
			$connect->commit();
			return true;			
		} catch (Exception $e) {
			throw $e;
		}
}
function follow_school($collegeId,$userId){
	global $connect;
		try {
			$connect->beginTransaction();
			$stmt = $connect->prepare("INSERT INTO school_followers(`user_id`,`college_id`) VALUES(?,?)");
			$stmt->bindParam(1,$userId, PDO::PARAM_INT);
			$stmt->bindParam(2,$collegeId,PDO::PARAM_INT);
			$stmt->execute();
			$connect->commit();
			return true;			
		} catch (Exception $e) {
			throw $e;
		}
}
function unfollow_member($userId,$friendId){
	global $connect;
		try {
			$connect->beginTransaction();
			$stmt = $connect->prepare("DELETE FROM `friend_followers`  WHERE user_id = ? AND friend_id = ?");
			$stmt->bindParam(1,$userId, PDO::PARAM_INT);
			$stmt->bindParam(2,$friendId,PDO::PARAM_INT);
			$stmt->execute();
			$connect->commit();
			return true;			
		} catch (Exception $e) {
			throw $e;
		}
}
function follow_member($userId,$friendId){
	global $connect;
		try {
			$connect->beginTransaction();
			$stmt = $connect->prepare("INSERT INTO friend_followers(`user_id`,`friend_id`) VALUES(?,?)");
			$stmt->bindParam(1,$userId, PDO::PARAM_INT);
			$stmt->bindParam(2,$friendId,PDO::PARAM_INT);
			$stmt->execute();
			$connect->commit();
			return true;			
		} catch (Exception $e) {
			throw $e;
		}
}
function unfollow_interest($categoryId,$userId){
	global $connect;
		try {
			$connect->beginTransaction();
			$stmt = $connect->prepare("DELETE FROM interests  WHERE student_id = ? AND category_id = ?");
			$stmt->bindParam(1,$userId, PDO::PARAM_INT);
			$stmt->bindParam(2,$categoryId,PDO::PARAM_INT);
			$stmt->execute();
			$connect->commit();
			return true;			
		} catch (Exception $e) {
			throw $e;
		}
}
function follow_interest($categoryId,$userId){
	global $connect;
		try {
			$connect->beginTransaction();
			$stmt = $connect->prepare("INSERT INTO interests(`category_id`,`student_id`) VALUES(?,?)");
			$stmt->bindParam(1,$categoryId, PDO::PARAM_INT);
			$stmt->bindParam(2,$userId,PDO::PARAM_INT);
			$stmt->execute();
			$connect->commit();
			return true;			
		} catch (Exception $e) {
			throw $e;
		}
}
function add_community_admin($communityId,$userId,$adminLevel){
	global $connect;
		try {
			$connect->beginTransaction();
			$stmt = $connect->prepare("INSERT INTO community_admins(`community_id`,`student_id`,`admin_level`) VALUES(?,?,$adminLevel)");
			$stmt->bindParam(1,$communityId, PDO::PARAM_INT);
			$stmt->bindParam(2,$userId,PDO::PARAM_INT);
			$stmt->execute();
			$connect->commit();
			return true;			
		} catch (Exception $e) {
			throw $e;
		}
}
function join_community($communityId,$userId,$status){
	global $connect;
	$read = NULL;
	if ($status == 2) {
		$read = 'no';
	}
		try {
			$connect->beginTransaction();
			$stmt = $connect->prepare("INSERT INTO community_members(`community_id`,`student_id`,`status`,`is_read`) VALUES(?,?,$status,?)");
			$stmt->bindParam(1,$communityId, PDO::PARAM_INT);
			$stmt->bindParam(2,$userId,PDO::PARAM_INT);
			$stmt->bindParam(3,$read,PDO::PARAM_STR);
			$stmt->execute();
			$connect->commit();
			return true;			
		} catch (Exception $e) {
			throw $e;
		}
}
function leave_community($communityId,$userId){
	global $connect;
		try {
			$connect->beginTransaction();
			$stmt = $connect->prepare("DELETE FROM `community_members`  WHERE community_id = ? AND student_id = ?");
			$stmt->bindParam(1,$communityId, PDO::PARAM_INT);
			$stmt->bindParam(2,$userId,PDO::PARAM_INT);
			$stmt->execute();
			$connect->commit();
			return true;			
		} catch (Exception $e) {
			throw $e;
		}
}
function attend_event($eventId,$userId){
	global $connect;
		try {
			$connect->beginTransaction();
			$stmt = $connect->prepare("INSERT INTO event_attendees(`event_id`,`student_id`) VALUES(?,?)");
			$stmt->bindParam(1,$eventId, PDO::PARAM_INT);
			$stmt->bindParam(2,$userId,PDO::PARAM_INT);
			$stmt->execute();
			$connect->commit();
			return true;			
		} catch (Exception $e) {
			throw $e;
		}
}
function leave_event($eventId,$userId){
	global $connect;
		try {
			$connect->beginTransaction();
			$stmt = $connect->prepare("DELETE FROM `event_attendees` WHERE event_id = ? AND student_id = ?");
			$stmt->bindParam(1,$eventId, PDO::PARAM_INT);
			$stmt->bindParam(2,$userId,PDO::PARAM_INT);
			$stmt->execute();
			$connect->commit();
			return true;			
		} catch (Exception $e) {
			throw $e;
		}
}
//check if user is a student at a school
function is_student($userId,$collegeId){
	global $connect;
		
	try {
		$connect->beginTransaction();
		$stmt = $connect->prepare("SELECT id FROM collegestudent WHERE id = ? AND collegeId = ?");
		$stmt->bindParam(1,$userId,PDO::PARAM_INT);
		$stmt->bindParam(2,$collegeId,PDO::PARAM_INT);
		$stmt->execute();
		$connect->commit();
		return $stmt->fetch(PDO::FETCH_ASSOC);	
	} catch (Exception $e) {
		throw $e;
	}
}
//check if user is a member of a community
function is_member($userId,$communityId,$all=Null){
	global $connect;
	if (is_null($all)) {
		try {
			$connect->beginTransaction();
			$stmt = $connect->prepare("SELECT student_id FROM community_members WHERE student_id = ? AND community_id = ? AND status = 1");
			$stmt->bindParam(1,$userId,PDO::PARAM_INT);
			$stmt->bindParam(2,$communityId,PDO::PARAM_INT);
			$stmt->execute();
			$connect->commit();
			return $stmt->fetch(PDO::FETCH_ASSOC);	
		} catch (Exception $e) {
			throw $e;
		}
	}else{
		try {
			$connect->beginTransaction();
			$stmt = $connect->prepare("SELECT student_id FROM community_members WHERE student_id = ? AND community_id = ? AND status = 2");
			$stmt->bindParam(1,$userId,PDO::PARAM_INT);
			$stmt->bindParam(2,$communityId,PDO::PARAM_INT);
			$stmt->execute();
			$connect->commit();
			return $stmt->fetch(PDO::FETCH_ASSOC);	
		} catch (Exception $e) {
			throw $e;
		}	
	}

}
//check if user is admin and output admin level - level 2 is top
function is_admin($userId,$communityId){
	global $connect;
		
	try {
		$connect->beginTransaction();
		$stmt = $connect->prepare("SELECT admin_level FROM community_admins WHERE student_id = ? AND community_id = ?");
		$stmt->bindParam(1,$userId,PDO::PARAM_INT);
		$stmt->bindParam(2,$communityId,PDO::PARAM_INT);
		$stmt->execute();
		$connect->commit();
		$result = $stmt->fetch(PDO::FETCH_ASSOC);	
	} catch (Exception $e) {
		throw $e;
	}

	if($result){
		if ($result['admin_level'] == '1') {
			return 'admin';
		}elseif ($result['admin_level'] == '2'){
			return 'creator-admin';
		}
	}else{
		return false;
	}
}
//checks if user is the creator of a post, review, event
function is_creator($type,$userId,$id){
	global $connect;

	if ($type == 'discussion') {
		try {
			$connect->beginTransaction();
			$stmt = $connect->prepare("SELECT student_id FROM discussion_post WHERE student_id = ? AND d_post_id = ?");
			$stmt->bindParam(1,$userId,PDO::PARAM_INT);
			$stmt->bindParam(2,$id,PDO::PARAM_INT);
			$stmt->execute();
			$connect->commit();
			return $stmt->fetch(PDO::FETCH_ASSOC);	
		} catch (Exception $e) {
			throw $e;
		}
	}elseif($type == 'event'){
		try {
			$connect->beginTransaction();
			$stmt = $connect->prepare("SELECT student_id FROM events WHERE student_id = ? AND event_id = ?");
			$stmt->bindParam(1,$userId,PDO::PARAM_INT);
			$stmt->bindParam(2,$id,PDO::PARAM_INT);
			$stmt->execute();
			$connect->commit();
			return $stmt->fetch(PDO::FETCH_ASSOC);			
		} catch (Exception $e) {
			throw $e;
		}
	}elseif($type == 'c_discussion'){
		try {
			$connect->beginTransaction();
			$stmt = $connect->prepare("SELECT student_id FROM community_discussions WHERE student_id = ? AND c_discussion_id = ?");
			$stmt->bindParam(1,$userId,PDO::PARAM_INT);
			$stmt->bindParam(2,$id,PDO::PARAM_INT);
			$stmt->execute();
			$connect->commit();
			return $stmt->fetch(PDO::FETCH_ASSOC);			
		} catch (Exception $e) {
			throw $e;
		}		
	}elseif($type == 'review'){
		try {
			$connect->beginTransaction();
			$stmt = $connect->prepare("SELECT student_id FROM reviews WHERE student_id = ? AND review_id = ?");
			$stmt->bindParam(1,$userId,PDO::PARAM_INT);
			$stmt->bindParam(2,$id,PDO::PARAM_INT);
			$stmt->execute();
			$connect->commit();
			return $stmt->fetch(PDO::FETCH_ASSOC);			
		} catch (Exception $e) {
			throw $e;
		}		
	}elseif($type == 'c_discussion_reply'){
		try {
			$connect->beginTransaction();
			$stmt = $connect->prepare("SELECT student_id FROM c_discussion_replies WHERE student_id = ? AND c_discussion_reply_id = ?");
			$stmt->bindParam(1,$userId,PDO::PARAM_INT);
			$stmt->bindParam(2,$id,PDO::PARAM_INT);
			$stmt->execute();
			$connect->commit();
			return $stmt->fetch(PDO::FETCH_ASSOC);			
		} catch (Exception $e) {
			throw $e;
		}		
	}elseif($type == 'c_discussion_reply_comment'){
		try {
			$connect->beginTransaction();
			$stmt = $connect->prepare("SELECT student_id FROM c_discussion_r_reply WHERE student_id = ? AND r_reply_id = ?");
			$stmt->bindParam(1,$userId,PDO::PARAM_INT);
			$stmt->bindParam(2,$id,PDO::PARAM_INT);
			$stmt->execute();
			$connect->commit();
			return $stmt->fetch(PDO::FETCH_ASSOC);			
		} catch (Exception $e) {
			throw $e;
		}		
	}elseif($type == 'discussion_reply'){
		try {
			$connect->beginTransaction();
			$stmt = $connect->prepare("SELECT student_id FROM discussion_replies WHERE student_id = ? AND d_reply_id = ?");
			$stmt->bindParam(1,$userId,PDO::PARAM_INT);
			$stmt->bindParam(2,$id,PDO::PARAM_INT);
			$stmt->execute();
			$connect->commit();
			return $stmt->fetch(PDO::FETCH_ASSOC);			
		} catch (Exception $e) {
			throw $e;
		}		
	}elseif($type == 'discussion_reply_comment'){
		try {
			$connect->beginTransaction();
			$stmt = $connect->prepare("SELECT student_id FROM discussion_r_replies WHERE student_id = ? AND r_reply_id = ?");
			$stmt->bindParam(1,$userId,PDO::PARAM_INT);
			$stmt->bindParam(2,$id,PDO::PARAM_INT);
			$stmt->execute();
			$connect->commit();
			return $stmt->fetch(PDO::FETCH_ASSOC);			
		} catch (Exception $e) {
			throw $e;
		}
	}elseif($type == 'event_comment'){
		try {
			$connect->beginTransaction();
			$stmt = $connect->prepare("SELECT student_id FROM event_comments WHERE student_id = ? AND e_comment_id = ?");
			$stmt->bindParam(1,$userId,PDO::PARAM_INT);
			$stmt->bindParam(2,$id,PDO::PARAM_INT);
			$stmt->execute();
			$connect->commit();
			return $stmt->fetch(PDO::FETCH_ASSOC);			
		} catch (Exception $e) {
			throw $e;
		}		
	}elseif($type == 'community'){
		try {
			$connect->beginTransaction();
			$stmt = $connect->query("SELECT creator_id FROM `communities`  WHERE community_id = $id  AND creator_id = $userId");
			$connect->commit();
			return $stmt->fetch(PDO::FETCH_ASSOC);
		} catch (Exception $e) {
			throw $e;
		}		
	}elseif($type == 'community_member'){
		try {
			$connect->beginTransaction();
			$stmt = $connect->query("SELECT student_id FROM `community_members`  WHERE community_id = $id  AND student_id = $userId");
			$connect->commit();
			return $stmt->fetch(PDO::FETCH_ASSOC);
		} catch (Exception $e) {
			throw $e;
		}	
	}


}


//show discussions + events
function showDiscussion($collegeId,$discussionTopicId =NULL,$dRoom){
		global $userId;
		global $loggedIn;
		global $collegeAbrev;
		global $urlCollegeName;
		if(!is_null($discussionTopicId)){
			$discussionsList = get_all_discussions($collegeId,$dRoom,$discussionTopicId);
		}else{
			$discussionsList = get_all_discussions($collegeId,$dRoom,'all');
		}
		if(!$loggedIn){
			$userId = 0;
		}
		$content = "";
		if(!empty($discussionsList)){
				foreach ($discussionsList as $key) {
					$replies = get_all_discussion_replies($collegeId,$key['d_post_id']);
					if (count($replies)==0){
						$replyCount =  count($replies)." Replies";
					}elseif(count($replies)==1){
						$replyCount = count($replies)." Reply"; 
					}else{
						$replyCount = count($replies)." Replies";
					}
					$checkFav = check_favorite($key['d_post_id'], $userId, 'discussion');
					if($checkFav){
						$color = "style='color: #DF7367;'";
					}else{
						$color = "";
					}
					$totalVotes = get_total_votes($key['d_post_id']);
					if (!$totalVotes) {
						$totalVotes = 0;
					}
					$up = $down = '';
					$checkVote = get_vote($key['d_post_id'],$userId);
					if ($checkVote['vote'] == 1) {
						$up = 'active-vote';
					}elseif ($checkVote['vote'] == -1) {
						$down = 'active-vote';
					}
					$postTime = post_time($key['post_date']);
					$remove = "";
					$isCreator = is_creator('discussion',$userId,$key['d_post_id']);
					if ($isCreator) {
						$remove = '<li class="ellipsis-button" onclick="removeItem(\'discussion\','.$key['d_post_id'].')">Remove Discussion</li><li>Edit Discussion</li>';
					}
					$content .= '<li class="forum-item" id="discussion-forum-item-' . $key['d_post_id'] . '">';
					$content .= '<div class="forum-post-vote">';
					$content .= '<i id="upvote-'. $key['d_post_id'] .'" class="fa fa-sort-up fa-2x vote-button '. $up .'" aria-hidden="true" onclick="vote('. $key['d_post_id'] .', ' . $userId .',this)"></i>';
					$content .= '<div id="vote-count-' . $key['d_post_id'] . '" class="vote-count">'. $totalVotes .'</div>';
					$content .= '<i id="downvote-'. $key['d_post_id'] .'" class="fa fa-sort-down fa-2x vote-button ' . $down . '" aria-hidden="true" onclick="vote('. $key['d_post_id'] .', ' . $userId .',this)"></i></div>';
					$content .= '<div class="forum-main"><div class="forum-post-body">';
					$content .= '<a href="discussion.php?school_name='. $urlCollegeName . '&discussion_id='. $key['d_post_id'].'"><p class="forum-title">' . $key['discussion_title'] . '</p></a></div>';
					$content .= '<ul  class="forum-item-list"><li><a href="profile.php?profile_id='.$key['student_id'] . '">@' . $key['userName'] . '</a><span> - '. $postTime .'</span></li>';
					$content .= '<li class="forum-item-btns"><span class="fa">' . $replyCount . '</span><i class="fa fa-heart-o" ' . $color . ' aria-hidden="true" id="discussion-'. $key['d_post_id'] . '" onclick="doFavorites(\'discussion\',' . $key['d_post_id'] . ', ' .$userId . ', this)"></i><i class="fa fa-ellipsis-h" id="ellipsis-cd-'.$key['d_post_id'].'" aria-hidden="true" onclick="showEllipsis(this)"></i><div class="ellipsis-menu"><ul><li data-type="post" data-id="'.$key['d_post_id'].'" class="ellipsis-button report-btn">Report</li>'.$remove.'</ul></div></li></ul>';
					$content .= '</div></li>';
				}
		}else{
			$content .= '<h3 style="padding:20px;">Be the first to start a discussion ' . $collegeAbrev . '</h3>';
		}

		return $content;
}


function showMeetups($meetupRoom){
		global $userId;
		global $loggedIn;
		global $collegeId;
		global $loggedIn;
		global $urlCollegeName;
		global $collegeAbrev;
		$eventsList = get_all_events($collegeId,$meetupRoom,null);

		if(!empty($eventsList)){
			$content = '<ul>';
			foreach ($eventsList as $key) {
				$checkFav = check_favorite($key['event_id'], $userId, 'event');
				if($checkFav){
					$color = "style='color: #DF7367;'";
				}else{
					$color = "";
				}
				$content .= '<li class="forum-item">';
				if (!is_null($key['community_id'])) {
					$content .= '<div class="event-details"><a href="event.php?school_name='. $urlCollegeName . '&community_id='. $key['community_id'] . '&event_id='. $key['event_id'].'"><h3>' . $key['event_title'] . '</h3></a><div class="forum-item-btns"><i class="fa fa-heart-o" ' . $color . ' aria-hidden="true" id="event-'. $key['event_id'] . '" onclick="doFavorites(\'event\',' . $key['event_id'] . ', ' .$userId . ', this)"></i><i class="fa fa-ellipsis-h" id="ellipsis-cd-'.$key['event_id'].'"  aria-hidden="true" onclick="showEllipsis(this)"></i><div class="ellipsis-menu""><ul><li data-type="meetup" data-id="'.$key['event_id'].'" class="report-btn">Report</li></ul></div></div></div>';
				}else{
					$content .= '<div class="event-details"><a href="event.php?school_name='. $urlCollegeName . '&event_id='. $key['event_id'].'"><h3>' . $key['event_title'] . '</h3></a><div class="forum-item-btns"><i class="fa fa-heart-o" ' . $color . ' aria-hidden="true" id="event-'. $key['event_id'] . '" onclick="doFavorites(\'event\',' . $key['event_id'] . ', ' .$userId . ', this)"></i><i class="fa fa-ellipsis-h" id="ellipsis-cd-'.$key['event_id'].'"  aria-hidden="true" onclick="showEllipsis(this)"></i><div class="ellipsis-menu""><ul><li data-type="meetup" data-id="'.$key['event_id'].'" class="report-btn">Report</li></ul></div></div></div>';
				}
				$content .= '<div><p>' . $key['event_description'] . '</p></div>';
				$content .= '<ul>';
				$content .= '<li class="event-info"><p>'. $key['event_date'] . '</p><p>' . '@' .$key['event_location'] . '</p></li>';
										
				$content .= '</ul>';

				$content .= '</li>';
			}
		$content .= '</ul>';
		}else{
			$content = '<h3 style="padding:20px;">Be the first to create an event ' . $collegeAbrev . '</h3>';
		}
									
		
		return $content;
}
//add comments to event
function add_event_comments($collegeId,$communityId = NULL, $eventId, $studentId, $eventComment){
	global $connect;

	if (!is_null($communityId)) {
			try{
				$connect->beginTransaction();
				$stmt = $connect->prepare("INSERT INTO event_comments(`college_id`,`community_id`,`event_id`,`student_id`, `comment`)  VALUES(?,?,?,?,?)");
				$stmt->bindParam(1,$collegeId,PDO::PARAM_INT);
				$stmt->bindParam(2,$communityId,PDO::PARAM_INT);
				$stmt->bindParam(3,$eventId,PDO::PARAM_INT);
				$stmt->bindParam(4,$studentId,PDO::PARAM_INT);
				$stmt->bindParam(5,$eventComment,PDO::PARAM_STR);
				$stmt->execute();
				$connect->commit();
				return true;
			}catch(Exception $e){
				throw $e;
			}
	}else{
			try{
				$connect->beginTransaction();
				$stmt = $connect->prepare("INSERT INTO event_comments(`college_id`,`event_id`,`student_id`, `comment`)  VALUES(?,?,?,?)");
				$stmt->bindParam(1,$collegeId,PDO::PARAM_INT);
				$stmt->bindParam(2,$eventId,PDO::PARAM_INT);
				$stmt->bindParam(3,$studentId,PDO::PARAM_INT);
				$stmt->bindParam(4,$eventComment,PDO::PARAM_STR);
				$stmt->execute();
				$connect->commit();
				return true;
			}catch(Exception $e){
				throw $e;
			}	
	}
}


//add replies to discussions
function add_reply($collegeId, $communityId = null, $majorId = null, $discussionId, $userId, $replyPost){
	global $connect;

	if (is_null($communityId) && is_null($majorId)) {
		try{
			$connect->beginTransaction();
			$stmt = $connect->prepare("INSERT INTO discussion_replies(`college_id`, `discussion_id`, `student_id`, `reply_post`)  VALUES(?,?,?,?)");
			$stmt->bindParam(1,$collegeId,PDO::PARAM_INT);
			$stmt->bindParam(2,$discussionId,PDO::PARAM_INT);
			$stmt->bindParam(3,$userId,PDO::PARAM_INT);
			$stmt->bindParam(4,$replyPost,PDO::PARAM_STR);
			$stmt->execute();
			$connect->commit();
			return true;
		}catch(Exception $e){
			throw $e;
		}
	}elseif (!is_null($communityId) && is_null($majorId)) {
		try{
			$connect->beginTransaction();
			$stmt = $connect->prepare("INSERT INTO c_discussion_replies(`college_id`, `community_id`, `c_discussion_id`, `student_id`, `reply_post`)  VALUES(?,?,?,?,?)");
			$stmt->bindParam(1,$collegeId,PDO::PARAM_INT);
			$stmt->bindParam(2,$communityId,PDO::PARAM_INT);
			$stmt->bindParam(3,$discussionId,PDO::PARAM_INT);
			$stmt->bindParam(4,$userId,PDO::PARAM_INT);
			$stmt->bindParam(5,$replyPost,PDO::PARAM_STR);
			$stmt->execute();
			$connect->commit();
			return true;
		}catch(Exception $e){
			throw $e;
		}
	}elseif (!is_null($majorId) && is_null($communityId)) {
		try{
			$connect->beginTransaction();
			$stmt = $connect->prepare("INSERT INTO c_discussion_replies(`college_id`, `major_id`, `c_discussion_id`, `student_id`, `reply_post`)  VALUES(?,?,?,?,?)");
			$stmt->bindParam(1,$collegeId,PDO::PARAM_INT);
			$stmt->bindParam(2,$majorId,PDO::PARAM_INT);
			$stmt->bindParam(3,$discussionId,PDO::PARAM_INT);
			$stmt->bindParam(4,$userId,PDO::PARAM_INT);
			$stmt->bindParam(5,$replyPost,PDO::PARAM_STR);
			$stmt->execute();
			$connect->commit();
			return true;
		}catch(Exception $e){
			throw $e;
		}
	}
}


//add comments to discussion replies
function add_reply_comments($dReplyId,$collegeId,$discussionId, $studentId, $rReplyPost){
	global $connect;

	try{
		$connect->beginTransaction();
		$stmt = $connect->prepare("INSERT INTO discussion_r_replies(`d_reply_id`, `college_id`, `discussion_id`, `student_id`, `r_reply_post`)  VALUES(?,?,?,?,?)");
		$stmt->bindParam(1,$dReplyId,PDO::PARAM_INT);
		$stmt->bindParam(2,$collegeId,PDO::PARAM_INT);
		$stmt->bindParam(3,$discussionId,PDO::PARAM_INT);
		$stmt->bindParam(4,$studentId,PDO::PARAM_INT);
		$stmt->bindParam(5,$rReplyPost,PDO::PARAM_STR);
		$stmt->execute();
		$connect->commit();
		return true;
	}catch(Exception $e){
		throw $e;
	}	
}

function add_c_reply_comments($dReplyId, $collegeId, $communityId = NULL, $majorId = NULL, $discussionId, $studentId, $rReplyPost){
	global $connect;
	if (!is_null($communityId)) {
		try{
			$connect->beginTransaction();
			$stmt = $connect->prepare("INSERT INTO c_discussion_r_reply(`c_discussion_reply_id`, `college_id`, `community_id`,`c_discussion_id`, `student_id`, `r_reply_post`)  VALUES(?,?,?,?,?,?)");
			$stmt->bindParam(1,$dReplyId,PDO::PARAM_INT);
			$stmt->bindParam(2,$collegeId,PDO::PARAM_INT);
			$stmt->bindParam(3,$communityId,PDO::PARAM_INT);
			$stmt->bindParam(4,$discussionId,PDO::PARAM_INT);
			$stmt->bindParam(5,$studentId,PDO::PARAM_INT);
			$stmt->bindParam(6,$rReplyPost,PDO::PARAM_STR);
			$stmt->execute();
			$connect->commit();
			return true;
		}catch(Exception $e){
			throw $e;
		}
	}elseif(!is_null($majorId)){
		try{
			$connect->beginTransaction();
			$stmt = $connect->prepare("INSERT INTO c_discussion_r_reply(`c_discussion_reply_id`, `college_id`, `major_id`,`c_discussion_id`, `student_id`, `r_reply_post`)  VALUES(?,?,?,?,?,?)");
			$stmt->bindParam(1,$dReplyId,PDO::PARAM_INT);
			$stmt->bindParam(2,$collegeId,PDO::PARAM_INT);
			$stmt->bindParam(3,$majorId,PDO::PARAM_INT);
			$stmt->bindParam(4,$discussionId,PDO::PARAM_INT);
			$stmt->bindParam(5,$studentId,PDO::PARAM_INT);
			$stmt->bindParam(6,$rReplyPost,PDO::PARAM_STR);
			$stmt->execute();
			$connect->commit();
			return true;
		}catch(Exception $e){
			throw $e;
		}
	}
}


//
function get_vote($discussionId,$userId){
	global $connect;

	try {
		$connect->beginTransaction();
		$stmt = $connect->prepare("SELECT * FROM discussion_vote WHERE discussion_id = ? AND student_id = ?");
		$stmt->bindParam(1,$discussionId,PDO::PARAM_INT);
		$stmt->bindParam(2,$userId,PDO::PARAM_INT);
		$stmt->execute();
		$connect->commit();
		return $stmt->fetch(PDO::FETCH_ASSOC);
	} catch (Exception $e) {
		throw $e;
	}
}

function new_vote($discussionId,$userId,$action){
	global $connect;

	if ($action == "up") {
		try{
			$connect->beginTransaction();
	        $stmt = $connect->prepare("INSERT INTO discussion_vote(`discussion_id`,`student_id`,`vote`) VALUES(?,?,1)");
	        $stmt->bindParam(1,$discussionId,PDO::PARAM_INT);
	        $stmt->bindParam(2,$userId,PDO::PARAM_INT);
	        $return = $stmt->execute();
			$connect->commit();
			return $return;
	    }catch(Exception $e){
			throw $e;
		}
	}elseif($action == "down"){
		try{
			$connect->beginTransaction();
	        $stmt = $connect->prepare("INSERT INTO discussion_vote(`discussion_id`,`student_id`,`vote`) VALUES(?,?,-1)");
	        $stmt->bindParam(1,$discussionId,PDO::PARAM_INT);
	        $stmt->bindParam(2,$userId,PDO::PARAM_INT);
	        $return = $stmt->execute();
			$connect->commit();
			return $return;
	    }catch(Exception $e){
			throw $e;
		}
	}
}

function update_vote($discussionId,$userId,$action){
	global $connect;
	$currentVote = intval(get_vote($discussionId,$userId)['vote']);

	if ($action == "up") {
		$newVote = $currentVote + 1;
		try{
			$connect->beginTransaction();
	        $stmt = $connect->prepare("UPDATE discussion_vote SET vote = $newVote WHERE discussion_id = ? AND student_id = ?");
	        $stmt->bindParam(1,$discussionId,PDO::PARAM_INT);
	        $stmt->bindParam(2,$userId,PDO::PARAM_INT);
	        $return = $stmt->execute();
			$connect->commit();
			return $return;
	    }catch(Exception $e){
			throw $e;
		}
	}elseif($action == "down"){
		$newVote = $currentVote - 1;
		try{
			$connect->beginTransaction();
	        $stmt = $connect->prepare("UPDATE discussion_vote SET vote = $newVote WHERE discussion_id = ? AND student_id = ?");
	        $stmt->bindParam(1,$discussionId,PDO::PARAM_INT);
	        $stmt->bindParam(2,$userId,PDO::PARAM_INT);
	        $return = $stmt->execute();
			$connect->commit();
			return $return;
	    }catch(Exception $e){
			throw $e;
		}
	}
}

function get_total_votes($discussionId){
	global $connect;

	try {
		$connect->beginTransaction();
		$stmt = $connect->prepare("SELECT SUM(vote) FROM discussion_vote WHERE discussion_id = ?");
		$stmt->bindParam(1,$discussionId,PDO::PARAM_INT);
		$stmt->execute();
		$connect->commit();
		return $stmt->fetchColumn();
	} catch (Exception $e) {
		throw $e;
	}
}

//community votes
function get_c_vote($discussionId,$userId){
	global $connect;

	try {
		$connect->beginTransaction();
		$stmt = $connect->prepare("SELECT * FROM community_discussion_vote WHERE c_discussion_id = ? AND student_id = ?");
		$stmt->bindParam(1,$discussionId,PDO::PARAM_INT);
		$stmt->bindParam(2,$userId,PDO::PARAM_INT);
		$stmt->execute();
		$connect->commit();
		return $stmt->fetch(PDO::FETCH_ASSOC);
	} catch (Exception $e) {
		throw $e;
	}
}

function new_c_vote($discussionId,$userId,$action){
	global $connect;

	if ($action == "up") {
		try{
			$connect->beginTransaction();
	        $stmt = $connect->prepare("INSERT INTO community_discussion_vote(`c_discussion_id`,`student_id`,`vote`) VALUES(?,?,1)");
	        $stmt->bindParam(1,$discussionId,PDO::PARAM_INT);
	        $stmt->bindParam(2,$userId,PDO::PARAM_INT);
	        $return = $stmt->execute();
			$connect->commit();
			return $return;
	    }catch(Exception $e){
			throw $e;
		}
	}elseif($action == "down"){
		try{
			$connect->beginTransaction();
	        $stmt = $connect->prepare("INSERT INTO community_discussion_vote(`c_discussion_id`,`student_id`,`vote`) VALUES(?,?,-1)");
	        $stmt->bindParam(1,$discussionId,PDO::PARAM_INT);
	        $stmt->bindParam(2,$userId,PDO::PARAM_INT);
	        $return = $stmt->execute();
			$connect->commit();
			return $return;
	    }catch(Exception $e){
			throw $e;
		}
	}
}

function update_c_vote($discussionId,$userId,$action){
	global $connect;
	$currentVote = intval(get_c_vote($discussionId,$userId)['vote']);

	if ($action == "up") {
		$newVote = $currentVote + 1;
		try{
			$connect->beginTransaction();
	        $stmt = $connect->prepare("UPDATE community_discussion_vote SET vote = $newVote WHERE c_discussion_id = ? AND student_id = ?");
	        $stmt->bindParam(1,$discussionId,PDO::PARAM_INT);
	        $stmt->bindParam(2,$userId,PDO::PARAM_INT);
	        $return = $stmt->execute();
			$connect->commit();
			return $return;
	    }catch(Exception $e){
			throw $e;
		}
	}elseif($action == "down"){
		$newVote = $currentVote - 1;
		try{
			$connect->beginTransaction();
	        $stmt = $connect->prepare("UPDATE community_discussion_vote SET vote = $newVote WHERE c_discussion_id = ? AND student_id = ?");
	        $stmt->bindParam(1,$discussionId,PDO::PARAM_INT);
	        $stmt->bindParam(2,$userId,PDO::PARAM_INT);
	        $return = $stmt->execute();
			$connect->commit();
			return $return;
	    }catch(Exception $e){
			throw $e;
		}
	}
}

function get_total_c_votes($discussionId){
	global $connect;

	try {
		$connect->beginTransaction();
		$stmt = $connect->prepare("SELECT SUM(vote) FROM community_discussion_vote WHERE c_discussion_id = ?");
		$stmt->bindParam(1,$discussionId,PDO::PARAM_INT);
		$stmt->execute();
		$connect->commit();
		return $stmt->fetchColumn();
	} catch (Exception $e) {
		throw $e;
	}
}
//delete user
function delete_user($userId){
	global $connect;
	try{
		$connect->beginTransaction();
		$stmt = $connect->prepare("DELETE FROM collegestudent WHERE id = ?");
		$stmt->bindParam(1,$userId,PDO::PARAM_INT);
		$return = $stmt->execute();
		$connect->commit();
		return $return;
  	}catch(Exception $e){
  	  	throw $e;
  	}
}
//update user 
function update_user($userId,$collegeId,$email,$username,$majorId){
	global $connect;
	try {
		$connect->beginTransaction();
		$stmt = $connect->prepare("UPDATE collegestudent SET collegeId = ?, email = ?, username = ? WHERE id = ?");
		$stmt->bindParam(1,$collegeId,PDO::PARAM_INT);
		$stmt->bindParam(2,$email,PDO::PARAM_STR);
		$stmt->bindParam(3,$username,PDO::PARAM_STR);
		$stmt->bindParam(4,$userId,PDO::PARAM_INT);
		$stmt->execute();

		$stmt = $connect->prepare("UPDATE profile_about_me SET major_id = ? WHERE student_id = ?");
		$stmt->bindParam(1,$majorId,PDO::PARAM_INT);
		$stmt->bindParam(2,$userId,PDO::PARAM_INT);
		$stmt->execute();
		$connect->commit();
		return true;		
	} catch (Exception $e) {
		
	}
}
function update_password($userId,$hashed){
	global $connect;
	try {
		$connect->beginTransaction();
		$stmt = $connect->prepare("UPDATE collegestudent SET token = ? WHERE id = ?");
		$stmt->bindParam(1,$hashed,PDO::PARAM_STR);
		$stmt->bindParam(2,$userId,PDO::PARAM_INT);
		$stmt->execute();
		$connect->commit();
		return true;		
	} catch (Exception $e) {
		
	}
}
function setVeriCode($userId,$veriCode) {
	global $connect;

	try{
		$connect->beginTransaction();
        $stmt = $connect->prepare("INSERT INTO verification(`id`,`verification_code`) VALUES(?,?)");
        $stmt->bindParam(1,$userId,PDO::PARAM_INT);
        $stmt->bindParam(2,$veriCode,PDO::PARAM_INT);
        $return = $stmt->execute();
		$connect->commit();
		return $return;
    }catch(Exception $e){
		throw $e;
	}
}

function setResetCode($userId,$resetCode) {
	global $connect;
	try{
		$connect->beginTransaction();
        $stmt = $connect->prepare("INSERT INTO resetPassword(`id`,`reset_code`) VALUES(?,?)");
        $stmt->bindParam(1,$userId,PDO::PARAM_INT);
        $stmt->bindParam(2,$resetCode,PDO::PARAM_STR);
		$return = $stmt->execute();
		$connect->commit();
		return $return;
    }catch(Exception $e){
		throw $e;
    }
		
}
function get_all_users(){
	global $connect;
	try{
		$connect->beginTransaction();
			$stmt = $connect->prepare("SELECT firstName, lastName, userName, email, uni_name AS university
													  FROM collegestudent INNER JOIN collegeList ON collegestudent.collegeid = collegeList.college_id");
			$stmt->execute();
			$connect->commit();
			return $stmt->fetchAll(PDO::FETCH_ASSOC);

	}catch(Exception $e){
		throw $e;
	}
}
function get_all_other_users($userId){
	global $connect;
	try{
		$connect->beginTransaction();
			$stmt = $connect->prepare("SELECT id, firstName, lastName, userName email, uni_name AS university
													  FROM collegestudent INNER JOIN university ON collegestudent.universityID = university.universityID WHERE id <> ?");
			$stmt->bindParam(1,$userId,PDO::PARAM_INT);
			$stmt->execute();
			$connect->commit();
			return $stmt->fetchAll(PDO::FETCH_ASSOC);

	}catch(Exception $e){
		throw $e;
	}
}
function get_students($collegeId,$userId=NULL){
	global $connect;
	if (is_null($userId)) {
		try{
			$connect->beginTransaction();
				$stmt = $connect->prepare("SELECT id,firstName, lastName, userName, email, uni_name 
														  FROM collegestudent INNER JOIN collegeList ON collegestudent.collegeId = collegeList.college_id
														  WHERE collegeList.college_id = ?");
				$stmt->bindParam(1,$collegeId,PDO::PARAM_INT);
				$stmt->execute();
				$connect->commit();
				return $stmt->fetchAll(PDO::FETCH_ASSOC);

		}catch(Exception $e){
			throw $e;
		}
	}else{
		try{
			$connect->beginTransaction();
				$stmt = $connect->prepare("SELECT id,firstName, lastName, userName, email, uni_name 
														  FROM collegestudent INNER JOIN collegeList ON collegestudent.collegeId = collegeList.college_id
														  WHERE collegeList.college_id = ? AND id <> ?");
				$stmt->bindParam(1,$collegeId,PDO::PARAM_INT);
				$stmt->bindParam(2,$userId,PDO::PARAM_INT);
				$stmt->execute();
				$connect->commit();
				return $stmt->fetchAll(PDO::FETCH_ASSOC);

		}catch(Exception $e){
			throw $e;
		}		
	}

}
function get_school_info($university){
	global $connect;
	try{
			$connect->beginTransaction();
			$stmt = $connect->prepare("SELECT * FROM collegeList WHERE uni_name = ?");
			$stmt->bindParam(1,$university,PDO::PARAM_STR);
			$stmt->execute();
			$connect->commit();
			return $stmt->fetch(PDO::FETCH_ASSOC);
	}catch(Exception $e){
		throw $e;
	}
}
function get_review_categories(){
	global $connect;
	try{
			$connect->beginTransaction();
			$stmt = $connect->prepare("SELECT * FROM reviews_categories");
			$stmt->execute();
			$connect->commit();
			return $stmt->fetchAll(PDO::FETCH_ASSOC);
	}catch(Exception $e){
		throw $e;
	}
}
function get_all_categories(){
	global $connect;
	try{
			$connect->beginTransaction();
			$stmt = $connect->prepare("SELECT * FROM categories ");
			$stmt->execute();
			$connect->commit();
			return $stmt->fetchAll(PDO::FETCH_ASSOC);
	}catch(Exception $e){
		throw $e;
	}
}
function get_category_communities($categoryId,$collegeId){
	global $connect;
	try{
			$connect->beginTransaction();
			$stmt = $connect->prepare("SELECT * FROM communities WHERE category_id = ? AND college_id = ?");
			$stmt->bindParam(1,$categoryId,PDO::PARAM_INT);
			$stmt->bindParam(2,$collegeId,PDO::PARAM_INT);
			$stmt->execute();
			$connect->commit();
			return $stmt->fetchAll(PDO::FETCH_ASSOC);
	}catch(Exception $e){
		throw $e;
	}
}
function get_category($categoryId){
	global $connect;
	try{
		$connect->beginTransaction();
			$stmt = $connect->prepare("SELECT category FROM categories WHERE category_id = ?");
			$stmt->bindParam(1,$categoryId,PDO::PARAM_INT);
			$stmt->execute();
			$connect->commit();
			$result = $stmt->fetch(PDO::FETCH_ASSOC);
			return $result['category'];

	}catch(Exception $e){
		throw $e;
	}
}

function get_all_communities($collegeId,$categoryId = NULL){
	global $connect;
	if (is_null($categoryId)) {
		try{
				$connect->beginTransaction();
				$stmt = $connect->prepare("SELECT * FROM communities WHERE college_id = ?");
				$stmt->bindParam(1,$collegeId,PDO::PARAM_INT);
				$stmt->execute();
				$connect->commit();
				return $stmt->fetchAll(PDO::FETCH_ASSOC);
		}catch(Exception $e){
			throw $e;
		}
	}else{
		try{
				$connect->beginTransaction();
				$stmt = $connect->prepare("SELECT * FROM communities 
											INNER JOIN categories ON communities.category_id =  categories.category_id
											WHERE college_id = ? AND communities.category_id = ? LIMIT 6");
				$stmt->bindParam(1,$collegeId,PDO::PARAM_INT);
				$stmt->bindParam(2,$categoryId,PDO::PARAM_INT);
				$stmt->execute();
				$connect->commit();
				return $stmt->fetchAll(PDO::FETCH_ASSOC);
		}catch(Exception $e){
			throw $e;
		}	
	}

}

function get_community($communityId,$collegeId){
	global $connect;
	try{
			$connect->beginTransaction();
			$stmt = $connect->prepare("SELECT communities.community_id, communities.category_id, categories.category,collegestudent.userName,community_name,community_message,community_category,community_description,community_type,community_color,date_created  FROM communities 
										INNER JOIN collegestudent ON communities.creator_id = collegestudent.id
	                                    INNER JOIN categories ON communities.category_id =  categories.category_id
										WHERE community_id = ?");
			$stmt->bindParam(1,$communityId,PDO::PARAM_INT);
			$stmt->execute();
			$connect->commit();
			return $stmt->fetch(PDO::FETCH_ASSOC);

	}catch(Exception $e){
		throw $e;
	}
}
function get_community_members($communityId){
	global $connect;
		try{
				$connect->beginTransaction();
				$stmt = $connect->prepare("SELECT community_members.student_id,collegestudent.id,collegestudent.firstName,collegestudent.lastName, userName FROM community_members 
											INNER JOIN collegestudent ON community_members.student_id = collegestudent.id
											WHERE community_id = ? AND status = 1");
				$stmt->bindParam(1,$communityId,PDO::PARAM_INT);
				$stmt->execute();
				$connect->commit();
				return $stmt->fetchAll(PDO::FETCH_ASSOC);
		}catch(Exception $e){
			throw $e;
		}

}
function get_community_admins($communityId){
	global $connect;
		try{
				$connect->beginTransaction();
				$stmt = $connect->prepare("SELECT community_admins.student_id, collegestudent.id,userName FROM community_admins 
											INNER JOIN collegestudent ON community_admins.student_id = collegestudent.id
											WHERE community_id = ?");
				$stmt->bindParam(1,$communityId,PDO::PARAM_INT);
				$stmt->execute();
				$connect->commit();
				return $stmt->fetchAll(PDO::FETCH_ASSOC);
		}catch(Exception $e){
			throw $e;
		}

}
function get_community_request($communityId,$status=NULL){
		global $connect;
		if (is_null($status)) {
			try {
				$connect->beginTransaction();
				$stmt = $connect->prepare("SELECT firstName, lastName, userName, student_id FROM community_members
											INNER JOIN collegestudent ON  community_members.student_id = collegestudent.id
										 WHERE community_id = ? AND status = 2");
				$stmt->bindParam(1,$communityId,PDO::PARAM_INT);
				$stmt->execute();
				$connect->commit();
				return $stmt->fetchAll(PDO::FETCH_ASSOC);	
			} catch (Exception $e) {
				throw $e;
			}
		}else{
			try {
				$connect->beginTransaction();
				$stmt = $connect->prepare("SELECT COUNT(*) FROM community_members WHERE community_id = ? AND status = 2 AND is_read = 'no'");
				$stmt->bindParam(1,$communityId,PDO::PARAM_INT);
				$stmt->execute();
				$connect->commit();
				return $stmt->fetchColumn();	
			} catch (Exception $e) {
				throw $e;
			}		
		}
	
}
function get_all_stories($collegeId,$categoryId=NULL){
	global $connect;
	if (is_null($categoryId)) {
		try{
				$connect->beginTransaction();
				$stmt = $connect->prepare("SELECT * FROM communities WHERE college_id = ? AND community_category = 'story'");
				$stmt->bindParam(1,$collegeId,PDO::PARAM_INT);
				$stmt->execute();
				$connect->commit();
				return $stmt->fetchAll(PDO::FETCH_ASSOC);
		}catch(Exception $e){
			throw $e;
		}
	}else{
		try{
				$connect->beginTransaction();
				$stmt = $connect->prepare("SELECT * FROM communities 
											INNER JOIN categories ON communities.category_id =  categories.category_id
											WHERE college_id = ? AND communities.category_id = ? AND community_category = 'story' LIMIT 6");
				$stmt->bindParam(1,$collegeId,PDO::PARAM_INT);
				$stmt->bindParam(2,$categoryId,PDO::PARAM_INT);
				$stmt->execute();
				$connect->commit();
				return $stmt->fetchAll(PDO::FETCH_ASSOC);
		}catch(Exception $e){
			throw $e;
		}	
	}

}

function get_all_majors($collegeId){
	global $connect;
	try{
			$connect->beginTransaction();
			$stmt = $connect->prepare("SELECT * FROM majors WHERE college_id = ?");
			$stmt->bindParam(1,$collegeId,PDO::PARAM_INT);
			$stmt->execute();
			$connect->commit();
			return $stmt->fetchAll(PDO::FETCH_ASSOC);
	}catch(Exception $e){
		throw $e;
	}
}
function get_major($collegeId,$majorId){
	global $connect;
	try{
			$connect->beginTransaction();
			$stmt = $connect->prepare("SELECT * FROM majors 
										INNER JOIN categories ON majors.category_id = categories.category_id
										WHERE major_id = ? AND college_id = ?");
			$stmt->bindParam(1,$majorId,PDO::PARAM_INT);
			$stmt->bindParam(2,$collegeId,PDO::PARAM_INT);
			$stmt->execute();
			$connect->commit();
			return $stmt->fetch(PDO::FETCH_ASSOC);

	}catch(Exception $e){
		throw $e;
	}
}
function get_majors_list($search){
	global $connect;
	$searchString = '%'.$search.'%';
	try{
			$connect->beginTransaction();
			$stmt = $connect->prepare("SELECT majorList_id as label, major as value FROM majorsLists WHERE major LIKE ?
										ORDER BY LOCATE(?, major)");
			$stmt->bindParam(1,$searchString,PDO::PARAM_STR);
			$stmt->bindParam(2,$search,PDO::PARAM_STR);
			$stmt->execute();
			$connect->commit();
			return $stmt->fetchAll(PDO::FETCH_ASSOC);

	}catch(Exception $e){
		throw $e;
	}
}
function search_communities($search,$collegeId){
	global $connect;
	$searchString = '%'.$search.'%';
	try{
			$connect->beginTransaction();
			$stmt = $connect->prepare("SELECT * FROM communities 
										INNER JOIN categories ON communities.category_id =  categories.category_id
										WHERE community_name LIKE ? OR community_description LIKE ? OR community_message LIKE ? OR categories.category LIKE ? AND college_id = ? ORDER BY LOCATE(?, community_name)");
			$stmt->bindParam(1,$searchString,PDO::PARAM_STR);
			$stmt->bindParam(2,$searchString,PDO::PARAM_STR);
			$stmt->bindParam(3,$searchString,PDO::PARAM_STR);
			$stmt->bindParam(4,$searchString,PDO::PARAM_STR);
			$stmt->bindParam(5,$collegeId,PDO::PARAM_INT);
			$stmt->bindParam(6,$search,PDO::PARAM_STR);
			$stmt->execute();
			$connect->commit();
			return $stmt->fetchAll(PDO::FETCH_ASSOC);

	}catch(Exception $e){
		throw $e;
	}
}
function search_discussion($search,$collegeId){
	global $connect;
	$searchString = '%'.$search.'%';
	try{
			$connect->beginTransaction();
			$stmt = $connect->prepare("SELECT * FROM discussion_post 
										INNER JOIN discussion_topics ON discussion_post.d_topic_id = discussion_topics.discussion_topic_id
										WHERE discussion_title LIKE ? OR discussion_post LIKE ? OR discussion_topics.discussion_topic LIKE ? AND college_id = ?
										ORDER BY LOCATE(?, discussion_title)");
			$stmt->bindParam(1,$searchString,PDO::PARAM_STR);
			$stmt->bindParam(2,$searchString,PDO::PARAM_STR);
			$stmt->bindParam(3,$searchString,PDO::PARAM_STR);
			$stmt->bindParam(4,$collegeId,PDO::PARAM_INT);
			$stmt->bindParam(5,$search,PDO::PARAM_STR);
			$stmt->execute();
			$connect->commit();
			return $stmt->fetchAll(PDO::FETCH_ASSOC);

	}catch(Exception $e){
		throw $e;
	}
}
function get_major_members($majorId){
	global $connect;
		try{
				$connect->beginTransaction();
				$stmt = $connect->prepare("SELECT profile_about_me.student_id,collegestudent.id, userName FROM profile_about_me 
											INNER JOIN collegestudent ON profile_about_me.student_id = collegestudent.id
											WHERE major_id = ?");
				$stmt->bindParam(1,$majorId,PDO::PARAM_INT);
				$stmt->execute();
				$connect->commit();
				return $stmt->fetchAll(PDO::FETCH_ASSOC);
		}catch(Exception $e){
			throw $e;
		}

}
function get_discussion_topics(){
	global $connect;
	try{
			$connect->beginTransaction();
			$stmt = $connect->prepare("SELECT * FROM discussion_topics ORDER BY discussion_topic ASC");
			$stmt->execute();
			$connect->commit();
			return $stmt->fetchAll(PDO::FETCH_ASSOC);
	}catch(Exception $e){
		throw $e;
	}
}
function get_topic($topicId){
	global $connect;
	try{
			$connect->beginTransaction();
			$stmt = $connect->prepare("SELECT * FROM discussion_topics WHERE discussion_topic_id = ? ");
			$stmt->bindParam(1,$topicId,PDO::PARAM_INT);
			$stmt->execute();
			$connect->commit();
			return $stmt->fetch(PDO::FETCH_ASSOC);
	}catch(Exception $e){
		throw $e;
	}
}
function get_all_discussions($collegeId,$dRoom,$discussionTopic){
	global $connect;
	if ($discussionTopic == "all") {
		if ($dRoom == 'community') {
			try{
					$connect->beginTransaction();
					$stmt = $connect->prepare("SELECT d_post_id, student_id, discussion_room, userName, discussion_title, discussion_post, post_date FROM discussion_post 
												INNER JOIN collegestudent ON discussion_post.student_id = collegestudent.id
		                                        INNER JOIN discussion_room ON discussion_post.discussion_room_id = 				
		                                        discussion_room.discussion_room_id 
												WHERE college_id = ?");
					$stmt->bindParam(1,$collegeId,PDO::PARAM_INT);
					$stmt->execute();
					$connect->commit();
					return $stmt->fetchAll(PDO::FETCH_ASSOC);
			}catch(Exception $e){
				throw $e;
			}
		}else{
			try{
					$connect->beginTransaction();
					$stmt = $connect->prepare("SELECT d_post_id, student_id, discussion_room, userName, discussion_title, discussion_post, post_date FROM discussion_post 
												INNER JOIN collegestudent ON discussion_post.student_id = collegestudent.id
		                                        INNER JOIN discussion_room ON discussion_post.discussion_room_id = 				
		                                        discussion_room.discussion_room_id 
												WHERE college_id = ? AND discussion_room.discussion_room = ?");
					$stmt->bindParam(1,$collegeId,PDO::PARAM_INT);
					$stmt->bindParam(2,$dRoom,PDO::PARAM_STR);
					$stmt->execute();
					$connect->commit();
					return $stmt->fetchAll(PDO::FETCH_ASSOC);
			}catch(Exception $e){
				throw $e;
			}			
		}

	}else{
		try{
				$discussionTopic = intval($discussionTopic);
				$connect->beginTransaction();
				$stmt = $connect->prepare("SELECT d_post_id, student_id, discussion_room, userName, discussion_title, discussion_post, post_date FROM discussion_post 
											INNER JOIN collegestudent ON discussion_post.student_id = collegestudent.id
	                                        INNER JOIN discussion_room ON discussion_post.discussion_room_id = 				
	                                        discussion_room.discussion_room_id 
											WHERE college_id = ? AND discussion_room.discussion_room = ? AND d_topic_id = ?");
				$stmt->bindParam(1,$collegeId,PDO::PARAM_INT);
				$stmt->bindParam(2,$dRoom,PDO::PARAM_STR);
				$stmt->bindParam(3,$discussionTopic,PDO::PARAM_INT);
				$stmt->execute();
				$connect->commit();
				return $stmt->fetchAll(PDO::FETCH_ASSOC);
		}catch(Exception $e){
			throw $e;
		}
	}

}
function get_discussion($collegeId,$discussionId,$dTopic=NULL){
	global $connect;
		try{
				$connect->beginTransaction();
				$stmt = $connect->prepare("SELECT d_post_id,discussion_post,discussion_title, userName, discussion_post.student_id,post_date FROM discussion_post 
											INNER JOIN collegestudent ON discussion_post.student_id = collegestudent.id
											WHERE college_id=? AND d_post_id = ?");
				$stmt->bindParam(1,$collegeId,PDO::PARAM_INT);
				$stmt->bindParam(2,$discussionId,PDO::PARAM_INT);

				$stmt->execute();
				$connect->commit();
				return $stmt->fetch(PDO::FETCH_ASSOC);

		}catch(Exception $e){
			throw $e;
		}
	
}
function get_all_discussion_replies($collegeId, $discussionId){
	global $connect;
	try{
			$connect->beginTransaction();
			$stmt = $connect->prepare("SELECT d_reply_id, discussion_replies.student_id, userName, reply_post, discussion_replies.post_date FROM discussion_replies 
										INNER JOIN collegestudent ON discussion_replies.student_id = collegestudent.id
										WHERE discussion_replies.college_id = ? AND discussion_id = ? ORDER BY post_date DESC");
			$stmt->bindParam(1,$collegeId,PDO::PARAM_INT);
			$stmt->bindParam(2,$discussionId,PDO::PARAM_INT);
			$stmt->execute();
			$connect->commit();
			return $stmt->fetchAll(PDO::FETCH_ASSOC);
	}catch(Exception $e){
		throw $e;
	}
}
function get_all_discussion_r_replies($collegeId,$discussionId,$discussionReplyId){
	global $connect;
	try{
			$connect->beginTransaction();
			$stmt = $connect->prepare("SELECT r_reply_id, d_reply_id, student_id, userName, r_reply_post, post_date FROM discussion_r_replies 
										INNER JOIN collegestudent ON discussion_r_replies.student_id = collegestudent.id
										WHERE  college_id = ? AND discussion_id = ? AND d_reply_id = ? ORDER BY post_date ASC");
			$stmt->bindParam(1,$collegeId,PDO::PARAM_INT);
			$stmt->bindParam(2,$discussionId,PDO::PARAM_INT);
			$stmt->bindParam(3,$discussionReplyId,PDO::PARAM_INT);
			$stmt->execute();
			$connect->commit();
			return $stmt->fetchAll(PDO::FETCH_ASSOC);
	}catch(Exception $e){
		throw $e;
	}
}
function get_all_events($collegeId,$eType = NULL,$communityId = NULL){
	global $connect;
	if (!is_null($eType) && is_null($communityId)) {
		if ($eType == 'communities') {
			try{
					$connect->beginTransaction();
					$stmt = $connect->prepare("SELECT event_id, community_id,event_type, student_id, event_access, event_title, event_description, event_location, event_address, event_date, event_time, event_photo, date_created FROM events
												INNER JOIN collegestudent ON events.student_id = collegestudent.id
		                                        INNER JOIN event_type ON events.event_type_id =  event_type.event_type_id 
												WHERE college_id = ?");
					$stmt->bindParam(1,$collegeId,PDO::PARAM_INT);
					$stmt->execute();
					$connect->commit();
					return $stmt->fetchAll(PDO::FETCH_ASSOC);
			}catch(Exception $e){
				throw $e;
			}
		}else{
			try{
					$connect->beginTransaction();
					$stmt = $connect->prepare("SELECT event_id, community_id,event_type, student_id, event_access, event_title, event_description, event_location, event_address, event_date, event_time, event_photo, date_created FROM events
												INNER JOIN collegestudent ON events.student_id = collegestudent.id
		                                        INNER JOIN event_type ON events.event_type_id =  event_type.event_type_id 
												WHERE college_id = ? AND event_type.event_type= ?");
					$stmt->bindParam(1,$collegeId,PDO::PARAM_INT);
					$stmt->bindParam(2,$eType,PDO::PARAM_STR);
					$stmt->execute();
					$connect->commit();
					return $stmt->fetchAll(PDO::FETCH_ASSOC);
			}catch(Exception $e){
				throw $e;
			}
		}
	}elseif(!is_null($eType) && !is_null($communityId)){
		try{
				$connect->beginTransaction();
				$stmt = $connect->prepare("SELECT event_id,community_id, student_id, event_access, event_title, event_description, event_location, event_address, event_date, event_time, event_photo, date_created FROM events
											INNER JOIN collegestudent ON events.student_id = collegestudent.id
											INNER JOIN event_type ON events.event_type_id =  event_type.event_type_id
											WHERE college_id = ? AND event_type.event_type= ? AND community_id= ?");
				$stmt->bindParam(1,$collegeId,PDO::PARAM_INT);
				$stmt->bindParam(2,$eType,PDO::PARAM_STR);
				$stmt->bindParam(3,$communityId,PDO::PARAM_INT);
				$stmt->execute();
				$connect->commit();
				return $stmt->fetchAll(PDO::FETCH_ASSOC);
		}catch(Exception $e){
			throw $e;
		}
	}elseif(is_null($eType) && !is_null($communityId)){
		try{
				$connect->beginTransaction();
				$stmt = $connect->prepare("SELECT event_id,community_id, student_id, event_access, event_title, event_description, event_location, event_address, event_date, event_time, event_photo, date_created FROM events
											INNER JOIN collegestudent ON events.student_id = collegestudent.id
											WHERE college_id = ? AND community_id= ?");
				$stmt->bindParam(1,$collegeId,PDO::PARAM_INT);
				$stmt->bindParam(2,$communityId,PDO::PARAM_INT);
				$stmt->execute();
				$connect->commit();
				return $stmt->fetchAll(PDO::FETCH_ASSOC);
		}catch(Exception $e){
			throw $e;
		}		
	}

}
function get_event($collegeId,$communityId = NULL,$eventId){
	global $connect;
	if (!is_null($communityId)) {
		try{
				$connect->beginTransaction();
				$stmt = $connect->prepare("SELECT event_id,event_type.event_type,communities.community_id, communities.community_name,communities.category_id,communities.community_category, userName, collegestudent.id, event_access, event_title, event_description, event_location, event_address, event_date, event_time, event_photo, events.date_created FROM events 
											INNER JOIN collegestudent ON events.student_id = collegestudent.id
		                                    INNER JOIN communities ON events.community_id =  communities.community_id
		                                    INNER JOIN event_type ON events.event_type_id =  event_type.event_type_id
											WHERE events.college_id=? AND events.community_id = ?  AND event_id = ?");
				$stmt->bindParam(1,$collegeId,PDO::PARAM_INT);
				$stmt->bindParam(2,$communityId,PDO::PARAM_INT);
				$stmt->bindParam(3,$eventId,PDO::PARAM_INT);
				$stmt->execute();
				$connect->commit();
				return $stmt->fetch(PDO::FETCH_ASSOC);

		}catch(Exception $e){
			throw $e;
		}
	}else{
		try{
				$connect->beginTransaction();
				$stmt = $connect->prepare("SELECT  community_id, event_id, userName, collegestudent.id, event_type.event_type,event_access, event_title, event_description, event_location, event_address, event_date, event_time, event_photo, events.date_created FROM events 
											INNER JOIN collegestudent ON events.student_id = collegestudent.id
											INNER JOIN event_type ON events.event_type_id = event_type.event_type_id
											WHERE events.college_id=? AND event_id = ?");
				$stmt->bindParam(1,$collegeId,PDO::PARAM_INT);
				$stmt->bindParam(2,$eventId,PDO::PARAM_INT);
				$stmt->execute();
				$connect->commit();
				return $stmt->fetch(PDO::FETCH_ASSOC);

		}catch(Exception $e){
			throw $e;
		}	
	}

}
function event_attendees($eventId){
	global $connect;
		try{
				$connect->beginTransaction();
				$stmt = $connect->prepare("SELECT event_attendees.student_id,collegestudent.id, userName FROM event_attendees 
											INNER JOIN collegestudent ON event_attendees.student_id = collegestudent.id
											WHERE event_id = ?");
				$stmt->bindParam(1,$eventId,PDO::PARAM_INT);
				$stmt->execute();
				$connect->commit();
				return $stmt->fetchAll(PDO::FETCH_ASSOC);
		}catch(Exception $e){
			throw $e;
		}

}
function get_event_comments($collegeId, $eventId){
	global $connect;
	try{
			$connect->beginTransaction();
			$stmt = $connect->prepare("SELECT e_comment_id,userName, student_id, comment, post_date  FROM event_comments
										INNER JOIN collegestudent ON event_comments.student_id = collegestudent.id
										WHERE college_id = ? AND event_id=? ORDER BY post_date ASC");
			$stmt->bindParam(1,$collegeId,PDO::PARAM_INT);
			$stmt->bindParam(2,$eventId,PDO::PARAM_INT);
			$stmt->execute();
			$connect->commit();
			return $stmt->fetchAll(PDO::FETCH_ASSOC);

	}catch(Exception $e){
		throw $e;
	}
}
function get_all_reviews($collegeId,$category = NULL,$ratings = NULL){
	global $connect;
	if (!is_null($category)) {
		try{
				$connect->beginTransaction();
				$stmt = $connect->prepare("SELECT review_id, student_id, collegestudent.userName, review_ratings.rating, reviews_categories.review_category,review_description, date_created FROM reviews
											INNER JOIN collegestudent ON reviews.student_id = collegestudent.id
											INNER JOIN review_ratings ON reviews.review_rating_id = review_ratings.rating_id
	                                        INNER JOIN reviews_categories ON reviews.review_category_id =  reviews_categories.review_category_id
											WHERE college_id = ? AND reviews_categories.review_category_id= ?");
				$stmt->bindParam(1,$collegeId,PDO::PARAM_INT);
				$stmt->bindParam(2,$category,PDO::PARAM_INT);
				$stmt->execute();
				$connect->commit();
				return $stmt->fetchAll(PDO::FETCH_ASSOC);
		}catch(Exception $e){
			throw $e;
		}
	}elseif(!is_null($ratings)){
		try{
				$connect->beginTransaction();
				$stmt = $connect->prepare("SELECT review_id, student_id, collegestudent.userName, review_ratings.rating, reviews_categories.review_category,review_description, date_created FROM reviews
											INNER JOIN collegestudent ON reviews.student_id = collegestudent.id
											INNER JOIN review_ratings ON reviews.review_rating_id = review_ratings.rating_id
	                                        INNER JOIN reviews_categories ON reviews.review_category_id =  reviews_categories.review_category_id
											WHERE college_id = ? AND review_ratings.rating_id = ?");
				$stmt->bindParam(1,$collegeId,PDO::PARAM_INT);
				$stmt->bindParam(2,$ratings,PDO::PARAM_INT);
				$stmt->execute();
				$connect->commit();
				return $stmt->fetchAll(PDO::FETCH_ASSOC);
		}catch(Exception $e){
			throw $e;
		}
	}else{
		try{
				$connect->beginTransaction();
				$stmt = $connect->prepare("SELECT review_id, student_id, collegestudent.userName, review_ratings.rating, reviews_categories.review_category,review_description, date_created FROM reviews
											INNER JOIN collegestudent ON reviews.student_id = collegestudent.id
											INNER JOIN review_ratings ON reviews.review_rating_id = review_ratings.rating_id
	                                        INNER JOIN reviews_categories ON reviews.review_category_id =  reviews_categories.review_category_id
											WHERE college_id = ?");
				$stmt->bindParam(1,$collegeId,PDO::PARAM_INT);
				$stmt->execute();
				$connect->commit();
				return $stmt->fetchAll(PDO::FETCH_ASSOC);
		}catch(Exception $e){
			throw $e;
		}		
	}

}
function get_all_community_discussions($communityId = NULL, $majorId = NULL,$storyId =NULL){
	global $connect;
	if (!is_null($communityId)) {
		try{
				$connect->beginTransaction();
				$stmt = $connect->prepare("SELECT c_discussion_id, student_id, userName, c_discussion_post, post_date FROM community_discussions 
											INNER JOIN collegestudent ON community_discussions.student_id = collegestudent.id
											WHERE community_id = ? ORDER BY post_date DESC");
				$stmt->bindParam(1,$communityId,PDO::PARAM_INT);
				$stmt->execute();
				$connect->commit();
				return $stmt->fetchAll(PDO::FETCH_ASSOC);
		}catch(Exception $e){
			throw $e;
		}
	}elseif(!is_null($majorId)){
		try{
				$connect->beginTransaction();
				$stmt = $connect->prepare("SELECT c_discussion_id, student_id, userName, c_discussion_post, post_date FROM community_discussions 
											INNER JOIN collegestudent ON community_discussions.student_id = collegestudent.id
											WHERE major_id = ? ORDER BY post_date DESC");
				$stmt->bindParam(1,$majorId,PDO::PARAM_INT);
				$stmt->execute();
				$connect->commit();
				return $stmt->fetchAll(PDO::FETCH_ASSOC);
		}catch(Exception $e){
			throw $e;
		}	
	}elseif(!is_null($storyId)){
		try{
				$connect->beginTransaction();
				$stmt = $connect->prepare("SELECT c_discussion_id, student_id, c_discussion_title, c_discussion_post, post_date FROM community_discussions 
											WHERE community_id = ? ORDER BY post_date DESC");
				$stmt->bindParam(1,$storyId,PDO::PARAM_INT);
				$stmt->execute();
				$connect->commit();
				return $stmt->fetchAll(PDO::FETCH_ASSOC);
		}catch(Exception $e){
			throw $e;
		}	
	}

}

function get_community_discussion($communityId = NULL, $majorId = NULL, $storyId = NULL,$c_discussion_id){
	global $connect;
	if (!is_null($communityId)) {
		try{
				$connect->beginTransaction();
				$stmt = $connect->prepare("SELECT collegestudent.userName, student_id, c_discussion_post, post_date  FROM community_discussions
											INNER JOIN collegestudent ON community_discussions.student_id = collegestudent.id
											WHERE community_id = ? AND c_discussion_id=?");
				$stmt->bindParam(1,$communityId,PDO::PARAM_INT);
				$stmt->bindParam(2,$c_discussion_id,PDO::PARAM_INT);
				$stmt->execute();
				$connect->commit();
				return $stmt->fetch(PDO::FETCH_ASSOC);

		}catch(Exception $e){
			throw $e;
		}
	}elseif(!is_null($majorId)){
		try{
				$connect->beginTransaction();
				$stmt = $connect->prepare("SELECT collegestudent.userName, student_id, c_discussion_post, post_date  FROM community_discussions
											INNER JOIN collegestudent ON community_discussions.student_id = collegestudent.id
											WHERE major_id = ? AND c_discussion_id=?");
				$stmt->bindParam(1,$majorId,PDO::PARAM_INT);
				$stmt->bindParam(2,$c_discussion_id,PDO::PARAM_INT);
				$stmt->execute();
				$connect->commit();
				return $stmt->fetch(PDO::FETCH_ASSOC);

		}catch(Exception $e){
			throw $e;
		}	
	}elseif(!is_null($storyId)){
		try{
				$connect->beginTransaction();
				$stmt = $connect->prepare("SELECT  c_discussion_title,c_discussion_post, post_date  FROM community_discussions
											WHERE community_id = ? AND c_discussion_id=?");
				$stmt->bindParam(1,$storyId,PDO::PARAM_INT);
				$stmt->bindParam(2,$c_discussion_id,PDO::PARAM_INT);
				$stmt->execute();
				$connect->commit();
				return $stmt->fetch(PDO::FETCH_ASSOC);

		}catch(Exception $e){
			throw $e;
		}	
	}

}
function get_all_community_discussion_replies($collegeId, $communityId = NULL, $majorId = NULL,$c_discussion_id){
	global $connect;
	if (!is_null($communityId)) {
		try{
				$connect->beginTransaction();
				$stmt = $connect->prepare("SELECT c_discussion_reply_id, student_id, userName, reply_post, post_date FROM c_discussion_replies 
											INNER JOIN collegestudent ON c_discussion_replies.student_id = collegestudent.id
											WHERE community_id = ? AND college_id = ? AND c_discussion_id = ? ORDER BY post_date DESC");
				$stmt->bindParam(1,$communityId,PDO::PARAM_INT);
				$stmt->bindParam(2,$collegeId,PDO::PARAM_INT);
				$stmt->bindParam(3,$c_discussion_id,PDO::PARAM_INT);
				$stmt->execute();
				$connect->commit();
				return $stmt->fetchAll(PDO::FETCH_ASSOC);
		}catch(Exception $e){
			throw $e;
		}
	}elseif(!is_null($majorId)){
		try{
				$connect->beginTransaction();
				$stmt = $connect->prepare("SELECT c_discussion_reply_id, student_id, userName, reply_post, post_date FROM c_discussion_replies 
											INNER JOIN collegestudent ON c_discussion_replies.student_id = collegestudent.id
											WHERE major_id = ? AND college_id = ? AND c_discussion_id = ? ORDER BY post_date DESC");
				$stmt->bindParam(1,$majorId,PDO::PARAM_INT);
				$stmt->bindParam(2,$collegeId,PDO::PARAM_INT);
				$stmt->bindParam(3,$c_discussion_id,PDO::PARAM_INT);
				$stmt->execute();
				$connect->commit();
				return $stmt->fetchAll(PDO::FETCH_ASSOC);
		}catch(Exception $e){
			throw $e;
		}
	}

}
function get_all_community_discussion_r_replies($collegeId, $communityId = NULL, $majorId = NULL, $c_discussion_id,$c_discussion_reply_id){
	global $connect;
	if (!is_null($communityId)) {
		try{
				$connect->beginTransaction();
				$stmt = $connect->prepare("SELECT r_reply_id, c_discussion_reply_id, student_id, userName, r_reply_post, post_date FROM c_discussion_r_reply 
											INNER JOIN collegestudent ON c_discussion_r_reply.student_id = collegestudent.id
											WHERE community_id = ? AND college_id = ? AND c_discussion_id = ? AND c_discussion_reply_id = ? ORDER BY post_date ASC");
				$stmt->bindParam(1,$communityId,PDO::PARAM_INT);
				$stmt->bindParam(2,$collegeId,PDO::PARAM_INT);
				$stmt->bindParam(3,$c_discussion_id,PDO::PARAM_INT);
				$stmt->bindParam(4,$c_discussion_reply_id,PDO::PARAM_INT);
				$stmt->execute();
				$connect->commit();
				return $stmt->fetchAll(PDO::FETCH_ASSOC);
		}catch(Exception $e){
			throw $e;
		}
	}elseif(!is_null($majorId)){
		try{
				$connect->beginTransaction();
				$stmt = $connect->prepare("SELECT r_reply_id, c_discussion_reply_id, student_id, userName, r_reply_post, post_date FROM c_discussion_r_reply 
											INNER JOIN collegestudent ON c_discussion_r_reply.student_id = collegestudent.id
											WHERE major_id = ? AND college_id = ? AND c_discussion_id = ? AND c_discussion_reply_id = ? ORDER BY post_date ASC");
				$stmt->bindParam(1,$majorId,PDO::PARAM_INT);
				$stmt->bindParam(2,$collegeId,PDO::PARAM_INT);
				$stmt->bindParam(3,$c_discussion_id,PDO::PARAM_INT);
				$stmt->bindParam(4,$c_discussion_reply_id,PDO::PARAM_INT);
				$stmt->execute();
				$connect->commit();
				return $stmt->fetchAll(PDO::FETCH_ASSOC);
		}catch(Exception $e){
			throw $e;
		}
	}

}
function get_profile_info($studentId){
	global $connect;
	try{
			$connect->beginTransaction();
			$stmt = $connect->prepare("SELECT id, collegeid, uni_name,firstName,lastName,userName, email,about,gender,location,grad_year,major_id,major,question_1,question_2,question_3  FROM profile_about_me 
										INNER JOIN collegestudent JOIN collegeList ON profile_about_me.student_id = collegestudent.id AND collegestudent.collegeId = collegeList.college_id
										INNER JOIN majorsLists ON profile_about_me.major_id = majorsLists.majorList_id
										WHERE profile_about_me.student_id = ?");
			$stmt->bindParam(1,$studentId,PDO::PARAM_INT);
			$stmt->execute();
			$connect->commit();
			return $stmt->fetch(PDO::FETCH_ASSOC);

	}catch(Exception $e){
		throw $e;
	}
}
function get_interests($studentId){
	global $connect;
	try{
			$connect->beginTransaction();
			$stmt = $connect->prepare("SELECT uni_name,interests.category_id, category, css_style  FROM interests
										INNER JOIN categories ON interests.category_id = categories.category_id 
										INNER JOIN collegestudent JOIN collegeList ON interests.student_id = collegestudent.id AND collegestudent.collegeId = collegeList.college_id
										WHERE student_id = ?");
			$stmt->bindParam(1,$studentId,PDO::PARAM_INT);
			$stmt->execute();
			$connect->commit();
			return $stmt->fetchAll(PDO::FETCH_ASSOC);

	}catch(Exception $e){
		throw $e;
	}
}
function get_user_communities($studentId,$all=null){
	global $connect;
	if (is_null($all)) {
		try{
				$connect->beginTransaction();
				$stmt = $connect->prepare("SELECT uni_name,community_members.community_id, community_name, category_id,community_category,community_color  FROM community_members
											INNER JOIN communities JOIN collegeList ON community_members.community_id = communities.community_id AND communities.college_id = collegeList.college_id
											WHERE student_id = ? AND status = 1");
				$stmt->bindParam(1,$studentId,PDO::PARAM_INT);
				$stmt->execute();
				$connect->commit();
				return $stmt->fetchAll(PDO::FETCH_ASSOC);

		}catch(Exception $e){
			throw $e;
		}
	}else{
		try{
				$connect->beginTransaction();
				$stmt = $connect->prepare("SELECT uni_name,community_members.community_id, community_name, category_id,community_category,community_color  FROM community_members
											INNER JOIN communities JOIN collegeList ON community_members.community_id = communities.community_id AND communities.college_id = collegeList.college_id
											WHERE student_id = ?");
				$stmt->bindParam(1,$studentId,PDO::PARAM_INT);
				$stmt->execute();
				$connect->commit();
				return $stmt->fetchAll(PDO::FETCH_ASSOC);

		}catch(Exception $e){
			throw $e;
		}	
	}

}
//returns an array of basic user info
function get_user_info($userId){
	global $connect;
	try{
			$connect->beginTransaction();
			$stmt = $connect->prepare("SELECT id,firstName, lastName, userName, email, token, verified, uni_name AS university
													  FROM collegestudent INNER JOIN collegeList ON collegestudent.collegeId = collegeList.college_id WHERE id=?");
			$stmt->bindParam(1,$userId,PDO::PARAM_INT);
			$stmt->execute();
			$connect->commit();
			return $stmt->fetch(PDO::FETCH_ASSOC);

	}catch(Exception $e){
		throw $e;
	}
}
function get_user_count($collegeId = NULL, $communityId = NULL, $eventId = NULL){
	global $connect;
	if (!is_null($collegeId)) {
		try{
				$connect->beginTransaction();
				$stmt = $connect->prepare("SELECT COUNT(*) FROM collegestudent WHERE collegeId = ?");
				$stmt->bindParam(1,$collegeId,PDO::PARAM_INT);
				$stmt->execute();
				$connect->commit();
				return $stmt->fetchColumn();
		}catch(Exception $e){
			throw $e;
		}
	}elseif (!is_null($communityId)) {
		try{
				$connect->beginTransaction();
				$stmt = $connect->prepare("SELECT COUNT(*) FROM community_members WHERE community_id= ? AND status = 1");
				$stmt->bindParam(1,$communityId,PDO::PARAM_INT);
				$stmt->execute();
				$connect->commit();
				return $stmt->fetchColumn();

		}catch(Exception $e){
			throw $e;
		}
	}elseif (!is_null($eventId)) {
		try{
				$connect->beginTransaction();
				$stmt = $connect->prepare("SELECT COUNT(*) FROM event_attendees WHERE event_id = ?");
				$stmt->bindParam(1,$eventId,PDO::PARAM_INT);
				$stmt->execute();
				$connect->commit();
				return $stmt->fetchColumn();

		}catch(Exception $e){
			throw $e;
		}
	}

}
//post time - displays time when a post or event was created
function post_time($date1){
    $date2 = time();
    $diff = abs($date2 - strtotime($date1));

    $years = floor($diff / (365*60*60*24));
    $months = floor(($diff - $years * 365*60*60*24) / (30*60*60*24));
    $weeks = floor(($diff - $years * 365*60*60*24 - $months*30*60*60*24)/ (7*60*60*24));
    $days = floor(($diff - $years * 365*60*60*24 - $months*30*60*60*24 - $weeks*7*60*60*24)/ (60*60*24));
    $hours = floor(($diff - $years * 365*60*60*24 - $months*30*60*60*24 - $days*60*60*24)/ (60*60));
    $minutes = floor(($diff - $years * 365*60*60*24 - $months*30*60*60*24 - $days*60*60*24 - $hours*60*60)/ (60));

    if ($years > 0) {
        return "Over " . $years . " yrs";
    }elseif($months > 0){
        return date("M d\, Y \@h:i A",strtotime($date1));
    }elseif($weeks > 0){
        return date("M d\, Y \@h:i A",strtotime($date1));
    }elseif($days > 0){
        return date("D M d \@h:i A",strtotime($date1));
    }elseif($hours > 0){
        return $hours . " hrs ago";
    }elseif($minutes > 0){
        return $minutes . "m ago";
    }else{
    	return "Just now";
    }
}
//remove functions
function remove_item($type,$userId,$id){
	global $connect;

	if ($type == 'discussion') {
		try {
			$connect->beginTransaction();
			$stmt = $connect->query("DELETE FROM `discussion_post`  WHERE d_post_id = $id AND student_id = $userId");
			$connect->commit();	
			return true;
		} catch (Exception $e) {
			throw $e;
		}
	}elseif($type == 'event'){
		try {
			$connect->beginTransaction();
			$stmt = $connect->query("DELETE FROM `events`  WHERE event_id = $id AND student_id = $userId");
			$connect->commit();	
			return true;
		} catch (Exception $e) {
			throw $e;
		}
	}elseif($type == 'c_discussion'){
		try {
			$connect->beginTransaction();
			$stmt = $connect->query("DELETE FROM `community_discussions`  WHERE c_discussion_id = $id AND student_id = $userId");
			$connect->commit();
			return true;	
		} catch (Exception $e) {
			throw $e;
		}		
	}elseif($type == 'review'){
		try {
			$connect->beginTransaction();
			$stmt = $connect->query("DELETE FROM `reviews`  WHERE review_id = $id  AND student_id = $userId");
			$connect->commit();
			return true;
		} catch (Exception $e) {
			throw $e;
		}		
	}elseif($type == 'c_discussion_reply'){
		try {
			$connect->beginTransaction();
			$stmt = $connect->query("DELETE FROM `c_discussion_replies`  WHERE c_discussion_reply_id = $id AND student_id = $userId");
			$connect->commit();	
			return true;
		} catch (Exception $e) {
			throw $e;
		}			
	}elseif($type == 'c_discussion_reply_comment'){
		try {
			$connect->beginTransaction();
			$stmt = $connect->query("DELETE FROM `c_discussion_r_reply`  WHERE r_reply_id = $id AND student_id = $userId");
			$connect->commit();
			return true;
		} catch (Exception $e) {
			throw $e;
		}		
	}elseif($type == 'discussion_reply'){
		try {
			$connect->beginTransaction();
			$stmt = $connect->query("DELETE FROM `discussion_replies`  WHERE d_reply_id = $id AND student_id = $userId");
			$connect->commit();		
			return true;
		} catch (Exception $e) {
			throw $e;
		}	
	}elseif($type == 'discussion_reply_comment'){
		try {
			$connect->beginTransaction();
			$stmt = $connect->query("DELETE FROM `discussion_r_replies`  WHERE r_reply_id = $id AND student_id = $userId");
			$connect->commit();	
			return true;	
		} catch (Exception $e) {
			throw $e;
		}
	}elseif($type == 'event_comment'){
		try {
			$connect->beginTransaction();
			$stmt = $connect->query("DELETE FROM `event_comments`  WHERE e_comment_id = $id  AND student_id = $userId");
			$connect->commit();
			return true;
		} catch (Exception $e) {
			throw $e;
		}		
	}elseif($type == 'community'){
		try {
			$connect->beginTransaction();
			$stmt = $connect->query("DELETE FROM `communities`  WHERE community_id = $id  AND creator_id = $userId");
			$connect->commit();
			return true;
		} catch (Exception $e) {
			throw $e;
		}		
	}elseif($type == 'community_member'){
		try {
			$connect->beginTransaction();
			$stmt = $connect->query("DELETE FROM `community_members`  WHERE community_id = $id  AND student_id = $userId");
			$connect->commit();
			return true;
		} catch (Exception $e) {
			throw $e;
		}	
	}
}

//Popular forum topic
function get_popular_forum_topic($collegeId){
	$topics = get_discussion_topics();
	$array = array();
	foreach ($topics as $key) {
		$topicId = $key['discussion_topic_id'];
		global $connect;
		try{
				$sql = "SELECT COUNT(*)  FROM discussion_post WHERE college_id = $collegeId AND d_topic_id = $topicId";
				$stmt = $connect->query($sql);
				$result = $stmt->fetchColumn();
				$array[$key['discussion_topic_id']] = intval($result);
		}catch(Exception $e){
			throw $e;
		}
	}
	arsort($array);
	return $array;
}
function get_top_communities($collegeId){
	$communities = get_all_communities($collegeId,NULL);
	$array = array();
	foreach ($communities as $key) {
		$commId = $key['community_id'];
		$count = 0;
		global $connect;
		try{
				$sql = "SELECT COUNT(*)  FROM community_members WHERE community_id = $commId AND status = 1";
				$stmt = $connect->query($sql);
				$result = $stmt->fetchColumn();
				$count += intval($result);
				$sql = "SELECT COUNT(*)  FROM community_discussions WHERE community_id = $commId";
				$stmt = $connect->query($sql);
				$result = $stmt->fetchColumn();
				$count += intval($result);
				$sql = "SELECT COUNT(*)  FROM events WHERE community_id = $commId";
				$stmt = $connect->query($sql);
				$result = $stmt->fetchColumn();
				$count += intval($result);
				$sql = "SELECT COUNT(*)  FROM c_discussion_replies WHERE community_id = $commId";
				$stmt = $connect->query($sql);
				$result = $stmt->fetchColumn();
				$count += intval($result);
				$array[$key['community_id']] = $count;
		}catch(Exception $e){
			throw $e;
		}
	}
	arsort($array);
	return $array;
}
//
function get_category_count($categoryId){
	global $connect;
	try{
			$sql = "SELECT COUNT(*)  FROM interests WHERE category_id = $categoryId";
			$stmt = $connect->query($sql);
			return $stmt->fetchColumn();
	}catch(Exception $e){
		throw $e;
	}
}
// get user's favorites
function get_user_favorites($userId){
	global $connect;
	try{
		$sql = "SELECT * FROM favorites WHERE user_id = $userId";
		$stmt = $connect->query($sql);
		$result = $stmt->fetchAll(PDO::FETCH_ASSOC);
		$array = array();
		if (!empty($result)) {
			foreach ($result as $key) {
					$array['community_id'][] = $key['community_id'];
					$array['discussion_id'][] = $key['discussion_id'];
					$array['c_discussion_id'][] = $key['c_discussion_id'];
					$array['event_id'][] = $key['event_id'];
			}

		return $array;
		}else{
			return false;
		}

	}catch(Exception $e){
		throw $e;
	}
}
//school followers
function get_followed_schools($userId,$collegeId=NULL){
	global $connect;
	if (is_null($collegeId)) {
		try{
			$sql = "SELECT uni_name,school_followers.college_id FROM school_followers
					INNER JOIN collegeList ON school_followers.college_id = collegeList.college_id
					INNER JOIN collegestudent ON school_followers.user_id = collegestudent.id
					 WHERE user_id = ?";
			$stmt = $connect->prepare($sql);
			$stmt->bindParam(1,$userId,PDO::PARAM_INT);
			$stmt->execute();
			return $stmt->fetchAll(PDO::FETCH_ASSOC);
		}catch(Exception $e){
			throw $e;
		} 
	}else{
		try{
			$sql = "SELECT uni_name,school_followers.college_id FROM school_followers
					INNER JOIN collegeList ON school_followers.college_id = collegeList.college_id
					INNER JOIN collegestudent ON school_followers.user_id = collegestudent.id
					 WHERE user_id = ? AND school_followers.college_id = ?";
			$stmt = $connect->prepare($sql);
			$stmt->bindParam(1,$userId,PDO::PARAM_INT);
			$stmt->bindParam(2,$collegeId,PDO::PARAM_INT);
			$stmt->execute();
			return $stmt->fetch(PDO::FETCH_ASSOC);
		}catch(Exception $e){
			throw $e;
		} 	
	}

}
function get_followed_member($userId,$friendId){
	global $connect;

	try {
			$sql = "SELECT follower_id FROM friend_followers WHERE user_id = ? AND friend_id = ?";
			$stmt = $connect->prepare($sql);
			$stmt->bindParam(1,$userId,PDO::PARAM_INT);
			$stmt->bindParam(2,$friendId,PDO::PARAM_INT);
			$stmt->execute();
			return $stmt->fetch(PDO::FETCH_ASSOC);
	} catch (Exception $e) {
		throw $e;
	}
}
//get liked communities

function get_liked_discussions($userId){
	$liked_discussions = get_user_favorites($userId)['discussion_id'];
	global $connect;
	if (!empty(array_filter($liked_discussions))) {
		try{
			$sqlStr = "SELECT uni_name,d_post_id,discussion_post.college_id,discussion_title,discussion_post,discussion_post.student_id,username,post_date FROM discussion_post 
				INNER JOIN collegestudent ON discussion_post.student_id = collegestudent.id 
				INNER JOIN collegeList ON discussion_post.college_id = collegeList.college_id WHERE ";
			$sqlStr .= "d_post_id IN ('" . implode("', '", $liked_discussions) . "')";
			$stmt = $connect->query($sqlStr);
			return $stmt->fetchAll(PDO::FETCH_ASSOC); 
		}catch(Exception $e){
			throw $e;
		} 
	}

}
//get like community discussions
function get_liked_community_discussions($userId){
	$liked_discussions = get_user_favorites($userId)['c_discussion_id'];
	global $connect;
	if (!empty(array_filter($liked_discussions))) {
		try{
			$sqlStr = "SELECT uni_name,community_id,c_discussion_id,community_discussions.college_id,major_id,c_discussion_title,c_discussion_post,photo,community_discussions.student_id,username,post_date FROM community_discussions 
				INNER JOIN collegestudent ON community_discussions.student_id = collegestudent.id 
				INNER JOIN collegeList ON community_discussions.college_id = collegeList.college_id WHERE ";
			$sqlStr .= "c_discussion_id IN ('" . implode("', '", $liked_discussions) . "')";
			$stmt = $connect->query($sqlStr);
			return $stmt->fetchAll(PDO::FETCH_ASSOC); 
		}catch(Exception $e){
			throw $e;
		} 
	}

}
//get liked events
function get_liked_events($userId){
	$liked_events = get_user_favorites($userId)['event_id'];
	global $connect;
	if (!empty(array_filter($liked_events))) {
		try{
			$sqlStr = "SELECT uni_name,community_id,event_id,event_title,event_description,event_date,event_location,event_photo FROM events 
				INNER JOIN collegeList ON events.college_id = collegeList.college_id WHERE ";
			$sqlStr .= "event_id IN ('" . implode("', '", $liked_events) . "')";
			$stmt = $connect->query($sqlStr);
			return $stmt->fetchAll(PDO::FETCH_ASSOC); 
		}catch(Exception $e){
			throw $e;
		} 
	}

}

function get_community_name($communityName,$collegeId){
	global $connect;
	try{
		$connect->beginTransaction();
		$sql = "SELECT community_name FROM communities WHERE community_name = ? AND college_id = ?";
		$stmt = $connect->prepare($sql);
		$stmt->bindParam(1,$communityName,PDO::PARAM_STR);
		$stmt->bindParam(2,$collegeId,PDO::PARAM_INT);
		$stmt->execute();
		$connect->commit();
		return $stmt->fetchAll(PDO::FETCH_ASSOC);
	}catch(Exception $e){
		throw $e;
	}
}
//notifications

// function getUnreadNumber($userId){
// 	global $connect;
// 	try{
// 			$connect->beginTransaction();
// 			$stmt = $connect->prepare("SELECT *  FROM notifications
// 										WHERE user_id = ?");
// 			$stmt->bindParam(1,$userId,PDO::PARAM_INT);
// 			$stmt->execute();
// 			$connect->commit();
// 			return $stmt->fetchAll(PDO::FETCH_ASSOC);

// 	}catch(Exception $e){
// 		throw $e;
// 	}
// }

//redirects to path
function redirect($path,$extra = []){
		header('Location:'.$path);
	  	exit(); 
}

function findUserByEmail($userEmail){
	global $connect;
	  try{
		$connect->beginTransaction();
	      $query = "SELECT * FROM collegestudent WHERE email = ?";
	      $stmt = $connect->prepare($query);
	       $stmt->bindParam(1,$userEmail);
	      $stmt->execute();
	      $connect->commit();
	      return $stmt->fetch(PDO::FETCH_ASSOC);
	  }catch(\Exception $e){
	    throw $e;
	  }
}
function findUserById($userId){
	global $connect;
	  try{
	  	$connect->beginTransaction();
	      $query = "SELECT * FROM collegestudent WHERE id = ?";
	      $stmt = $connect->prepare($query);
	       $stmt->bindParam(1,$userId);
	      $stmt->execute();
	      $connect->commit();
	      return $stmt->fetch(PDO::FETCH_ASSOC);
	  }catch(\Exception $e){
	    throw $e;
	  }
}


function sendVerificationEmail($collegeEmail,$veriCode){
	global $connect;
	try{
		$user = findUserByEmail($collegeEmail);
		$userInfo = get_user_info($user['id']);
  	}catch(Exception $e){
  	  	throw $e;
  	}
      $firstName = $userInfo['firstName'];
      $userId = $userInfo['id'];
    require_once('emailScript.php');

    $bodyContent = '<div style="text-align: center;">';
    $bodyContent .= '<div style="padding:15px 0;background:rgba(199,68,68,.85);border-top-left-radius:5px;border-top-right-radius: 5px;"><img style="height: 50px;width: 50px;" src="https://meetmycampus.com/warmpuppies/img/logo4.png"></div>';
    $bodyContent .= '<p>Hi ' . ucfirst($firstName) . ',</p>';
    $bodyContent .= '<p>Thanks for joining <strong>The MeetMyCampus Community<!/strong></p>';
    $bodyContent .= '<p>Enter the following 6-digit verification code: </p>';
    $bodyContent .= '<h3 style="font-weight:normal;padding:15px 15px; background:rgba(199,68,68,.7); margin:0 auto;color:#fff;width:85px;text-align: center;font-size:19px;letter-spacing: 1px;">' . $veriCode .'</h3>';
    $bodyContent .= '<p>Or <a  href="localhost/official_mmc/procedures/doVerify.php?code=' . $veriCode  . '&userID=' . $userId .'"'. '>Click here to verify your email</a> </p>';
    $bodyContent .= '</div>';

    $mail->Subject = $veriCode . ' is your verification code';
    $mail->Body    = $bodyContent;

    if(!$mail->send()) {
        $_SESSION['error_message'] = 'Oh No! There was an error. Please try again or Contact Us ';
        $$_SESSION['error_message'] .= 'Error Code: ' . $mail->ErrorInfo;
        redirect('/index.php');
   	} else{
   		return $userInfo;
    }
}

function sendResetPasswordEmail($user,$resetCode){

    $firstName = $user['firstName'];
    $userId = $user['id'];
    require_once('emailScript.php');
    $bodyContent = '<div style="text-align:center;font-size:20px;font-weight:bold; background:#f1f1f1
                  ;padding:40px;">';
    $bodyContent .= '<div><img style="height: 50px;width: 50px;" src="https://meetmycampus.com/warmpuppies/img/logo3.gif"></div>';
    $bodyContent .= '<p style="padding-bottom:10px;">So, you forgot your password huh?</p>';
    $bodyContent .= '<div style="border-radius:3px;border:solid 1px #c5c5c5;font-size:15px;font-weight:normal;background:#fff;text-align:center;width:500px;margin:0 auto;padding:30px;">';
    $bodyContent .= '<p style="margin-bottom:20px;">To reset your password, just click the link below.</p>';
    $bodyContent .= '<button style="border:solid 1px #c74444;border-radius:2px;color:#fff;font-weight:normal;padding:15px 15px; background:rgba(199,68,68,1);text-transform:uppercase;font-size:13px;text-align: center;"><a style="text-decoration:none;color:#fff;" href="localhost/official_mmc/procedures/doResetPassword.php?userID=' . $userId . '&resetCode='. $resetCode .'"'.'>Reset Password</a> </button>';
    $bodyContent .= '<p style="margin-top:50px;font-weight:bold;">Why do I have to reset my password? </p>';
    $bodyContent .= '<p>As a security measure, MeetMyCampus does not store your password. A unique link to reset your password has been generated for you to ensure password safety.</p>';
    $bodyContent .= '</div>';

    $bodyContent .= '</div>';

    $mail->Subject = 'forgot password?';
    $mail->Body    = $bodyContent;

    if(!$mail->send()) {
        $_SESSION['error_message'] = 'Oh No! There was an error. Please try again or Contact Us ';
        $$_SESSION['error_message'] .= 'Error Code: ' . $mail->ErrorInfo;
        redirect('../forgotpassword.php');
   	} else{
   		return true;
    }
}


function getVerifiedStatus($userId){
	global $connect;
	try{
		$connect->beginTransaction();
	 	 $results = $connect->prepare("SELECT verified FROM collegestudent WHERE id = ?");
	 	 $results->bindParam(1,$userId,PDO::PARAM_INT);
	  	 $results->execute();
	  	 $connect->commit();
	  	 return $results->fetch(PDO::FETCH_ASSOC);
  	}catch(Exception $e){
  	  	throw $e;
  	}

}

function getVeriCode($userId){
	global $connect;
	try{
		$connect->beginTransaction();
		$results = $connect->prepare("SELECT id, verification_code FROM verification WHERE id = ?");
		$results->bindParam(1,$userId,PDO::PARAM_STR);
		$results->execute();
		$connect->commit();
		return $results->fetch(PDO::FETCH_ASSOC);
  	}catch(Exception $e){
  	  	throw $e;
  	}
}

function getResetCode($userId){
	global $connect;
	try{
		$connect->beginTransaction();
		$results = $connect->prepare("SELECT reset_code FROM resetPassword WHERE id = ?");
		$results->bindParam(1,$userId,PDO::PARAM_STR);
		$results->execute();
		$connect->commit();
		return $results->fetch(PDO::FETCH_ASSOC);
  	}catch(Exception $e){
  	  	throw $e;
  	}
}


function updateVeriStatus($userId,$yes){
	global $connect;
	try{
		$connect->beginTransaction();
		$stmt = $connect->prepare("UPDATE collegestudent SET verified = ? WHERE id = ?");
		$stmt->bindParam(1,$yes,PDO::PARAM_STR);
		$stmt->bindParam(2,$userId,PDO::PARAM_INT);
		$return = $stmt->execute();
		$connect->commit();
		return $return;
  	}catch(Exception $e){
  	  	throw $e;
  	}
}

function updateVeriCode($userId,$veriCode){
	global $connect;
	try{
		$connect->beginTransaction();
        $stmt = $connect->prepare("UPDATE verification SET verification_code = ? WHERE id = ?");
		$stmt->bindParam(1,$veriCode,PDO::PARAM_INT);
		$stmt->bindParam(2,$userId,PDO::PARAM_INT);
		$return = $stmt->execute();
		$connect->commit();
		return $return;
  	}catch(Exception $e){
  	  	throw $e;
  	}
}

function updateResetCode($userId,$resetCode){
	global $connect;
	try{
		$connect->beginTransaction();
        $stmt = $connect->prepare("UPDATE resetPassword SET reset_code = ? WHERE id = ?");
		$stmt->bindParam(1,$resetCode,PDO::PARAM_INT);
		$stmt->bindParam(2,$userId,PDO::PARAM_INT);
		$return = $stmt->execute();
		$connect->commit();
		return $return;
  	}catch(Exception $e){
  	  	throw $e;
  	}
}

function updatePassword($userId,$hashed){
	global $connect;
	try{
		$connect->beginTransaction();
		$stmt = $connect->prepare("UPDATE collegestudent SET token = ? WHERE id = ?");
		$stmt->bindParam(1,$hashed,PDO::PARAM_STR);
		$stmt->bindParam(2,$userId,PDO::PARAM_INT);
		$return = $stmt->execute();
		$connect->commit();
		return $return;
  	}catch(Exception $e){
  	  	throw $e;
  	}
}
function deleteVeriCode($userId){
	global $connect;
	try{
		$connect->beginTransaction();
		$stmt = $connect->prepare("DELETE FROM verification WHERE id = ?");
		$stmt->bindParam(1,$userId,PDO::PARAM_INT);
		$return = $stmt->execute();
		$connect->commit();
		return $return;
  	}catch(Exception $e){
  	  	throw $e;
  	}
}

function deleteResetCode($userId){
	global $connect;
	try{
		$connect->beginTransaction();
		$stmt = $connect->prepare("DELETE FROM resetPassword WHERE id = ?");
		$stmt->bindParam(1,$userId,PDO::PARAM_INT);
		$return = $stmt->execute();
		$connect->commit();
		return $return;
  	}catch(Exception $e){
  	  	throw $e;
  	}
}

function authenticate_user(){
	if ((!isset($_COOKIE['user_id'])) || (!strlen($_COOKIE['user_id']) > 0)) {
		return false;
	}else{
		return true;
	}

}

function authorize_user($userId,$pageLink){
	if($_COOKIE['user_id'] != $userId){
	   	redirect($pageLink.$_COOKIE['user_id']);
	}
}



?>