<?php
require_once("EasyBitcoin-PHP/easybitcoin.php");

$intercrone = new Bitcoin("InterCronerpc", "1337133713371337", "localhost", "8443");

function use_input($data) {
  $data = trim($data);
  $data = stripslashes($data);
  $data = htmlspecialchars($data);
  return $data;
}

function check_userid() {
	global $userid;
	if (isset ($_SESSION['userid'])) {
		$userid = $_SESSION['userid'];
		return $userid;
	} return "";
}

function getAccount () {
  global $intercrone;
  $userid = use_input($_POST['userid']);
	$token = use_input($_POST['token']);
  if (!empty($userid) && !empty($token)) {
    if (file_exists('data/users/'. $userid)) {
      // account found in database
  		$userdata = json_decode(file_get_contents('data/users/'. $userid));
  		if ($token == $userdata->token) {
        // key was correct
        $token = hash('sha256', time());
        $userdata->token = $token;
        file_put_contents('data/users/'. $userid, json_encode($userdata));
        $balance = $intercrone->getbalance($userid);
       return (json_encode(array("userid"=>$userid, "token"=>$token, "balance"=>$balance, "address"=>$userdata->address)));
	}
    }
  }
    // Create a new wallet
    $userid = hash('sha256', time());
    $token = hash('sha256', $userid);
    if (!file_exists('data/users/'. $userid)) {
      $address = $intercrone->getnewaddress($userid);
      $balance = 0.0;
      $userdata = json_encode(array("balance"=>$balance, "address"=>$address, "token"=>$userid));
      file_put_contents('data/users/'. $userid, $userdata);
    }

  //echo (json_encode(array("userid"=>$userid, "token"=>$token)));
  return  (json_encode(array("userid"=>$userid, "token"=>$token, "balance"=>$balance, "address"=>$address)));
}

function check_login () {
	$userid = use_input($_POST['userid']);
	$token = use_input($_POST['token']);
	return check_user_token($userid, $token);
}

function check_user_token ($userid, $token) {
	if (file_exists('data/users/'. $userid)) {
		$userdata = json_decode(file_get_contents('data/users/'. $userid));
		if ($token == $userdata->token) {
			return true;
		}
	}
	return false;
}

function Respond ($id, $response) {
	echo (json_encode(array("id"=>$id, "jsonrpc"=>"2.0", "result"=>$response)));
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {

	$inputJson = file_get_contents('php://input');
	$input = json_decode($inputJson);

	//if (isset ($_POST['method'])) {

	$id = $input->id;
	$method = $input->method;
	$params = $input->params;
	//$method = user_input($_POST['method']);
	//$q = use_input($_POST['q']);

	if ($method == "createAccount") {
		$data=getAccount();
		Respond($id, $data);
	} else if ($method=="secureAccount") {
		$accountId=$params[0];
		$accountKey=$params[1];
		Respond($id, array("key"=>$accountKey));
	} else if ($method=="ping") {
		Respond ($id, "pong");
	} else if ($method=="system.describe") {
		$procs = array(
				array("name"=>"ping", "params"=>array()),
				array("name"=>"createAccount", "params"=>""),
				array("name"=>"secureAccount", "params"=>array("<account>", "<key>"))
			);
		echo (json_encode(array("id"=>$input->id, "jsonrpc"=>"2.0", "procs"=>$procs)));
	}
//	}
}


?>

