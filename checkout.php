<link rel="stylesheet" href="assets/css/bootstrap.min.css">

<?php
require_once("includes/braintree_init.php");

if ($_SERVER["REQUEST_METHOD"] == "POST")
{
    $amount = $_POST["amount"];
    $nonce = $_POST["payment_method_nonce"];
    $result = $gateway->transaction()->sale([
        'amount' => $amount,
        'paymentMethodNonce' => $nonce,
        'options' => [
          'submitForSettlement' => true
        ]
    ]);
    if ($result->success || !is_null($result->transaction))
    {
     $transaction = $result->transaction;
     header("Location: transaction.php?id=" . $transaction->id."&amount=".$_POST["amount"]."&email=".$_POST["email"]);
    }
    else
    {
        $errorString = "";
        foreach($result->errors->deepAll() as $error)
        {
            $errorString .= 'Error: ' . $error->code . ": " . $error->message . "\n";
        }
        $_SESSION["errors"] = $errorString;
        header("Location: index.php");
    }
}
else
{
    
    header('Refresh:3; url=index.php');
    echo '<div class="container">
            <div class="row text-center">
                <div class="col-sm-6 col-sm-offset-3">
                <br><br> <h2 style="color:#0fad00">Fail!</h2>
                <img src="assets/images/fail.svg">
                <p style="font-size:20px;color:#5C5C5C;">There was a problem with your submission, please try again!</p>
            </div>
            </div>
          </div>';
}
?>

<script src="assets/js/bootstrap.min.js"></script>
<script src="assets/js/jquery-1.11.1.min.js"></script>






