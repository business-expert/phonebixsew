<?php

error_reporting('0');
// Prevent browser cache
header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");
header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
header("Cache-Control: no-store, no-cache, must-revalidate");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");

// Remove headers
function remove_headers($string) {
    $headers = array(
        "/to\:/i",
        "/from\:/i",
        "/bcc\:/i",
        "/cc\:/i",
        "/Content\-Transfer\-Encoding\:/i",
        "/Content\-Type\:/i",
        "/Mime\-Version\:/i"
    );
    if (preg_replace($headers, '', $string) == $string) {
        return $string;
    } else {
        die('');
    }
}

// Separate headers by either \r\n or \n to ensure email sends properly
$uself = 0;
$headersep = (!isset($uself) || ($uself == 0)) ? "\r\n" : "\n";


// ==================================================================
// Insert your information and correct link paths here
$mailto = 'umnitech01@gmail.com';
$from = "PhoenixSewing.com Contact Form";
$formurl = "formmail.php";
$errorurl = "validation-error.php";
$thankyouurl = "/thank-you.htm";
// ==================================================================
// ==================================================================
// Add or remove your specific Form variables here
$name = remove_headers($_POST['name']);
$email = remove_headers($_POST['email']);
$subject = remove_headers($_POST['subject']);
$comments = remove_headers($_POST['message']);
$spam = remove_headers($_POST['captcha']);
$http_referrer = getenv("HTTP_REFERER");
// ==================================================================
// ==================================================================
// Un-comment if you want to add/clean PHONE to form
// if (preg_match("{[A-Za-z]}", $phone))
// {
//   header( "Location: $errorurl" );
//	exit ;
// }
// ==================================================================
// Clean Captcha: random numbers 1 through 5 (1+3, 4+2, 5+3, etc)
session_start();
if (!isset($_SESSION['num1']) || !isset($_SESSION['num2'])) {
    // no known session. cannot validate captcha
    header("Location: $errorurl");
    exit;
}
$sum = (int) $_SESSION['num1'] + (int) $_SESSION['num2'];
if (isset($_POST['captcha']) && (int) $_POST['captcha'] !== $sum) {
    // captcha given but incorrect
    header("Location: $errorurl");
    exit;
} else {
    // captcha correct, show a new one next time
    unset($_SESSION['num1'], $_SESSION['num2']);
}

// Send Message
$message = "This message was sent from:\n" .
        "$http_referrer\n\n" .
// ==================================================================	
// Add or remove your specific Form variables here
        "Name: $name\n\n" .
        "Email: $email\n\n" .
        "Subject: $subject\n\n" .
        "Comments: $comments\n\n" .
// ==================================================================

        "\n\n------------------------------------------------------------\n";
$total_size=array_sum($_FILES["upload"]["size"]);

if (count($_FILES["upload"]["tmp_name"]) > 0 && isset($_FILES["upload"]["tmp_name"][0]) && trim($_FILES["upload"]["tmp_name"][0])!='') {
    if($total_size<=10485760)
{
    for ($i = 0; $i < count($_FILES["upload"]["tmp_name"]); $i++) {
        move_uploaded_file($_FILES["upload"]["tmp_name"][$i], 'tmp/' . basename($_FILES['upload']['name'][$i]));
    }

    //move_uploaded_file($_FILES["upload"]["tmp_name"],'temp/'.basename($_FILES['upload']['name']));
    mail_attachment($from, $mailto, $subject, $message);
    echo "<script type=text/javascript>window.location='/thank-you.html'</script>";
}else{
    header("Location: $errorurl");
    exit;
}
} else {
    $headers = "From: " .($from) . "\r\n";
                        $headers .= "Reply-To: ".($from) . "\r\n";
                        $headers .= "Return-Path: ".($from) . "\r\n";;
                        $headers .= "MIME-Version: 1.0\r\n";
                        $headers .= "Content-Type: text/html; charset=ISO-8859-1\r\n";
                        $headers .= "X-Priority: 3\r\n";
                        $headers .= "X-Mailer: PHP". phpversion() ."\r\n";
    if (mail($mailto, $from, $message,$headers)) {
        // echo "<script type=text/javascript>alert('Thank you for your feedback')</script>";
        echo "<script type=text/javascript>window.location='/thank-you.html'</script>";
    } else {
        // echo "<script type=text/javascript>alert('Thank you for your feedback')</script>";
        echo "<script type=text/javascript>window.location='/thank-you.html'</script>";
    }
}

function mail_attachment($from, $to, $subject, $message) {
    
    $files = array();
    for ($i = 0; $i < count($_FILES['upload']['name']); $i++) {
        $files[$i] = $_FILES['upload']['name'][$i];
    }

    $email_subject = $subject; // The Subject of the email 
    $email_txt = $message; // Message that the email has in it   
    $email_to = $to; // Who the email is to   
    $headers = "From: " . $from;
    // boundary 
    $semi_rand = md5(time());
    $mime_boundary = "==Multipart_Boundary_x{$semi_rand}x";

    // headers for attachment 
    $headers .= "\nMIME-Version: 1.0\n" . "Content-Type: multipart/mixed;\n" . " boundary=\"{$mime_boundary}\"";

    // multipart boundary 
    $email_message = "This is a multi-part message in MIME format.\n\n" . "--{$mime_boundary}\n" . "Content-Type: text/plain; charset=\"iso-8859-1\"\n" . "Content-Transfer-Encoding: 7bit\n\n" . $message . "\n\n";
    $email_message .= "--{$mime_boundary}\n";

    for ($x = 0; $x < count($files); $x++) {
        $file = fopen('tmp/' . $files[$x], "rb");
        $data = fread($file, filesize('tmp/' . $files[$x]));
        fclose('tmp/' . $file);
        $data = chunk_split(base64_encode($data));
        $email_message .= "Content-Type: {\"application/octet-stream\"};\n" . " name=\"$files[$x]\"\n" .
                "Content-Disposition: attachment;\n" . " filename=\"$files[$x]\"\n" .
                "Content-Transfer-Encoding: base64\n\n" . $data . "\n\n";
        $email_message .= "--{$mime_boundary}\n";
    }



    $ok = @mail($email_to, $email_subject, $email_message, $headers);
    for ($i = 0; $i < count($_FILES['upload']['name']); $i++) {
        $files[$i] = unlink('tmp/' . $_FILES['upload']['name'][$i]);
    }
    return $ok;
}

?>