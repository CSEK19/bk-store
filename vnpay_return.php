<?php
    ob_start();
    session_start();
?>
<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="utf-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <!-- The above 3 meta tags *must* come first in the head; any other head content must come *after* these tags -->
        <meta name="description" content="">
        <meta name="author" content="">
        <title>Billing Information</title>
        <!-- Bootstrap core CSS -->
        <link href="public/assets/bootstrap.min.css" rel="stylesheet"/>
        <!-- Custom styles for this template -->
        <link href="public/assets/jumbotron-narrow.css" rel="stylesheet">         
        <script src="public/assets/jquery-1.11.3.min.js"></script>
    </head>
    <body>
        <?php
        $vnp_TmnCode = "A25KNJSA"; //Website ID in VNPAY System
        $vnp_HashSecret = "MDBXYVILBYYAIXFQIULAYRIBAKSLILWJ"; //Secret key

        $startTime = date("YmdHis");
        $expire = date('YmdHis',strtotime('+15 minutes',strtotime($startTime)));
        $vnp_SecureHash = $_GET['vnp_SecureHash'];
        $inputData = array();
        foreach ($_GET as $key => $value) {
            if (substr($key, 0, 4) == "vnp_") {
                $inputData[$key] = $value;
            }
        }
        unset($inputData['vnp_SecureHashType']);
        unset($inputData['vnp_SecureHash']);
        ksort($inputData);
        $i = 0;
        $hashData = "";
        foreach ($inputData as $key => $value) {
            if ($i == 1) {
                $hashData = $hashData . '&' . $key . "=" . $value;
            } else {
                $hashData = $hashData . $key . "=" . $value;
                $i = 1;
            }
        }

        // $secureHash = md5($vnp_HashSecret . $hashData);
		$secureHash = hash('sha256',$vnp_HashSecret . $hashData);
        ?>
        <!--Begin display -->
        <div class="container">
            <div class="header clearfix">
                <h3 class="text-muted">Billing Information</h3>
            </div>
            <div class="table-responsive">
                <div class="form-group">
                    <label >Order Id:</label>
                    
                    <label><?php echo $_GET['vnp_TxnRef'] ?></label>
                </div>    
                <div class="form-group">

                    <label >Price:</label>
                    <label><?=number_format($_GET['vnp_Amount']/100) ?> VNĐ</label>
                </div>  
                <div class="form-group">
                    <label >Billing content:</label>
                    <label><?php echo $_GET['vnp_OrderInfo'] ?></label>
                </div> 
                <div class="form-group">
                    <label >Response Code:</label>
                    <label><?php echo $_GET['vnp_ResponseCode'] ?></label>
                </div> 
                <div class="form-group">
                    <label >VNPAY Code:</label>
                    <label><?php echo $_GET['vnp_TransactionNo'] ?></label>
                </div> 
                <div class="form-group">
                    <label >Bank Id:</label>
                    <label><?php echo $_GET['vnp_BankCode'] ?></label>
                </div> 
                <div class="form-group">
                    <label >Date:</label>
                    <label><?php echo $_GET['vnp_PayDate'] ?></label>
                </div> 
                <div class="form-group">
                    <label >Result:</label>
                    <label>
                        <?php   
                            if ($_GET['vnp_ResponseCode'] == '00') {
                                require_once "mvc/utility/utility.php";
                                require_once "mvc/core/config.php";
                                $user = getUserToken();
                                
                                $order_id = $_GET['vnp_TxnRef'];
                                $vnp_SecureHash = $_GET['vnp_SecureHash'];
                                $money = $_GET['vnp_Amount']/100;
                                $note = $_GET['vnp_OrderInfo'];
                                $vnp_response_code = $_GET['vnp_ResponseCode'];
                                $code_vnpay = $_GET['vnp_TransactionNo'];
                                $code_bank = $_GET['vnp_BankCode'];
                                $time = $_GET['vnp_PayDate'];
                                $date_time = substr($time, 0, 4) . '-' . substr($time, 4, 2) . '-' . substr($time, 6, 2) . ' ' . substr($time, 8, 2) . ':' . substr($time, 10, 2) . ':' . substr($time, 12, 2);
                                
                                $conn = mysqli_connect(HOST, USERNAME, PASSWORD, DATABASE);
                                mysqli_set_charset($conn, 'utf8');

                                $user_id=55;
                                if($_COOKIE["token"]){
                                    $token = $_COOKIE['token'];
                                    $sql = "SELECT user_id
                                            FROM tokens 
                                            WHERE token='$token'";
                                    $resultset = mysqli_query($conn, $sql);
                                    $data = mysqli_fetch_array($resultset, 1);
                                    $user_id= $data["user_id"];
                                }

                                // query
                                $sql = "INSERT INTO payments(order_id, user_id, money, note, vnp_response_code, code_vnpay, code_bank, time) VALUES ($order_id, $user_id, $money, '$note', '$vnp_response_code', '$code_vnpay', '$code_bank', '$date_time')";
                                $result = mysqli_query($conn, $sql);
                                if (!$result) {
                                    die('Error: ' . mysqli_error($conn));
                                }

                                $sql = "SELECT id
                                        FROM orders
                                        WHERE user_id='$user_id'
                                        ORDER BY id DESC
                                        LIMIT 1";
                                $resultset = mysqli_query($conn, $sql);
                                $data = mysqli_fetch_array($resultset, 1);
                                $orderId= $data["id"];

                                $sql = "update orders set status = 4 where id = $orderId";
                                mysqli_query($conn, $sql);

                                // close connection
                                mysqli_close($conn);
                                setcookie('cart', "", -60, '/');
                                
                                echo "Transaction successful";
                            } else {
                                echo "Transaction failed";
                            }
                        ?>

                    </label>
                    <br>
                    <a href="http://localhost/bkstore/Home">
                        <button>Back to home</button>
                    </a>
                </div> 
            </div>
            <p>
                &nbsp;
            </p>
            <footer class="footer">
                <p>Copyright &copy; 2023 BK Store</p>
            </footer>
        </div>  
    </body>
</html>
