<?php
header('Access-Control-Allow-Origin: https://feasytherapy.com');

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
	if (isset($_POST["phpsessid"]) && !empty($_POST["phpsessid"])) {
		if (empty(session_id())) {
			session_id($_POST["phpsessid"]);
			session_start();
		} else {
			session_destroy();
			session_id($_POST["phpsessid"]);
			session_start();
		}
		
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
					$response["id"] = $_SESSION["id"];
					$response["id_number"] = $_SESSION["id_number"];
					$response["type"] = $_SESSION["type"];
					$response = json_encode($response);
				} else {
					$response["result"] = "failure";
					$response["message"] = "User not logged in";
					$response = json_encode($response);
				}
			
			/////////////////////// PHYSIOTHERAPIST ///////////////////////
			//login
			} else if ($operation == 'login') {
				if(isset($_SESSION["id_number"])) {
					if ($_SESSION["type"] == "physiotherapist") {
						if($fun -> isAccountAvailableForLogin($_SESSION["id_number"], "physiotherapist")) {
							$response["result"] = "failure";
							$response["message"] = "User already logged in";
							$response["id_number"] = $_SESSION["id_number"];
							$response = json_encode($response);
						} else {
							session_unset();
							session_destroy();
							
							$response["result"] = "failure";
							$response["message"] = "Invalid Login Credentials";
							$response = json_encode($response);
						}
					} else {
						$response["result"] = "failure";
						$response["message"] = "Logged as in another user role";
						$response = json_encode($response);
					}
				} else {
					if (isset($data["id_number"]) && !empty($data["id_number"]) && 
						isset($data["password"]) && !empty($data["password"])) {
						$id_number = urldecode($data["id_number"]);
						$password = urldecode($data["password"]);
						
						if (!($fun -> idNumberCheck($id_number)) || !($fun -> passwordCheck($password)))
							$response = $fun -> getMsgInvalidParam();
						else
							$response = $fun -> login($id_number, $password, "physiotherapist");
					} else
						$response = $fun -> getMsgInvalidParam();
				}
				
			//getPatients	
			} else if ($operation == 'getPatients') {
				if (isset($_SESSION["id_number"])) {
					if($_SESSION["type"] == "physiotherapist")
						$response = $fun -> getPatients();
					else {
						$response["result"] = "failure";
						$response["message"] = "User not authorized";
						$response = json_encode($response);
					}
				} else {
					$response["result"] = "failure";
					$response["message"] = "User not logged in";
					$response = json_encode($response);
				}
				
			//getExercise
			} else if ($operation === 'getPhysiotherapist') {
				if($_SESSION["type"] === "physiotherapist")
					$response = $fun -> getPhysiotherapist($_SESSION["id"]);
				else
					$response = $fun -> getMsgInvalidParam();
				
			//logout
			} else if ($operation == 'logout') {
				if (isset($_SESSION["id_number"])) {
					$fun -> logOffUser();
					
					session_unset();
					session_destroy();
				}
				
				$response = "";
				
			/////////////////////// ADMIN ///////////////////////
			} else if ($operation == 'admin_login') {
				if(isset($_SESSION["id_number"])) {
					if ($_SESSION["type"] == "admin") {
						if($fun -> isAccountAvailableForLogin($_SESSION["id_number"], "admin")) {
							$response["result"] = "failure";
							$response["message"] = "User already logged in";
							$response["id_number"] = $_SESSION["id_number"];
							$response = json_encode($response);
						} else {
							session_unset();
							session_destroy();
							
							$response["result"] = "failure";
							$response["message"] = "Invalid Login Credentials";
							$response = json_encode($response);
						}
					} else {
						$response["result"] = "failure";
						$response["message"] = "Logged as in another user role";
						$response = json_encode($response);
					}
				} else {
					if (isset($data["id_number"]) && !empty($data["id_number"]) && 
						isset($data["password"]) && !empty($data["password"])) {
						$id_number = urldecode($data["id_number"]);
						$password = urldecode($data["password"]);
						
						if (!($fun -> idNumberCheck($id_number)) || !($fun -> passwordCheck($password)))
							$response = $fun -> getMsgInvalidParam();
						else
							$response = $fun -> login($id_number, $password, "admin");
					} else
						$response = $fun -> getMsgInvalidParam();
				}
				
			/////////////////////// PATIENT ///////////////////////
			} else if ($operation == 'patient_login') {
				if(isset($_SESSION["id_number"])) {
					if ($_SESSION["type"] == "patient") {
						if($fun -> isAccountAvailableForLogin($_SESSION["id_number"], "patient")) {
							$response["result"] = "failure";
							$response["message"] = "User already logged in";
							$response["id_number"] = $_SESSION["id_number"];
							$response = json_encode($response);
						} else {
							session_unset();
							session_destroy();
							
							$response["result"] = "failure";
							$response["message"] = "Invalid Login Credentials";
							$response = json_encode($response);
						}
					} else {
						$response["result"] = "failure";
						$response["message"] = "Logged as in another user role";
						$response = json_encode($response);
					}
				} else {
					if (isset($data["id_number"]) && !empty($data["id_number"]) && 
						isset($data["password"]) && !empty($data["password"])) {
						$id_number = urldecode($data["id_number"]);
						$password = urldecode($data["password"]);
						
						if (!($fun -> idNumberCheck($id_number)) || !($fun -> passwordCheck($password)))
							$response = $fun -> getMsgInvalidParam();
						else
							$response = $fun -> login($id_number, $password, "patient");
					} else
						$response = $fun -> getMsgInvalidParam();
				}
			} else
				$response = $fun -> getMsgInvalidParam();
		} else
			$response = $fun -> getMsgInvalidParam();
		
		echo $response;
	}
} else
	echo "GET";
?>