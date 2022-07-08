<?php
if (php_sapi_name() !== 'cli') {
    die('Can only be executed via CLI');
}

$host = 'localhost';
$user = 'root';
$pass = '???';
$db = 'feasytherapy';
$conn;

$conn = new PDO("mysql:host=".$host.";dbname=".$db, $user, $pass);
$conn -> exec("SET GLOBAL time_zone='+00:00';");

$sql = 'SELECT id, physiotherapist_updated, patient_updated, physiotherapist_id, patient_id, patient_joined FROM ongoing';
$query = $conn -> prepare($sql);
$query -> execute();

if ($query) {
	$result = $query -> fetchAll(PDO::FETCH_ASSOC);
	
	if (count($result) != 0) {
		for($i = 0; $i < count($result); $i++) {
			if((time() - strtotime($result[$i]["physiotherapist_updated"]) >= 45) || ((time() - strtotime($result[$i]["patient_updated"]) >= 45) && $result[$i]["patient_joined"]))
				logOffUser($conn, $result[$i]["id"], $result[$i]["physiotherapist_id"], $result[$i]["patient_id"]);
		}
	}
}

function logOffUser($conn, $id, $physiotherapist_id, $patient_id) {			
	$sql = 'DELETE FROM ongoing WHERE id = :id';
	$query = $conn -> prepare($sql);
	$query -> execute(array(':id' => $id));
	
	$sql = 'UPDATE session SET ending_date = :ending_date WHERE physiotherapist_id = :physiotherapist_id AND patient_id = :patient_id ORDER BY id DESC LIMIT 1;';
	$query = $conn -> prepare($sql);
	$query -> execute(array(':ending_date' => strval(date('Y-m-d H:i:s', time())), ':physiotherapist_id' => $physiotherapist_id, ':patient_id' => $patient_id));
}

/*
if (array_key_exists('HTTP_ORIGIN', $_SERVER))
    $origin = $_SERVER['HTTP_ORIGIN'];
else if (array_key_exists('HTTP_REFERER', $_SERVER))
    $origin = $_SERVER['HTTP_REFERER'];
else
    $origin = $_SERVER['REMOTE_ADDR'];
*/
?>
