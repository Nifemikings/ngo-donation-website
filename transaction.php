<html>

<?php  
    require_once("includes/head.php");
?> 

<body>

<?php 
    require_once("includes/header.php");
?> 


<?php
    require_once("includes/braintree_init.php");
    require_once("includes/header.php");
    require 'vendor/autoload.php';
    require 'mail_conf.php';
    use PHPMailer\PHPMailer\PHPMailer;


    if (isset($_GET["id"]))
    {
        $transaction = $gateway->transaction()->find($_GET["id"]);

        $transactionSuccessStatuses = [
            Braintree\Transaction::AUTHORIZED,
            Braintree\Transaction::AUTHORIZING,
            Braintree\Transaction::SETTLED,
            Braintree\Transaction::SETTLING,
            Braintree\Transaction::SETTLEMENT_CONFIRMED,
            Braintree\Transaction::SETTLEMENT_PENDING,
            Braintree\Transaction::SUBMITTED_FOR_SETTLEMENT
        ];

        if (in_array($transaction->status, $transactionSuccessStatuses))
        {
            //Generating Invoice
            class MYPDF extends FPDF
            {
                // Page header
                function Header()
                {
                    // Logo
                    $this->Image('assets/images/pdf-logo.png',10,6);
                    // Arial bold 15
                    $this->SetFont('Arial','B',20);
                    // Move to the right
                    $this->Cell(80);
                    // Title
                    $this->Cell(30,10,'Invoice',1,0,'C');
                    // Line break
                    $this->Ln(20);
                }

                // Page footer
                function Footer()
                {
                    // Position at 1.5 cm from bottom
                    $this->SetY(-15);
                    // Arial italic 8
                    $this->SetFont('Arial','B',8);
                    // Page number
                    $this->Cell(0,10,'Page '.$this->PageNo().'/{nb}',0,0,'C');
                }
            }

            // Instanciation of inherited class
            $pdf = new MYPDF();
            $pdf->AliasNbPages();
            $pdf->AddPage();
            $pdf->SetFont('Arial','B',15);

            $pdf->Cell(80);
            $pdf->Cell(30,10,"Transaction ID :- ".$transaction->id,0,1,'C');
            $pdf->Ln(20);
            $pdf->Cell(80);
            $pdf->Cell(30,10,"Amount :- ".$_GET['amount'],0,1,'C');

            //Invoice Generated and saved at server side
            $content = $pdf->Output('Invoice.pdf','F');


            //Mailing the Invoice to Donor
            $mail = new PHPMailer;
            $email = filter_var(trim($_GET["email"]), FILTER_SANITIZE_EMAIL);

            $donor_email = $_GET['email'];

            // SMTP configuration
            $mail->isSMTP();
            $mail->Host = 'smtp.gmail.com';
            $mail->SMTPAuth = true;
            $mail->Username = $comp_email;
            $mail->Password = $pw;
            $mail->SMTPSecure = 'tls';
            $mail->Port = 587;
            
            //Creating mail
            $mail->setFrom( $comp_email, $comp_name);
            $mail->addAddress($donor_email);
            $mail->Subject = 'Donation Invoice';
            $mail->AddAttachment('Invoice.pdf', $name = 'Invoice',  $encoding = 'base64', $type = 'application/pdf');
            $mail->Body = 'Thank You for Donation! Here is your Invoice.';

            if($mail->send())
            {
                $header = "Success!";
                $icon = "success";
                $message = "Your transaction has been successfully processed. <br> Thank You for your Donation! Invoice has been sent to your email id.";
            }
            else
            {
                $header = "Success!";
                $icon = "success";
                $message = "Your transaction has been successfully processed. <br> Thank You for your Donation! But something went wrong and we couldn\'t send you Invoice..";
            }
        }
        else
        {
            $header = "Transaction Failed!";
            $icon = "fail";
            $message = "Your transaction has a status of " . $transaction->status;
        }
    }
?>

  <?php
    header('Refresh:3; url=index.php');
  ?>

<div class="container">
    <div class="row text-center">
        <div class="col-sm-6 col-sm-offset-3">
        <br><br> <h2 style="color:#0fad00"><?php echo $header; ?></h2>
        <img src="assets/images/<?php echo $icon?>.svg">
        <p style="font-size:20px;color:#5C5C5C;"><?php echo $message; ?></p>
        </div>
    </div>
</div>

<br>
<br>
<?php require_once("includes/footer.php"); ?>
</body>
</html>
