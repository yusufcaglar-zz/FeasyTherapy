<?php
class DBOperations {
	private $host = 'localhost';
	private $user = 'root';
	private $pass = '???';
	private $db = 'feasytherapy';
	private $conn;

	public function __construct() {
		$this -> conn = new PDO("mysql:host=".$this -> host.";dbname=".$this -> db, $this -> user, $this -> pass);
		$this -> conn -> exec("set names utf8mb4");
		$this -> conn -> exec("SET GLOBAL time_zone='+03:00';");
	}
	
	//INDEX
	public function login($id_number, $password, $type) {
		if ($type == "physiotherapist") {
			$sql = 'SELECT id, encrypted_password, salt FROM physiotherapist WHERE id_number = :id_number AND active = :active';
			$query = $this -> conn -> prepare($sql);
			$query -> execute(array(':id_number' => $id_number, ':active' => 1));
		} else if ($type == "admin") {
			$sql = 'SELECT id, encrypted_password, salt FROM admin WHERE id_number = :id_number';
			$query = $this -> conn -> prepare($sql);
			$query -> execute(array(':id_number' => $id_number));
		} else {
			$sql = 'SELECT id, encrypted_password, salt FROM patient WHERE id_number = :id_number AND active = :active';
			$query = $this -> conn -> prepare($sql);
			$query -> execute(array(':id_number' => $id_number, ':active' => 1));
		}

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
			$_SESSION["type"] = $type;
			
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
					if($this -> isAccountAvailable($result[$i]["id"], "patient")) {
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
		$patients_data["age"] = 2022 - intval(explode("-", $data -> birthday)[0]);
		$patients_data["register_date"] = $data -> register_date;
		$patients_data["complaint"] = $data -> complaint;
		
		return $patients_data;
	}
	
	public function logOffUser($id, $type) {
		if ($type === "physiotherapist") {
			//Update Session
			$sql = 'SELECT patient_id FROM ongoing WHERE physiotherapist_id = :id';
			$query = $this -> conn -> prepare($sql);
			$query -> execute(array(':id' => $id));
			
			$data = $query -> fetchObject();
			
			if (is_object($data)) {
				$sql = 'UPDATE session SET ending_date = :ending_date WHERE physiotherapist_id = :physiotherapist_id AND patient_id = :patient_id ORDER BY id DESC LIMIT 1;';
				$query = $this -> conn -> prepare($sql);
				$query -> execute(array(':ending_date' => strval(date('Y-m-d H:i:s', time())), ':physiotherapist_id' => $id, ':patient_id' => $data -> patient_id));
			}
			
			//Log Off
			$sql = 'DELETE FROM ongoing WHERE physiotherapist_id = :physiotherapist_id';
			$query = $this -> conn -> prepare($sql);
			$query -> execute(array(':physiotherapist_id' => $id));
		} else if ($type === "patient") {
			//Update Session
			$sql = 'SELECT physiotherapist_id FROM ongoing WHERE patient_id = :id';
			$query = $this -> conn -> prepare($sql);
			$query -> execute(array(':id' => $id));
			
			$data = $query -> fetchObject();
			
			if (is_object($data)) {
				$sql = 'UPDATE session SET ending_date = :ending_date WHERE physiotherapist_id = :physiotherapist_id AND patient_id = :patient_id ORDER BY id DESC LIMIT 1;';
				$query = $this -> conn -> prepare($sql);
				$query -> execute(array(':ending_date' => strval(date('Y-m-d H:i:s', time())), ':physiotherapist_id' => $data -> physiotherapist_id, ':patient_id' => $id));
			}
			
			//Log Off
			$sql = 'DELETE FROM ongoing WHERE patient_id = :patient_id';
			$query = $this -> conn -> prepare($sql);
			$query -> execute(array(':patient_id' => $id));
		}
	}
	
	//EXERCISE
	public function canCreateSession($physiotherapist_id) {
		$sql = 'SELECT COUNT(*) FROM ongoing WHERE physiotherapist_id = :physiotherapist_id';
		$query = $this -> conn -> prepare($sql);
		$query -> execute(array(':physiotherapist_id' => $physiotherapist_id));

		$row_count = $query -> fetchColumn();

		if ($row_count == 0)
			return true;
		else
			return false;
	}
	
	public function canJoinSession($patient_id) {
		$sql = 'SELECT COUNT(*) FROM ongoing WHERE patient_id = :patient_id AND patient_joined = :patient_joined';
		$query = $this -> conn -> prepare($sql);
		$query -> execute(array(':patient_id' => $patient_id, ':patient_joined' => 0));

		$row_count = $query -> fetchColumn();

		if ($row_count !== 0)
			return true;
		else
			return false;
	}
	
	public function getExerciseToken($id) {
		$sql = 'SELECT token, phpsessid, physiotherapist_id FROM ongoing WHERE patient_id = :id';
		$query = $this -> conn -> prepare($sql);
		$query -> execute(array(':id' => $id));
		
		$data = $query -> fetchObject();
		return $data;
	}
	
	public function createToken($physiotherapist_id, $phpsessid, $token, $patient_id) {		
		//Device Info
		$device_info = $_SERVER['HTTP_USER_AGENT'];
		
		//Ip
		$client  = @$_SERVER['HTTP_CLIENT_IP'];
		$forward = @$_SERVER['HTTP_X_FORWARDED_FOR'];
		$remote  = $_SERVER['REMOTE_ADDR'];

		if(filter_var($client, FILTER_VALIDATE_IP))
			$ip = $client;
		else if(filter_var($forward, FILTER_VALIDATE_IP))
			$ip = $forward;
		else
			$ip = $remote;
		
		//Country
		$ipdat = @json_decode(file_get_contents("http://www.geoplugin.net/json.gp?ip=".$ip));
		$country = $ipdat -> geoplugin_countryName;

		//Create
		$sql = 'INSERT INTO ongoing SET physiotherapist_id = :physiotherapist_id, country = :country, device_info = :device_info, ip = :ip, phpsessid = :phpsessid, token = :token, patient_id = :patient_id';
		$query = $this -> conn -> prepare($sql);
		
		if($query -> execute(array(':physiotherapist_id' => $physiotherapist_id, ':country' => $country, ':device_info' => $device_info, ':ip' => $ip, ':phpsessid' => $phpsessid, ':token' => $token, ':patient_id' => $patient_id)))
			return true;
		else
			return false;
	}
	
	public function keepAlive($id, $type) {
		if ($type === "physiotherapist") {
			$sql = 'UPDATE ongoing SET physiotherapist_updated = :updated WHERE physiotherapist_id = :id';
			$query = $this -> conn -> prepare($sql);

			if ($query -> execute(array(':updated' => strval(date('Y-m-d H:i:s', time())), ':id' => $id)))
				return true;
			else
				return false;
		} else if ($type === "patient") {
			$sql = 'UPDATE ongoing SET patient_updated = :updated WHERE patient_id = :id';
			$query = $this -> conn -> prepare($sql);

			if ($query -> execute(array(':updated' => strval(date('Y-m-d H:i:s', time())), ':id' => $id)))
				return true;
			else
				return false;
		}
	}
	
	public function patientBelongsPhysiotherapist($physiotherapist_id, $patient_id) {
		$sql = 'SELECT COUNT(*) FROM patient WHERE id = :id AND physiotherapist_id = :physiotherapist_id AND active = :active';
		$query = $this -> conn -> prepare($sql);
		$query -> execute(array(':id' => $patient_id, 'physiotherapist_id' => $physiotherapist_id, ':active' => 1));

		$row_count = $query -> fetchColumn();

		if ($row_count == 0)
			return false;
		else
			return true;
	}
		
	public function getPhysiotherapist($id) {
		$sql = 'SELECT physiotherapist_id FROM patient WHERE id = :id';
		$query = $this -> conn -> prepare($sql);
		$query -> execute(array(':id' => $id));
		$data = $query -> fetchObject();
		
		$id = $data -> physiotherapist_id;
		
		$sql = 'SELECT name, surname, photo FROM physiotherapist WHERE id = :id';
		$query = $this -> conn -> prepare($sql);
		$query -> execute(array(':id' => $id));
		$data = $query -> fetchObject();
		
		$physiotherapist["name"] = $data -> name;
		$physiotherapist["surname"] = $data -> surname;
		$physiotherapist["photo"] = $data -> photo;
		
		return $physiotherapist;
	}
	
	public function createSession($physiotherapist_id, $patient_id) {
		$sql = 'UPDATE ongoing SET patient_joined = :patient_joined WHERE physiotherapist_id = :physiotherapist_id AND patient_id = :patient_id';
		$query = $this -> conn -> prepare($sql);

		if ($query -> execute(array(':patient_joined' => 1, ':physiotherapist_id' => $physiotherapist_id, ':patient_id' => $patient_id)))
			return true;
		else
			return false;
	}
	
	public function getToken($physiotherapist_id) {
		$sql = 'SELECT token FROM ongoing WHERE physiotherapist_id = :physiotherapist_id';
		$query = $this -> conn -> prepare($sql);
		$query -> execute(array(':physiotherapist_id' => $physiotherapist_id));
		$data = $query -> fetchObject();
		
		return $data -> token;
	}
	
	public function getTokenNd($id_number, $password) {
		$sql = 'SELECT physiotherapist_id, encrypted_password, salt FROM patient WHERE id_number = :id_number AND active = :active';
		$query = $this -> conn -> prepare($sql);
		$query -> execute(array(':id_number' => $id_number, ':active' => 1));

		$data = $query -> fetchObject();
		$physiotherapist_id = $data -> physiotherapist_id;
		$db_encrypted_password = $data -> encrypted_password;
		$salt = $data -> salt;

		if ($this -> verifyHash($password.$salt, $db_encrypted_password)) {
			$response["result"] = "success";
			$response["token"] = $this -> getToken($physiotherapist_id);
			return json_encode($response);
		} else {
			$response["result"] = "failure";
			$response["message"] = "Invalid Login Credentials";
			return json_encode($response);
		}
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
	public function isAccountAvailableForLogin($id_number, $type) {
		if ($type == "physiotherapist")
			$sql = 'SELECT COUNT(*) FROM physiotherapist WHERE id_number = :id_number AND active = :active';
		else if ($type == "admin")
			$sql = 'SELECT COUNT(*) FROM admin WHERE id_number = :id_number';
		else
			$sql = 'SELECT COUNT(*) FROM patient WHERE id_number = :id_number AND active = :active';
		$query = $this -> conn -> prepare($sql);
		$query -> execute(array(':id_number' => $id_number, ':active' => 1));

		$row_count = $query -> fetchColumn();

		if ($row_count == 0)
			return false;
		else
			return true;
	}
	
	public function isAccountAvailable($id, $type) {
		if ($type == "physiotherapist")
			$sql = 'SELECT COUNT(*) FROM physiotherapist WHERE id = :id AND active = :active';
		else if ($type == "admin")
			$sql = 'SELECT COUNT(*) FROM admin WHERE id = :id';
		else
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
	
	public function checkIdNumberExist($id_number, $type) {
		if ($type == "physiotherapist") {
			$sql = 'SELECT COUNT(*) FROM physiotherapist WHERE id_number = :id_number AND active = :active';
			$query = $this -> conn -> prepare($sql);
			$query -> execute(array(':id_number' => $id_number, ':active' => 1));
		} else if ($type == "admin") {
			$sql = 'SELECT COUNT(*) FROM admin WHERE id_number = :id_number';
			$query = $this -> conn -> prepare($sql);
			$query -> execute(array(':id_number' => $id_number));
		} else {
			$sql = 'SELECT COUNT(*) FROM patient WHERE id_number = :id_number AND active = :active';
			$query = $this -> conn -> prepare($sql);
			$query -> execute(array(':id_number' => $id_number, ':active' => 1));
		}

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
