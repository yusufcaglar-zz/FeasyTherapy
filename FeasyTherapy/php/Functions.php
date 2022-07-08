<?php
require_once 'DBOperations.php';

class Functions {
	private $db;

	public function __construct() {
		  $this -> db = new DBOperations();
	}

	//INDEX
	public function login($id_number, $password, $type) {
		$db = $this -> db;

		if ($db -> checkIdNumberExist($id_number, $type)) {
			if ($db -> isAccountAvailableForLogin($id_number, $type)) {
				if ($db -> login($id_number, $password, $type)) {
					$response["result"] = "success";
					$response["message"] = "Login Sucessful";
					return json_encode($response);
				} else {
					$response["result"] = "failure";
					$response["message"] = "Invalid Login Credentials";
					return json_encode($response);
				}
			} else {
				$response["result"] = "failure";
				$response["message"] = "Invalid Login Credentials";
				return json_encode($response);
			}
		} else {
			$response["result"] = "failure";
			$response["message"] = "Invalid Login Credentials";
			return json_encode($response);
		}
	}
	
	public function getPatients() {
		$db = $this -> db;
		
		if($db -> isAccountAvailable($_SESSION["id"], $_SESSION["type"])) {
			if($patients = $db -> getPatients($_SESSION["id"])) {
				$response["result"] = "success";
				$response["patients"] = $patients;
				return json_encode($response);
			} else {
				$response["result"] = "failure";
				return json_encode($response);
			}
		} else {
			session_unset();
			session_destroy();
				
			$response["result"] = "failure";
			$response["message"] = "User not exists";
			return json_encode($response);
		}
	}
	
	public function logOffUser() {
		$db = $this -> db;
		
		if($db -> isAccountAvailable($_SESSION["id"], $_SESSION["type"])) {
			if($db -> logOffUser($_SESSION["id"], $_SESSION["type"])) {
				$response["result"] = "success";
				return json_encode($response);
			} else {
				$response["result"] = "failure";
				return json_encode($response);
			}
		} else {
			session_unset();
			session_destroy();
				
			$response["result"] = "failure";
			$response["message"] = "User not exists";
			return json_encode($response);
		}
	}
	
	//EXERCISE
	public function getExercise($patient_id) {
		$db = $this -> db;
		
		//Check user logged in
		if(isset($_SESSION["id_number"])) {
			if($db -> isAccountAvailable($_SESSION["id"], $_SESSION["type"])) {
				$response["id"] = $_SESSION["id"];
				$response["id_number"] = $_SESSION["id_number"];
				$response["type"]= $_SESSION["type"];
				
				//Check physiotherapist availability
				$check = true;
				
				if($_SESSION["type"] !== "physiotherapist") {
					$data = $db -> getExerciseToken($_SESSION["id"]);
					
					if (is_object($data)) {
						$token = $data -> token;
						$physiotherapist_phpsessid = $data -> phpsessid;
						$physiotherapist_id = $data -> physiotherapist_id;
					} else {
						$response["result"] = "failure";
						$response["message"] = "User is offline";
						$check = false;
					}
				}
				
				//Token
				if($check) {
					if($_SESSION["type"] === "physiotherapist") {
						if($db -> canCreateSession($_SESSION["id"])) {
							$response["patient"] = $db -> getPatientsData($patient_id);
							$response["result"] = "success";
							$response["message"] = "Session will be created";
						} else {
							$response["result"] = "failure";
							$response["message"] = "A session have been created already";
						}
					} else {
						if($db -> canJoinSession($_SESSION["id"])) {
							$response["physiotherapist"] = $db -> getPhysiotherapist($patient_id);
							$response["result"] = "success";
							$response["message"] = "Session will be created";
							$response["token"] = $token;
							$response["physiotherapist_phpsessid"] = $physiotherapist_phpsessid;
							$response["physiotherapist_id"] = $physiotherapist_id;
						} else {
							$response["result"] = "failure";
							$response["message"] = "A session have been created already";
						}
					}
				}
			} else {
				session_unset();
				session_destroy();
				
				$response["result"] = "failure";
				$response["message"] = "User not exists";
			}
		} else {
			session_unset();
			session_destroy();
				
			$response["result"] = "failure";
			$response["message"] = "User not exists";
		}
		
		return json_encode($response);
	}
		
	public function createToken($phpsessid, $token, $patient_id) {
		$db = $this -> db;
		
		if($db -> isAccountAvailable($_SESSION["id"], $_SESSION["type"])) {
			if($db -> canCreateSession($_SESSION["id"])) {
				if($db -> patientBelongsPhysiotherapist($_SESSION["id"], $patient_id)) {
					$token = $db -> createToken($_SESSION["id"], $phpsessid, $token, $patient_id);
					
					if(!$token) {
						$response["result"] = "failure";
						$response["message"] = "Token couldn't created";
						return json_encode($response);
						
					} else {
						$response["result"] = "success";
						return json_encode($response);
					}
				} else {
					$response["result"] = "failure";
					$response["message"] = "Patient does not belong to the physiotherapist";
					return json_encode($response);
				}
			} else {
				$response["result"] = "failure";
				$response["message"] = "User is offline";
				return json_encode($response);
			}
		} else {
			session_unset();
			session_destroy();
				
			$response["result"] = "failure";
			$response["message"] = "User not exists";
			return json_encode($response);
		}
	}
	
