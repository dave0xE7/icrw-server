<?php
require_once("EasyBitcoin-PHP/easybitcoin.php");

$intercrone = new Bitcoin("InterCronerpc", "1337133713371337", "localhost", "8443");

define ("dataDir", "data/users/");


function checkAccount ($account) {
        return file_exists(dataDir. $account);
}
function checkKey ($account, $key) {
	$userdata = json_decode(file_get_contents(dataDir. $account));
	return ($key == $userdata->key);
}
function checkAccess ($account, $key) {
        if (checkAccount($account)) {
                return checkKey($account, $key);
        } return false;
}

function testAccount ($account, $key) {
        $found = checkAccount($account);
        $correct = false;
        if ($found) {
                $correct = checkKey($account, $key);
        }
        Respond(array("account"=>$found, "key"=>$correct));
}

function createAccount () {
  globel $intercrone
  // Create a new wallet
  $account = hash('sha256', time());
  $newkey = hash('sha256', $account);
  if (!file_exists(dataDir. $account)) {
    $address = $intercrone->getnewaddress($account);
    $balance = 0.0;
    $userdata = json_encode(array("balance"=>$balance, "address"=>$address, "key"=>$newkey));
    file_put_contents(dataDir. $account, $userdata);
    Respond(array("account"=>$account, "key"=>$newkey));
  } else {
  Error("-15","account exists");
	}
}

function secureAccount ($account, $key) {
    if (file_exists(dataDir. $account)) {
      // account found in database
  	$userdata = json_decode(file_get_contents(dataDir. $account));
  	if ($key == $userdata->key) {
        // key was correct
        $newkey = hash('sha256', time());
        $userdata->key = $newkey;
        file_put_contents(dataDir. $account, json_encode($userdata));
        Respond($newkey);
      } else {
	Error ("-10", "key incorrect");}
    } else {
            Error ("-15", "not found");
    }
}
function getBalance ($account, $key) {
        global $intercrone;
        if (file_exists(dataDir. $account)) {
                // account found in database
                $userdata = json_decode(file_get_contents(dataDir. $account));
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

function listTransactions ($account, $key) {
        if (checkAccess($account, $key)) {
                globel $intercrone
                //$userdata = json_decode(file_get_contents(dataDir. $account));
                Respond($intercrone->listtransactions($account));
        }
}


function Respond ($response) {
  global $id;
  echo (json_encode(array("id"=>$id, "jsonrpc"=>"2.0", "result"=>$response)));
}
function Error ($code, $message) {
  global $id;
  echo (json_encode(array("id"=>$id, "jsonrpc"=>"2.0", "error"=>array("code"=>$code, "message"=>$message))));
}
function RespondBool ($value) {
        if ($value) { Respond("true"); }
        else { Respond("false"); }
}

$inputJson = file_get_contents('php://input');
$input = json_decode($inputJson);

$id = $input->id;
$method = $input->method;
$params = $input->params;

if ($method == "createAccount") {
        createAccount();
} else if ($method=="checkAccount") {
        RespondBool(checkAccount($params[0]));
} else if ($method=="testAccount") {
        testAccount($params[0], $params[1]);
} else if ($method=="secureAccount") {
        secureAccount($params[0], $params[1]);
} else if ($method=="getBalance") {
        getBalance($params[0], $params[1]);
} else if ($method=="listTransactions") {
        listTransactions($params[0], $params[1]);
} else if ($method=="ping") {
	Respond ($id, "pong");
} else if ($method=="system.describe") {
	$procs = array(
		array("name"=>"ping", "params"=>array()),
		array("name"=>"createAccount", "params"=>""),
		array("name"=>"checkAccount", "params"=>array("<account>")),
		array("name"=>"testAccount", "params"=>array("<account>", "<key>")),
		array("name"=>"secureAccount", "params"=>array("<account>", "<key>")),
		array("name"=>"getBalance", "params"=>array("<account>", "<key>")),
		array("name"=>"listTransactions", "params"=>array("<account>", "<key>")),
		array("name"=>"makeTransaction", "params"=>array("<account>", "<key>", "<receiver>", "<amount>"))
	);
	echo (json_encode(array("id"=>$input->id, "jsonrpc"=>"2.0", "procs"=>$procs)));
}


?>
