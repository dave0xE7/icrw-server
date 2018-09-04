<?php

require_once("api2.php");

$method = $_POST['method'];

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
} else if ($method=="makeTransaction") {
        makeTransaction($params[0], $params[1], $params[2], $params[3]);
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
		array("name"=>"makeTransaction", "params"=>array("<account>", "<key>", "<address>", "<amount>"))
	);
	echo (json_encode(array("id"=>$input->id, "jsonrpc"=>"2.0", "procs"=>$procs)));
}

?>
