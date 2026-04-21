<?php

function initiatempesa()
{
  global $config; // get the global config
  $accses_type = "";
  if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $accses_type = "api";
    // Parse JSON input
    $input = json_decode(file_get_contents('php://input'), true);
    // Check if username and phone are set and validate them
    $username = isset($input['username']) ? trim($input['username']) : null;
    $phone = isset($input['phone']) ? trim($input['phone']) : null;

    // You could add validation here to check if the username and phone are valid
    if (empty($username) || empty($phone)) {
      // Handle validation error, e.g. return an error response
      echo json_encode(['error' => 'Username and phone are required']);
      exit;
    }
  } elseif ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $accses_type = "web";
    $user = User::_info();
    if ($user) {
      // Check if the user info is available
      $username = $user->username ?? null;
      $phone = $user->phonenumber ?? null;
    } else {
      echo "<script>toastr.error('Please login to continue');</script>";
      die();
    }
  } else {
    // Handle unsupported request method
    echo json_encode(['error' => 'Unsupported request method']);
    exit;
  }


  if (empty($username) || empty($phone)) {
    if ($accses_type == "api") {
      header('Content-Type: application/json; charset=utf-8');
      echo json_encode(["status" => "error", "message" => "Please fill all fields"]);
      exit();
    } else {
      echo "<script>toastr.error('Please fill all fields');</script>";
      die();
    }
  }
  // Format phone number
  $phone = (substr($phone, 0, 1) == '+') ? str_replace('+', '', $phone) : $phone;
  $phone = (substr($phone, 0, 1) == '0') ? preg_replace('/^0/', '254', $phone) : $phone;
  $phone = (substr($phone, 0, 1) == '7') ? preg_replace('/^7/', '2547', $phone) : $phone;
  $phone = (substr($phone, 0, 1) == '1') ? preg_replace('/^1/', '2541', $phone) : $phone;
  $phone = (substr($phone, 0, 1) == '0') ? preg_replace('/^01/', '2541', $phone) : $phone;
  $phone = (substr($phone, 0, 1) == '0') ? preg_replace('/^07/', '2547', $phone) : $phone;


  $CallBackURL = U . 'callback/mpesa';


  // Fetch user record
  $CheckId = ORM::for_table('tbl_customers')
    ->where('username', $username)
    ->order_by_desc('id')
    ->find_one();

  if (!$CheckId) {
    if ($accses_type == "api") {
      header('Content-Type: application/json; charset=utf-8');
      echo json_encode(["status" => "error", "message" => "User " . $username . " not found"]);
      exit();
    } else {
      echo "<script>toastr.error('User " . $username . " not found');</script>";
      die();
    }
  }

  $UserId = $CheckId->id;


  // Fetch payment gateway record
  $PaymentGatewayRecord = ORM::for_table('tbl_payment_gateway')
    ->where('username', $username)
    ->where('status', 1)
    ->order_by_desc('id')
    ->find_one();

  if (!$PaymentGatewayRecord) {
    if ($accses_type == "api") {
      header('Content-Type: application/json; charset=utf-8');
      echo json_encode(["status" => "error", "message" => "Unable to process payment, please reload the page"]);
      exit();
    } else {
      r2(U . 'order/view/' . $PaymentGatewayRecord->id, 'e', 'Unable to process payment, please reload the page');
    }
  }

  // Update user details
  $CheckId->phonenumber = $phone;
  $CheckId->username = $username;
  $CheckId->save();

  $amount = $PaymentGatewayRecord->price;



  // Get the M-Pesa consumer key
  $mpesa_consumer_key = ORM::for_table('tbl_appconfig')
    ->where('setting', 'mpesa_consumer_key')
    ->find_one();
  $mpesa_consumer_key = ($mpesa_consumer_key) ? $mpesa_consumer_key->value : null;

  // Get the M-Pesa consumer secret
  $mpesa_consumer_secret = ORM::for_table('tbl_appconfig')
    ->where('setting', 'mpesa_consumer_secret')
    ->find_one();
  $mpesa_consumer_secret = ($mpesa_consumer_secret) ? $mpesa_consumer_secret->value : null;

  // Get the M-Pesa business code
  $mpesa_business_code = ORM::for_table('tbl_appconfig')
    ->where('setting', 'mpesa_business_code')
    ->find_one();
  $mpesa_business_code = ($mpesa_business_code) ? $mpesa_business_code->value : null;

  // Get the M-Pesa shortcode type
  $mpesa_shortcode_type = ORM::for_table('tbl_appconfig')
    ->where('setting', 'mpesa_shortcode_type')
    ->find_one();

  $mpesa_shortcode_type = ($mpesa_shortcode_type) ? $mpesa_shortcode_type->value : null;

  if ($mpesa_shortcode_type == 'BuyGoods') {
    $mpesa_buygoods_till_number = ORM::for_table('tbl_appconfig')
      ->where('setting', 'mpesa_buygoods_till_number')
      ->find_one();
    $mpesa_buygoods_till_number = ($mpesa_buygoods_till_number) ? $mpesa_buygoods_till_number->value : null;
    $PartyB = $mpesa_buygoods_till_number
      ? $mpesa_buygoods_till_number
      : $mpesa_business_code;
    $Type_of_Transaction = 'CustomerBuyGoodsOnline';
  } else {
    $PartyB = $mpesa_business_code;
    $Type_of_Transaction = 'CustomerPayBillOnline';
  }

  // Get the M-Pesa pass key
  $mpesa_pass_key = ORM::for_table('tbl_appconfig')
    ->where('setting', 'mpesa_pass_key')
    ->find_one();
  $mpesa_pass_key = ($mpesa_pass_key) ? $mpesa_pass_key->value : null;

  if (!$mpesa_pass_key || !$mpesa_consumer_key || !$mpesa_consumer_secret || !$mpesa_business_code) {
    if ($accses_type == "api") {
      header('Content-Type: application/json; charset=utf-8');
      echo json_encode(["status" => "error", "message" => "M-Pesa configuration not set"]);
      exit();
    } else {
      r2(U . 'order/view/' . $PaymentGatewayRecord->id, 'e', 'M-Pesa configuration not set');
    }
  }

  // Get the M-Pesa environment
  $mpesa_env = ORM::for_table('tbl_appconfig')
    ->where('setting', 'mpesa_env')
    ->find_one();
  $mpesa_env = ($mpesa_env) ? $mpesa_env->value : null;

  if ($mpesa_env == 'null' || $mpesa_consumer_key == 'null' || $mpesa_consumer_secret == 'null' || $mpesa_business_code == 'null') {
    if ($accses_type == "api") {
      header('Content-Type: application/json; charset=utf-8');
      echo json_encode(["status" => "error", "message" => "M-Pesa configuration not set"]);
      exit();
    } else {
      r2(U . 'order/view/' . $PaymentGatewayRecord->id, 'e', 'M-Pesa configuration not set');
    }
  }

  $Time_Stamp = date("Ymdhis");
  $password = base64_encode($mpesa_business_code . $mpesa_pass_key . $Time_Stamp);
  if ($mpesa_env == "live") {
    $stk_url = 'https://api.safaricom.co.ke/mpesa/stkpush/v1/processrequest';
    $Token_URL = 'https://api.safaricom.co.ke/oauth/v1/generate?grant_type=client_credentials';
  } elseif ($mpesa_env == "sandbox") {
    $stk_url = 'https://sandbox.safaricom.co.ke/mpesa/stkpush/v1/processrequest';
    $Token_URL = 'https://sandbox.safaricom.co.ke/oauth/v1/generate?grant_type=client_credentials';
  } else {
    return json_encode(["Message" => "invalid application status"]);
  };
  $headers = ['Content-Type:application/json; charset=utf8'];
  $curl = curl_init();
  if ($curl === false) {
    return null;
  }
  curl_setopt($curl, CURLOPT_URL, $Token_URL);
  curl_setopt($curl, CURLOPT_HTTPHEADER, ['Authorization: Basic ' . base64_encode($mpesa_consumer_key . ':' . $mpesa_consumer_secret)]);
  curl_setopt($curl, CURLOPT_HEADER, false);
  curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
  curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
  $response = curl_exec($curl);
  if ($response === false) {
    // Log cURL error if needed
    _log("cURL error: " . curl_error($curl));
  }
  curl_close($curl);
  $access_token = json_decode($response, true)['access_token'] ?? null;
  if (!$access_token) {
    sendTelegram("M-Pesa payment failed: " . json_encode($response));
    if ($accses_type == "api") {
      header('Content-Type: application/json; charset=utf-8');
      echo json_encode(["status" => "error", "message" => "Failed to generate token"]);
      exit();
    } else {
      r2(U . 'order/view/' . $PaymentGatewayRecord->id, 'e', 'Failed to generate token');
    }
  }
  if ($accses_type == "api") {
    $accountReference = 'Hotspot-' . $username;
  } else {
    $accountReference = $user->phonenumber;
  }
  //INITIATE CURL
  $curl2 = curl_init();
  curl_setopt($curl2, CURLOPT_URL, $stk_url);
  curl_setopt($curl2, CURLOPT_HTTPHEADER, ['Content-Type:application/json', 'Authorization:Bearer ' . $access_token]);
  $curl2_post_data = array(
    //Fill in the request parameters with valid values
    'BusinessShortCode' => $mpesa_business_code,
    'Password' => $password,
    'Timestamp' => $Time_Stamp,
    'TransactionType' => $Type_of_Transaction,
    'Amount' => $amount,
    'PartyA' => $phone,
    'PartyB' => $PartyB,
    'PhoneNumber' => $phone,
    'CallBackURL' => $CallBackURL,
    'AccountReference' => $accountReference,
    'TransactionDesc' => 'Payment for Hotspot',
  );
  $data2_string = json_encode($curl2_post_data);
  curl_setopt($curl2, CURLOPT_RETURNTRANSFER, true);
  curl_setopt($curl2, CURLOPT_POST, true);
  curl_setopt($curl2, CURLOPT_POSTFIELDS, $data2_string);
  curl_setopt($curl2, CURLOPT_HEADER, false);
  curl_setopt($curl2, CURLOPT_SSL_VERIFYPEER, 0);
  curl_setopt($curl2, CURLOPT_SSL_VERIFYHOST, 0);
  $curl_response = curl_exec($curl2);
  if (!$curl_response) {
    sendTelegram("M-Pesa payment failed: " . json_encode($curl_response));
    if ($accses_type == "api") {
      header('Content-Type: application/json; charset=utf-8');
      echo json_encode(["status" => "error", "message" => "Failed to process STK Push"]);
      exit();
    } else {
      r2(U . 'order/view/' . $PaymentGatewayRecord->id, 'e', 'Failed to process STK Push');
    }
  }
  $mpesaResponse = json_decode($curl_response);
  $responseCode = $mpesaResponse->ResponseCode ?? null;
  $MerchantRequestID = $mpesaResponse->MerchantRequestID ?? null;
  $CheckoutRequestID = $mpesaResponse->CheckoutRequestID ?? null;
  $resultDesc = $mpesaResponse->CustomerMessage ?? 'No message';
  if ($responseCode == "0") {

    $PaymentGatewayRecord->pg_paid_response = $resultDesc;
    $PaymentGatewayRecord->pg_request = $CheckoutRequestID;
    $PaymentGatewayRecord->username = $username;
    $PaymentGatewayRecord->payment_method = 'Mpesa Stk Push';
    $PaymentGatewayRecord->payment_channel = 'Mpesa Stk Push';
    $PaymentGatewayRecord->save();

    header('Content-Type: application/json; charset=utf-8');
    if ($accses_type == "api") {
      echo json_encode(["status" => "success", "message" => "Enter Mpesa Pin to complete"]);
      exit();
    } else {
      r2(U . 'order/view/' . $PaymentGatewayRecord->id, 's', 'Enter M-pesa Pin on your Phone to complete');
    }
  } else {
    sendTelegram("M-Pesa payment failed: " . json_encode($curl_response));
    if ($accses_type == "api") {
      header('Content-Type: application/json; charset=utf-8');
      echo json_encode(["status" => "error", "message" => "There is an issue with the transaction, please try again"]);
      exit();
    } else {
      r2(U . 'order/view/' . $PaymentGatewayRecord->id, 'e', 'There is an issue with the transaction, please try again');
    }
  }
}


//CloudTik_api_user