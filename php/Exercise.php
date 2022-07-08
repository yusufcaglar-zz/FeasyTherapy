<?php
header('Access-Control-Allow-Credentials: true');
header('Access-Control-Allow-Origin: https://feasytherapy.site:3000');

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
	//POST
	if ($_POST) {
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
				
				//getExercise
				if ($operation === 'getExercise') {
					if(isset($data["id"]) && !empty($data["id"]))
						$response = $fun -> getExercise($data["id"]);
					else
						$response = $fun -> getMsgInvalidParam();
					
				//createToken
				} else if ($operation === 'createToken') {
					if(isset($data["token"]) && !empty($data["token"] && isset($data["patient_id"]) && !empty($data["patient_id"]))) {
						$phpsessid = $data["phpsessid"];
						$token = $data["token"];
						$patient_id = $data["patient_id"];
						
						if ((strlen($token) !== 20) && ($_SESSION["type"] !== "physiotherapist"))
							$response = $fun -> getMsgInvalidParam();
						else if(isset($_SESSION["id_number"]))			
							$response = $fun -> createToken($phpsessid, $token, $patient_id);
						else {
							$response["result"] = "failure";
							$response["message"] = "User not logged in";
							$response = json_encode($response);
						}
					} else
						$response = $fun -> getMsgInvalidParam();
					
				//keepAlive	
				} else if ($operation == 'keepAlive') {
					if(isset($_SESSION["id_number"]))
						$response = $fun -> keepAlive();
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
		}
		
	//JSON
	} else if (file_get_contents("php://input")) {
		require_once 'Functions.php';
		$fun = new Functions();
		
		$data = json_decode(file_get_contents("php://input"));

		if(isset($data -> operation) && !empty($data -> operation)) {
			$operation = $data -> operation;
			$password = $data -> password;
			
			if (hash_equals("???", $password)) {
				//createSession
				if ($operation === 'createSession') {
					$physiotherapist_id = $data -> physiotherapist_id;
					$patient_id = $data -> patient_id;
					
					echo $fun -> createSession($physiotherapist_id, $patient_id);
					
				//getToken
				} else if ($operation === 'getToken') {
					if (isset($data -> id_number) && !empty($data -> id_number) && 
						isset($data -> passwd) && !empty($data -> passwd)) {
						$id_number = urldecode($data -> id_number);
						$password = urldecode($data -> passwd);
						
						if (!($fun -> idNumberCheck($id_number)) || !($fun -> passwordCheck($password)))
							echo $fun -> getMsgInvalidParam();
						else
							echo $fun -> getToken($id_number, $password);
					} else
						echo $fun -> getMsgInvalidParam();
				} else
					echo $fun -> getMsgInvalidParam();
			} else
				echo $fun -> getMsgInvalidParam();
		} else
			echo $fun -> getMsgInvalidParam();
	} else
		echo $fun -> getMsgInvalidParam();
} else
	echo "GET";
?>
