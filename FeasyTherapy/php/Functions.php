<?php
require_once 'DBOperations.php';

class Functions {
	private $db;

	public function __construct() {
		  $this -> db = new DBOperations();
	}

	//INDEX
	public function login($id_number, $password) {
		$db = $this -> db;

		if ($db -> checkIdNumberExist($id_number)) {
			if ($db -> isAccountAvailableForLogin($id_number)) {
				if ($db -> login($id_number, $password)) {
					$response["result"] = "success";
					$response["message"] = "Login Sucessful";
					return json_encode($response);
				} else {
					$response["result"] = "failure";
					$response["message"] = "Invaild Login Credentials";
					return json_encode($response);
				}
			} else {
				$response["result"] = "failure";
				$response["message"] = "Invaild Login Credentials";
				return json_encode($response);
			}
		} else {
			$response["result"] = "failure";
			$response["message"] = "Invaild Login Credentials";
			return json_encode($response);
		}
	}
	
	public function getPatients() {
		$db = $this -> db;
		
		if($db -> isAccountAvailable($_SESSION["id"], session_id())) {
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
		
		if($db -> isAccountAvailable($_SESSION["id"], session_id())) {
			//if($db -> isOnlineUser($_SESSION["id"], false)) {
				if($db -> logOffUser($_SESSION["id"])) {
					$response["result"] = "success";
					return json_encode($response);
				} else {
					$response["result"] = "failure";
					return json_encode($response);
				}
			//} else {
			//	$response["result"] = "failure";
			//	return json_encode($response);
			//}
		} else {
			session_unset();
			session_destroy();
				
			$response["result"] = "failure";
			$response["message"] = "User not exists";
			return json_encode($response);
		}
	}
	
	//AUX METHODS
	public function isAccountAvailableForLogin($id_number) {
		return $this -> db -> isAccountAvailableForLogin($id_number);
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