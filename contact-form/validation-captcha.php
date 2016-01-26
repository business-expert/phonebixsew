<?php
session_start();
if (!isset($_SESSION['num1']) || !isset($_SESSION['num2'])) {
    exit('"Unknown session"');
}
$sum = (int)$_SESSION['num1'] + (int)$_SESSION['num2'];
if (isset($_POST['captcha']) && (int)$_POST['captcha'] === $sum) {
    exit('true');
}
exit('false');
?>