	public function keepAlive() {
		$db = $this -> db;
		
		if($db -> isAccountAvailable($_SESSION["id"], $_SESSION["type"])) {
			if($db -> keepAlive($_SESSION["id"], $_SESSION["type"])) {
				$response["result"] = "success";
				return json_encode($response);
			} else {
				session_unset();
				session_destroy();

				$response["result"] = "failure";
				return json_encode($response);
			}
		} else {
			session_unset();
			session_destroy();
				
			$response["result"] = "failure";
			$response["message"] = "User not exists";
			return json_encode($response);
		}
	}
	
	public function createSession($physiotherapist_id, $patient_id) {
		$db = $this -> db;
		
		if($db -> isAccountAvailable($physiotherapist_id, "physiotherapist") && $db -> isAccountAvailable($patient_id, "patient")) {
			if($db -> canJoinSession($patient_id)) {
				if($db -> patientBelongsPhysiotherapist($physiotherapist_id, $patient_id)) {
					if($db -> createSession($physiotherapist_id, $patient_id)) {
						//Create TXT file with the name of the token
						if ($token = $db -> getToken($physiotherapist_id)) {
							$user_file = fopen("/var/www/html/php/sessions/".$token.".txt", "w") or die("Unable to open file!");
							$content = date("Y-m-d H:i:s")."~Physiotherapist~Ready";
							fwrite($user_file, $content);
							fclose($user_file);
							
							$response["result"] = "success";
							return json_encode($response);
						} else {
							$response["result"] = "failure";
							$response["message"] = "Server failure";
							return json_encode($response);
						}
					} else {
						$response["result"] = "failure";
						$response["message"] = "Session couldn't created";
						return json_encode($response);
					}
				} else {
					$response["result"] = "failure";
					$response["message"] = "Patient does not belong to the physiotherapist";
					return json_encode($response);
				}
			} else {
				$response["result"] = "failure";
				$response["message"] = "User is offline";
				return json_encode($response);
			}
		} else {
			session_unset();
			session_destroy();
				
			$response["result"] = "failure";
			$response["message"] = "User not exists";
			return json_encode($response);
		}
	}
	
	public function getToken($id_number, $password) {
		$db = $this -> db;

		if ($db -> checkIdNumberExist($id_number, "patient")) {
			if ($db -> isAccountAvailableForLogin($id_number, "patient"))
				return $db -> getTokenNd($id_number, $password);
			else {
				$response["result"] = "failure";
				$response["message"] = "Invalid Login Credentials";
				return json_encode($response);
			}
		} else {
			$response["result"] = "failure";
			$response["message"] = "Invalid Login Credentials";
			return json_encode($response);
		}
	}
	
	//AUX METHODS
	public function getPhysiotherapist($id) {
		$response["physiotherapist"] = $this -> db -> getPhysiotherapist($id);
		$response["result"] = "success";
		return json_encode($response);
	}
	
	public function isAccountAvailableForLogin($id_number, $type) {
		return $this -> db -> isAccountAvailableForLogin($id_number, $type);
	}
	
	public function idNumberCheck($id) {
		if(is_null($id) || empty($id))
			return false;
		
		///// Id check
		$allowed_characters = ["0", "1", "2", "3", "4", "5", "6", "7", "8", "9"];
		$id_characters = str_split($id);

		$id_check = true;
		for ($i = 0; $i < count($id_characters); $i++) {
			$check = false;

			for ($j = 0; $j < count($allowed_characters); $j++) {
				if ($id_characters[$i] === $allowed_characters[$j])
					$check = true;
			}

			if (!$check) {
				$id_check = false;
				break;
			}
		}
		
		if (strlen($id) != 11)
			return false;
		else
			return $id_check;
	}
	
	public function passwordCheck($password) {
		if (is_null($password) || empty($password))
			return false;
		
		////// Password check
		$upper_characters = ["A", "B", "C", "Ç", "D", "E", "F", "G", "Ğ", "H", "I", "İ", "J", "K", "L", "M",
			"N", "O", "Ö", "P", "Q", "R", "S", "Ş", "T", "U", "Ü", "V", "W", "X", "Y", "Z"];
		$number_characters = ["0", "1", "2", "3", "4", "5", "6", "7", "8", "9"];
		$password_characters = str_split($password);

		$upperCheck = false;
		for ($i = 0; $i < count($password_characters); $i++) {
			for ($j = 0; $j < count($upper_characters); $j++) {
				if ($password_characters[$i] === $upper_characters[$j])
					$upperCheck = true;
			}
		}

		$numberCheck = false;
		for ($i = 0; $i < count($password_characters); $i++) {
			for ($j = 0; $j < count($number_characters); $j++) {
				if ($password_characters[$i] === $number_characters[$j])
					$numberCheck = true;
			}
		}
		
		if (strlen($password) < 8 || strlen($password) > 16)
			return false;
		else if (!$upperCheck || !$numberCheck)
			return false;
		else
			return true;
	}
	
	public function getMsgInvalidParam() {
		$response["result"] = "failure";
		$response["message"] = "Invalid Parameters";
		return json_encode($response);
	}
}
?>