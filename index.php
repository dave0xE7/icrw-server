<?php

require_once("EasyBitcoin-PHP/easybitcoin.php");

$intercrone = new Bitcoin("InterCronerpc", "1337133713371337", "localhost", "8443");

$info = $intercrone->getinfo();
#$info = $intercrone->listtransactions();

print_r ($info);

print_r($intercrone->listtransactions());
print_r($intercrone->listaccounts());
<<<<<<< HEAD
print_r($intercrone->getbestblock());
=======
>>>>>>> f58a20ab7b1f563c9809c3920c67d3029ec52658

?>
