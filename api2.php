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
    Respond("true");
  } else {
	  Respond("false");
	}
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
  } else {
  Error("-15","account exists");
	}
}
function testAccount ($account, $key) {
        if (file_exists('data/users/'. $account)) {
                // account found in database
                $userdata = json_decode(file_get_contents('data/users/'. $account));
                if ($key == $userdata->key) {
                        Respond("true"); // correct
                } else {
                        Respond("false"); // incorrect
                }
        } else {
                Error ("-15", "not found");
        }
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
        Respond($newkey);
      } else {
	Error ("-10", "key incorrect");}
    } else {
            Error ("-15", "not found");
    }
}
function getBalance ($account, $key) {
        if (file_exists('data/users/'. $account)) {
                // account found in database
                $userdata = json_decode(file_get_contents('data/users/'. $account));
                if ($key == $userdata->key) {
                        $balance = $intercrone->getbalance($userdata->address);
                        Respond (json_encode(array("balance"=>$balance, "address"=>$userdata->address)));
                } else {
                        Error ("-10", "key incorrect");
                }
        } else {
                Error ("-15", "not found");
        }
}
/**

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

$id = $input->id;
$method = $input->method;
$params = $input->params;

if ($method == "createAccount") {
        createAccount();
} else if ($method=="checkAccount") {
        checkAccount($params[0]);
} else if ($method=="testAccount") {
        testAccount($params[0], $params[1]);
} else if ($method=="secureAccount") {
        secureAccount($params[0], $params[1]);
} else if ($method=="getBalance") {
        getBalance($params[0], $params[1]);
} else if ($method=="ping") {
	Respond ($id, "pong");
} else if ($method=="system.describe") {
	$procs = array(
		array("name"=>"ping", "params"=>array()),
		array("name"=>"createAccount", "params"=>""),
		array("name"=>"checkAccount", "params"=>array("<account>")),
		array("name"=>"testAccount", "params"=>array("<account>", "<key>")),
		array("name"=>"secureAccount", "params"=>array("<account>", "<key>")),
		array("name"=>"getBalance", "params"=>array("<account>", "<key>"))
	);
	echo (json_encode(array("id"=>$input->id, "jsonrpc"=>"2.0", "procs"=>$procs)));
}


?>
