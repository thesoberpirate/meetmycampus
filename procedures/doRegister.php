<?php
require_once('../inc/bootstrap.php');

$collegeEmail = $university = $password = $firstName = $lastName = $hashed = $userName = $userMajor = $majorId = $createMajor = "";
$user_bio = $user_major = $user_major_id = $user_grad_year = $user_location = $user_interests = $user_communities = $createUser = $collegeId = $userId = 
$user_photo = $result= "";

$userId = $_COOKIE['user_id'];

if (isset($_COOKIE['college_id'])) { //check if cookie with user's college id is set
  $collegeId = intval($_COOKIE['college_id']);
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
  if (isset($_POST['setup_account'])) {
      $user_bio = trim(filter_input(INPUT_POST,"profile_bio",FILTER_SANITIZE_STRING));
      $user_major = ucfirst(trim(filter_input(INPUT_POST,"major",FILTER_SANITIZE_STRING)));
      $user_grad_year = trim(filter_input(INPUT_POST,"grad_year",FILTER_SANITIZE_STRING));
      $user_location = trim(filter_input(INPUT_POST,"location",FILTER_SANITIZE_STRING));
      $user_interests = filter_input(INPUT_POST, 'check_list', FILTER_SANITIZE_STRING, FILTER_REQUIRE_ARRAY);
      $user_communities = filter_input(INPUT_POST, 'sc_list', FILTER_SANITIZE_STRING, FILTER_REQUIRE_ARRAY);

      //Get major id from the major name, if major doesn't exist at school, create a major community at that school
      try {
        $stmt = $connect->prepare("SELECT major_list_id FROM majors_list WHERE major = ?");
        $stmt->bindParam(1,$user_major);
        $stmt->execute();
        $user_major_id = intval($stmt->fetchColumn());
      } catch (Exception $e) {
        throw $e;
      }

      //if major is in majors lists, then check if major is at user's school
      if ($user_major_id != "") {

          try {
            $stmt = $connect->prepare("SELECT major_id FROM majors WHERE major_id = ? and college_id = ?");
            $stmt->bindParam(1,$user_major_id,PDO::PARAM_INT);
            $stmt->bindParam(2,$collegeId,PDO::PARAM_INT);
            $stmt->execute();
            $result = $stmt->fetchColumn();
            // if major is not at the user's school, add it to majors table
            //add user to respective major community
            //TO-DO - DEBUG 
            if ($result == "") {
              $createdMajorId = create_major($collegeId,$user_major_id,$user_major); 
              join_community($createdMajorId,$userId,1);    
            }else{
              
            }
          } catch (Exception $e) {
            throw $e;
          }

      }else{              //if the the major is not in the majors list, then add it to list

          try {
            $stmt = $connect->prepare("INSERT INTO majors_list(`major`) VALUES(?)");
            $stmt->bindParam(1,$user_major,PDO::PARAM_STR);
            $stmt->execute();

            $user_major_id = intval($connect->lastInsertId());
            $createdMajorId = create_major($collegeId,$user_major_id,$user_major); 
            join_community($createdMajorId,$userId,1);    

          } catch (Exception $e) {
            throw $e;
          }

      }
      

      //insert basic profile info(bio,major id, grad year, location) into user_profile table, using create_profile function
      create_profile($userId, $user_major_id, $user_bio, $user_grad_year, $user_location, $user_photo);

      //loop through interests array, add each category id to interests table with corresponding student id
      foreach ($user_interests as $key) {
        follow_interest(intval($key),$userId);
      }

      //check if user checked off any communities. 
      //if yes, loop through array, add user to each community in array, if no, move on
      if ($user_communities != "") {
        foreach ($user_communities as $key) {
          $communityType = get_community(intval($key),$collegeId)['community_type'];
          if ($communityType = "public") {
            join_community(intval($key),$userId,1);
          }elseif($communityType = "private"){
            join_community(intval($key),$userId,2);
          }
        }
      }
      //set profile_build field name in users_profile table to 1, which means complete
      $stmt = $connect->prepare("UPDATE user_profile SET profile_build = 1 WHERE student_id = ?");
      $stmt->bindParam(1,$userId,PDO::PARAM_STR);
      $stmt->execute();

      //kill sessions related to profile builder and kill data array cookie
      setcookie('data_array', '', time()-(365*24*60*60),'/','localhost');
      session_unset();
      session_unset();
      //redirect user to home.php 
      redirect('../home.php');

  }else{
    $firstName = trim(filter_input(INPUT_POST,"firstName",FILTER_SANITIZE_STRING));
    $lastName = trim(filter_input(INPUT_POST,"lastName",FILTER_SANITIZE_STRING));
    $userName = trim(filter_input(INPUT_POST,"userName",FILTER_SANITIZE_STRING));
    $collegeEmail = trim(filter_input(INPUT_POST,"userCollegeEmail",FILTER_SANITIZE_EMAIL));
    $university = trim(filter_input(INPUT_POST,"userCollege",FILTER_SANITIZE_STRING));
    $password = trim(filter_input(INPUT_POST,"userCollegePassword",FILTER_SANITIZE_STRING));
    $hashed = password_hash($password, PASSWORD_DEFAULT);
    
    $_SESSION['first_name'] = $firstName;
    $_SESSION['last_name'] = $lastName;

    if($firstName == "" || $lastName == "" || $collegeEmail == "" || $password == "" || $userName == ""){
      if (isset($_SESSION['redirect_location']) && isset($_GET['status'])) {
        if ($_GET['status'] == 'not_signed_up') {
          $redirect = $_SESSION['redirect_location'];
          $_SESSION['not_signed_up_error'] = "Please fill in the required fields: First Name, Last Name, Email, University, Password";
          redirect($redirect);
        }
      }else{
          $_SESSION['error_message'] = "Please fill in the required fields: First Name, Last Name, Email, University, Password";
          redirect('../signup.php');    
      }
    }

    $enteredSchool = get_school_info($university);
    if(empty($enteredSchool)){
      if (isset($_SESSION['redirect_location']) && isset($_GET['status'])) {
        if ($_GET['status'] == 'not_signed_up') {
          $redirect = $_SESSION['redirect_location'];
          $_SESSION['not_signed_up_error'] = "Oh No! Seems like MeetMyCampus has yet to arrive at your school. Be patient, we'll get to you soon!" ;
          redirect($redirect);
        }
      }else{
          $_SESSION['error_message'] = "Oh No! Seems like MeetMyCampus has yet to arrive at your school. Be patient, we'll get to you soon!" ;
          redirect('../signup.php');    
      }
    }

    $user = findUserByEmail($collegeEmail);

    if(!empty($user)){
        if ($_GET['status'] == 'not_signed_up') {
          $redirect = $_SESSION['redirect_location'];
          $_SESSION['not_signed_up_error'] = "Oops! This email has already been used. Try Signing In";
          redirect($redirect);
      }else{
          $_SESSION['error_message'] = "Oops! This email has already been used. Try Signing In";
          redirect('../signup.php');    
      }
    }
    $checkUsername = check_username($userName);
    if(!empty($checkUsername)){
        if ($_GET['status'] == 'not_signed_up') {
          $redirect = $_SESSION['redirect_location'];
          $_SESSION['not_signed_up_error'] = "Oops! This username is taken. Try another one.";
          redirect($redirect);
      }else{
          $_SESSION['error_message'] = "Oops! This username is taken. Try another one.";
          redirect('../signup.php');    
      }
    }
    if ($_POST["address"] != "") {
           $_SESSION['error_message']  = "Bad form input";
            redirect('../index.php');
    }



        $createUser = create_user($enteredSchool['college_id'],$firstName,$lastName,$userName,$collegeEmail,$hashed,'college_student');
        
        if($createUser != ""){
              follow_school($enteredSchool['college_id'],$createUser);
              setcookie('username',$userName,time()+860000,'/', 'localhost'); 
              setcookie('user_id',$createUser,time()+860000,'/', 'localhost');
              setcookie('college_id',$enteredSchool['college_id'],time()+860000,'/', 'localhost');
              redirect('../build-profile.php');
        }
  }

}

?>