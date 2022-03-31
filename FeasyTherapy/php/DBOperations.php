<?php
class DBOperations {
	private $host = 'localhost';
	private $user = 'root';
	private $pass = '';
	private $db = 'feasytherapy';
	private $conn;

	public function __construct() {
		$this -> conn = new PDO("mysql:host=".$this -> host.";dbname=".$this -> db, $this -> user, $this -> pass);
		$this -> conn -> exec("set names utf8mb4");
		$this -> conn -> exec("SET GLOBAL time_zone='+03:00';");
	}
	
	//INDEX
	public function login($id_number, $password) {
		$sql = 'SELECT id, encrypted_password, salt FROM physiotherapist WHERE id_number = :id_number AND active = :active';
		$query = $this -> conn -> prepare($sql);
		$query -> execute(array(':id_number' => $id_number, ':active' => 1));

		$data = $query -> fetchObject();
		$id = $data -> id;
		$db_encrypted_password = $data -> encrypted_password;
		$salt = $data -> salt;

		if ($this -> verifyHash($password.$salt, $db_encrypted_password)) {			
			//Log off if logged on
			//if($this -> isOnlineUser($id, false))
			//	$this -> logOffUser($id);
		
			//Create online_users
			//$this -> createOnlineUser($id, session_id());
			
			//Return
			$_SESSION["id"] = $id;
			$_SESSION["id_number"] = $id_number;
			
			return true;
		} else
			return false;
	}

	public function getPatients($id) {	
		$sql = 'SELECT id FROM patient WHERE physiotherapist_id = :physiotherapist_id';
		$query = $this -> conn -> prepare($sql);
		$query -> execute(array(':physiotherapist_id' => $id));
		
		if ($query) {
			$result = $query -> fetchAll(PDO::FETCH_ASSOC);
			
			$counter = 0;
			if (count($result) != 0) {
				for($i = 0; $i<count($result); $i++) {
					if($this -> isPatientAvailable($result[$i]["id"])) {
						$array = $result[$i];
						
						$patients_data = $this -> getPatientsData($array["id"]);
						
						$patients[$counter]["id"] = $array["id"];
						$patients[$counter]["name"] = $patients_data["name"];
						$patients[$counter]["surname"] = $patients_data["surname"];
						$patients[$counter]["gender"] = $patients_data["gender"];
						$patients[$counter]["photo"] = $patients_data["photo"];
						$patients[$counter]["age"] = $patients_data["age"];
						$patients[$counter]["register_date"] = $patients_data["register_date"];
						$patients[$counter]["complaint"] = $patients_data["complaint"];
						
						$counter++;
					}
				}
				
				if($counter == 0)
					return null;
				else
					return $patients;
			} else 
				return null;
		} else 
			return null;
	}
	
	public function getPatientsData($id) {
		$sql = 'SELECT name, surname, gender, photo, birthday, register_date, complaint FROM patient WHERE id = :id';
		$query = $this -> conn -> prepare($sql);
		$query -> execute(array(':id' => $id));
		$data = $query -> fetchObject();
		
		$patients_data["name"] = $data -> name;
		$patients_data["surname"] = $data -> surname;
		$patients_data["gender"] = $data -> gender;
		$patients_data["photo"] = $data -> photo;
		$patients_data["age"] = floor(time() - strtotime($data -> birthday));
		$patients_data["register_date"] = $data -> register_date;
		$patients_data["complaint"] = $data -> complaint;
		
		return $patients_data;
	}
	
	public function logOffUser($id) {
		//Log Off
		//$sql = 'DELETE FROM online_users WHERE id = :id';
		//$query = $this -> conn -> prepare($sql);
		//$query -> execute(array(':id' => $id));
	}
	
	//Encryption
	public function getHash($password) {
		$salt = sha1(rand());
		$salt = substr($salt, 0, 10);
		$encrypted = password_hash($password.$salt, PASSWORD_DEFAULT);
		$hash = array("salt" => $salt, "encrypted" => $encrypted);

		return $hash;
	}

	public function verifyHash($password, $hash) {
		return password_verify($password, $hash);
	}
	
	//AUX
	public function isAccountAvailableForLogin($id_number) {		
		$sql = 'SELECT COUNT(*) FROM physiotherapist WHERE id_number = :id_number AND active = :active';
		$query = $this -> conn -> prepare($sql);
		$query -> execute(array(':id_number' => $id_number, ':active' => 1));

		$row_count = $query -> fetchColumn();

		if ($row_count == 0)
			return false;
		else
			return true;
	}
	
	public function isAccountAvailable($id, $phpsessid) {		
		$sql = 'SELECT COUNT(*) FROM physiotherapist WHERE id = :id AND active = :active';
		$query = $this -> conn -> prepare($sql);
		$query -> execute(array(':id' => $id, ':active' => 1));

		$row_count = $query -> fetchColumn();

		if ($row_count == 0)
			return false;
		else {
			/*$sql = 'SELECT COUNT(*) FROM online_users WHERE id = :id AND phpsessid = :phpsessid';
			$query = $this -> conn -> prepare($sql);
			$query -> execute(array(':id' => $id, ':phpsessid' => $phpsessid));

			$row_count = $query -> fetchColumn();

			if ($row_count == 0)
				return false;
			else
				return true;*/
			return true;
		}
	}
	
	public function isPatientAvailable($id) {		
		$sql = 'SELECT COUNT(*) FROM patient WHERE id = :id AND active = :active';
		$query = $this -> conn -> prepare($sql);
		$query -> execute(array(':id' => $id, ':active' => 1));

		$row_count = $query -> fetchColumn();

		if ($row_count == 0)
			return false;
		else {
			/*$sql = 'SELECT COUNT(*) FROM online_users WHERE id = :id AND phpsessid = :phpsessid';
			$query = $this -> conn -> prepare($sql);
			$query -> execute(array(':id' => $id, ':phpsessid' => $phpsessid));

			$row_count = $query -> fetchColumn();

			if ($row_count == 0)
				return false;
			else
				return true;*/
			return true;
		}
	}
	
	public function checkIdNumberExist($id_number) {
		$sql = 'SELECT COUNT(*) FROM physiotherapist WHERE id_number = :id_number AND active = :active';
		$query = $this -> conn -> prepare($sql);
		$query -> execute(array(':id_number' => $id_number, ':active' => 1));

		if($query) {
			$row_count = $query -> fetchColumn();

			if ($row_count == 0)
				return false;
			else
				return true;
		} else
			return false;
	}
}
?>