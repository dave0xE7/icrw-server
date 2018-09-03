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

function checkAccount ($account) {
  if (file_exists('data/users/'. $account)) {
    return true;
  }
  return false;
}
function createAccount () {
  global $intercrone;
  // Create a new wallet
  $account = hash('sha256', time());
  $newkey = hash('sha256', $account);
  if (!file_exists('data/users/'. $account)) {
    $address = $intercrone->getnewaddress($account);
    $balance = 0.0;
    $userdata = json_encode(array("balance"=>$balance, "address"=>$address, "key"=>$newkey));
    file_put_contents('data/users/'. $account, $userdata);
    Respond(array("account"=>$account, "key"=>$newkey));
  }
  Error("-15","account exists");
}

function secureAccount ($account, $key) {
    if (file_exists('data/users/'. $account)) {
      // account found in database
  		$userdata = json_decode(file_get_contents('data/users/'. $account));
  		if ($key == $userdata->key) {
        // key was correct
        $newkey = hash('sha256', time());
        $userdata->key = $newkey;
        file_put_contents('data/users/'. $account, json_encode($userdata));
        return $newkey;
      }
    }
}
/**
        $balance = $intercrone->getbalance($userid);
       return (json_encode(array("userid"=>$userid, "token"=>$token, "balance"=>$balance, "address"=>$userdata->address)));
	}
    }
  }
  //echo (json_encode(array("userid"=>$userid, "token"=>$token)));
  return  (json_encode(array("userid"=>$userid, "token"=>$token, "balance"=>$balance, "address"=>$address)));
}
**/

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

//function Respond ($id, $response) {
function Respond ($response) {
  global $id;
  echo (json_encode(array("id"=>$id, "jsonrpc"=>"2.0", "result"=>$response)));
}
//function Error ($id, $code, $message) {
function Error ($code, $message) {
  global $id;
  echo (json_encode(array("id"=>$id, "jsonrpc"=>"2.0", "error"=>array("code"=>$code, "message"=>$message))));
}

//if ($_SERVER["REQUEST_METHOD"] == "POST") {

	$inputJson = file_get_contents('php://input');
	$input = json_decode($inputJson);

	//if (isset ($_POST['method'])) {

	$id = $input->id;
	$method = $input->method;
	$params = $input->params;
	//$method = user_input($_POST['method']);
	//$q = use_input($_POST['q']);

	if ($method == "createAccount") {
		//$data=getAccount();
		//Respond($id, $data);
    createAccount();
  } else if ($method=="checkAccount") {
    checkAccount($params[0]);
	} else if ($method=="secureAccount") {
		$accountId=$params[0];
		$accountKey=$params[1];
    secureAccount($accountId, $accountKey);
    //Respond($id, array("key"=>$accountKey));
	} else if ($method=="ping") {
		Respond ($id, "pong");
	} else if ($method=="system.describe") {
		$procs = array(
				array("name"=>"ping", "params"=>array()),
				array("name"=>"createAccount", "params"=>""),
				array("name"=>"checkAccount", "params"=>""),
				array("name"=>"secureAccount", "params"=>array("<account>", "<key>"))
			);
		echo (json_encode(array("id"=>$input->id, "jsonrpc"=>"2.0", "procs"=>$procs)));
	}
//	}
//}


?>
