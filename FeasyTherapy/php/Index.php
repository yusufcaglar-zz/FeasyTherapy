<?php
if(!isset($_SESSION))
		session_start();
	
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
	require_once 'Functions.php';
	$fun = new Functions();
			
	$data = $_POST;
	
	if(isset($data["operation"]) && !empty($data["operation"])) {
		$operation = $data["operation"];

		//isLoggedIn
		if($operation == 'isLoggedIn') {
			if(isset($_SESSION["id_number"])) {
				$response["result"] = "success";
				$response["message"] = "User already logged in";
				$response = json_encode($response);
			} else {
				$response["result"] = "failure";
				$response["message"] = "User not logged in";
				$response = json_encode($response);
			}
		
		//isLoggedIn
		} else if ($operation == 'login') {
			if(isset($_SESSION["id_number"])) {
				if($fun -> isAccountAvailableForLogin($_SESSION["id_number"])) {
					$response["result"] = "failure";
					$response["message"] = "User already logged in";
					$response["id_number"] = $_SESSION["id_number"];
					$response = json_encode($response);
				} else {
					session_unset();
					session_destroy();
					
					$response["result"] = "failure";
					$response["message"] = "Invaild Login Credentials";
					$response = json_encode($response);
				}
			} else {
				if(isset($data["id_number"]) && !empty($data["id_number"]) && 
					isset($data["password"]) && !empty($data["password"])) {
					$id_number = urldecode($data["id_number"]);
					$password = urldecode($data["password"]);
					
					if (!($fun -> idNumberCheck($id_number)) || !($fun -> passwordCheck($password)))
						$response = $fun -> getMsgInvalidParam();
					else
						$response = $fun -> login($id_number, $password);
				} else
					$response = $fun -> getMsgInvalidParam();
			}
			
		//getPatients	
		} else if ($operation == 'getPatients') {
			if(isset($_SESSION["id_number"]))
				$response = $fun -> getPatients();
			else {
				$response["result"] = "failure";
				$response["message"] = "User not logged in";
				$response = json_encode($response);
			}	
				
			
		//logout
		} else if ($operation == 'logout') {
					if (isset($_SESSION["id_number"])) {
						$fun -> logOffUser();
						
						session_unset();
						session_destroy();
					}
					
					$response = "";
				
		} else
			$response = $fun -> getMsgInvalidParam();
	} else
		$response = $fun -> getMsgInvalidParam();
	
	echo $response;
} else
	echo "GET";
?>