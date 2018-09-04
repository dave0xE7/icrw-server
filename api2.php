<?php
require_once("EasyBitcoin-PHP/easybitcoin.php");

$intercrone = new Bitcoin("InterCronerpc", "1337133713371337", "localhost", "8443");

define ("dataDir", "data/users/");

function MyLog ($message) {
        file_put_contents("calls.log", "[".date("Ymd-H-i-s")."]".$message."\n", FILE_APPEND);
}

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
  global $intercrone;
  // Create a new wallet
  $account = hash('sha256', time());
  $newkey = hash('sha256', $account);
  if (!file_exists(dataDir. $account)) {
    $address = $intercrone->getnewaddress($account);
    $userdata = json_encode(array("createTime"=>time(),"address"=>$address, "keyTime"=>time(), "key"=>$newkey));
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
        $userdata->keyTime = time();
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
                        $balance = $intercrone->getbalance($account);
                        Respond (array("balance"=>$balance, "address"=>$userdata->address));
                } else {
                        Error ("-10", "key incorrect");
                }
        } else {
                Error ("-15", "not found");
        }
}

function listTransactions ($account, $key) {
        if (checkAccess($account, $key)) {
                global $intercrone;
                //$userdata = json_decode(file_get_contents(dataDir. $account));
                Respond($intercrone->listtransactions($account));
        }
}

function makeTransaction ($account, $key, $address, $amount) {
        global $intercrone;
        if (checkAccess($account, $key)) {
                $balance = $intercrone->getbalance($account);
                if ($balance > 0) {
                        $transaction = $intercrone->sendfrom($account, $address, $amount);
                        Respond($transaction);
                }
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

MyLog($inputJson);
//MyLog($input;

//$id; $method, $params, $account, $key;

if (!empty($input)) {
        $id = $input->id;
        $method = $input->method;
        $params = $input->params;
        $account = $params[0];
        $key = $params[1];
}

if (isset ($_POST['id'])) {
        $id = $_POST['id'];
        $method = $_POST['method'];
        if (isset ($_POST['account'])) {
                $account = $_POST['account'];
        }
        if (isset ($_POST['key'])) {
                $key = $_POST['key'];
        }
        if (isset ($_POST['address'])) {
                $address = $_POST['address'];
        }
        if (isset ($_POST['amount'])) {
                $amount = $_POST['amount'];
        }
        MyLog("method=".$method);
}

if ($method == "createAccount") {
        createAccount();
} else if ($method=="checkAccount") {
        RespondBool(checkAccount($account));
} else if ($method=="testAccount") {
        testAccount($account, $key);
} else if ($method=="secureAccount") {
        secureAccount($account, $key);
} else if ($method=="getBalance") {
        getBalance($account, $key);
} else if ($method=="listTransactions") {
        listTransactions($account, $key);
} else if ($method=="makeTransaction") {
        makeTransaction($account, $key, $address, $amount);
} else if ($method=="ping") {
	Respond ($id. " pong");
} else if ($method=="system.describe") {
	$procs = array(
		array("name"=>"ping", "params"=>array()),
		array("name"=>"createAccount", "params"=>""),
		array("name"=>"checkAccount", "params"=>array("<account>")),
		array("name"=>"testAccount", "params"=>array("<account>", "<key>")),
		array("name"=>"secureAccount", "params"=>array("<account>", "<key>")),
		array("name"=>"getBalance", "params"=>array("<account>", "<key>")),
		array("name"=>"listTransactions", "params"=>array("<account>", "<key>")),
		array("name"=>"makeTransaction", "params"=>array("<account>", "<key>", "<address>", "<amount>"))
	);
	echo (json_encode(array("id"=>$input->id, "jsonrpc"=>"2.0", "procs"=>$procs)));
}



?>
