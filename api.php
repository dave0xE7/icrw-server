<?php
session_start();

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
  $userid = use_input($_POST['userid']);
	$token = use_input($_POST['token']);
  if (file_exists('data/users/'. $userid)) {
    // account found in database
		$userdata = json_decode(file_get_contents('data/users/'. $userid));
		if ($token == $userdata->token) {
      // key was correct
      // updating key
      $token = hash('sha256', time());
      $userdata->token = $token;
      file_put_contents('data/users/'. $userid, json_encode($userdata));
      echo (json_encode(array("userid"=>$userid, "token"=>$token)));
		}
	}
  $userid = hash('sha256', time());
  $token = hash('sha256', $userid);
  if (!file_exists('data/users/'. $userid)) {
    $userdata = json_encode(array("balance"=>"0", "address"=>"", "token"=>$userid));
    file_put_contents('data/users/'. $userid, $userdata);
  }
  echo (json_encode(array("userid"=>$userid, "token"=>$token)));
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

if ($_SERVER["REQUEST_METHOD"] == "POST") {

	$q = use_input($_POST['q']);

	if ($q == "login") {
		$email = use_input($_POST["email"]);
		$password = use_input($_POST["password"]);

		$loginuserid = hash('sha256', $email);
		if (file_exists('data/users/'. $loginuserid)) {
			$userdata = json_decode(file_get_contents('data/users/'. $loginuserid));

			// check password
			if ($password == $userdata->password) {
				$_SESSION['userid'] = $loginuserid;
				$userid = $loginuserid;

				// retruns userid
				//echo ($loginuserid);
				// must add token
				$token = hash('sha256', time());
				$userdata->token = $token;
				file_put_contents('data/users/'. $userid, json_encode($userdata));
				echo (json_encode(array("userid"=>$userid, "token"=>$token)));
			} else {
				echo ("false");
			}
		}
	} else if ($q == "register") {

		$name1 = use_input($_POST["name1"]);
		$name2 = use_input($_POST["name2"]);
		$email = use_input($_POST["email"]);
		$pass1 = use_input($_POST["pass1"]);
		$pass2 = use_input($_POST["pass2"]);

		$reguserid = hash('sha256', $email);
		if (!file_exists('data/users/'. $reguserid)) {
			$userdata = json_encode(array("name1"=>$name1, "name2"=>$name2, "email"=>$email, "password"=>$pass1, "balance"=>"0", "address"=>"", "token"=>"0"));
			file_put_contents('data/users/'. $reguserid, $userdata);
			echo ("true");
		} else {
			echo ("false");
		}

	} else if ($q == "charts") {
		include_once("coinbe.php");
		echo (json_encode(array("btceurval"=>$btceurval, "icrbtcval"=>$icrbtcval, "icreurval"=>$icreurval)));

	} else if ($q == "checklogin") {
		echo (check_login());
  } else if ($q == "getaccount") {
    getAccount();
	} else if ($q == "getnewaddress") {
		if (!empty(check_userid())) {

		}
		return "false";
	} else if ($q == "alldata") {
		// shoild check userid and token
		if (check_login()) {
			if (file_exists('data/users/'. $userid)) {
				$userdata = json_decode(file_get_contents('data/users/'. $userid));

				$name1 = $userdata->name1;
				$name2 = $userdata->name2;
				$email = $userdata->email;
				$balance = $userdata->balance;
				$address = $userdata->address;
				//$balance_eur = $userdata[4]*$icreurval;
			}
			$user = array("userid"=>$userid, "name1"=>$name1, "name2"=>$name2, "email"=>$email);
			$wallet = array ("balance"=>$balance, "address"=>$address); //, $balance_eur
			$data = array("user"=>$user, "wallet"=>$wallet);
			echo (json_encode($data));
		} else {
			echo "false";
		}
	}

	/**
	 * $userid, $name1, $name2, $email,
	 *
	 * $address_icr
	 *
	 * $balance_icr, $balance_eur, $balance_btc
	 *
	 * echo ($btceurval.','.$icrbtcval.','.$icreurval);
	 *
	 * $login_timestamp
	 *
	 */

}

?>
