<?php 

use PHPMailer\PHPMailer\PHPMailer;
require 'vendor/autoload.php';
require 'mail_conf.php';

if ($_SERVER["REQUEST_METHOD"] == "POST")
{
    $mail = new PHPMailer;
	$name = strip_tags(trim($_POST["name"]));
    $name = str_replace(array("\r","\n"),array(" "," "),$name);
    $email = filter_var(trim($_POST["email"]), FILTER_SANITIZE_EMAIL);
    $message = trim($_POST["message"]);

    if ( empty($name) OR empty($message) OR !filter_var($email, FILTER_VALIDATE_EMAIL))
    {
        // Set a 400 (bad request) response code and exit.
        http_response_code(400);
        echo "Oops! There was a problem with your submission. Please complete the form and try again.";
        exit;
    }
    
    $contact_name = $_POST['name'];
    $contact_email = $_POST['email'];
    $contact_message = $_POST['message'];

    // SMTP configuration
    $mail->isSMTP();
    $mail->Host = 'smtp.gmail.com';
    $mail->SMTPAuth = true;
    $mail->Username = $comp_email;
    $mail->Password = $pw;
    $mail->SMTPSecure = 'tls';
    $mail->Port = 587;
    
    $mail->setFrom( $comp_email, $comp_name);
    $mail->addReplyTo($contact_email, $contact_name);

    // Add a recipient
    $mail->addAddress($reply_email);
    
    // Email subject
    $mail->Subject = 'Contact form';

    // Set email format to HTML
    $mail->isHTML(true);

    // Email body content
    $mailContent = 'Name:- '.$contact_name.'<br>'.'Email:- '.$contact_email.'<br>'.'Message:- '.$contact_message;
    $mail->Body = $mailContent;

    // Send email
    if($mail->send())
    {
        http_response_code(200);
        echo "Thank You! Your message has been sent.";
    }
    else
    {
    // Set a 500 (internal server error) response code.
        http_response_code(500);
        echo "Oops! Something went wrong and we couldn't send your message.";
    }
}
else
{
// Not a POST request, set a 403 (forbidden) response code.
    http_response_code(403);
    echo "There was a problem with your submission, please try again.";
}

?>