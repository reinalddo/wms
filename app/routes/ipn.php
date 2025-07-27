<?php

  /**
    * Frontend
  **/

  $app->get('/ipn', function( ) use ( $app ) {

    $transactions = new \Transactions\Transactions();
    $mp = new MP( MERCADO_CLIENT, MERCADO_SECRET );

    $params = ["access_token" => $mp->get_access_token()];

    // Get the payment reported by the IPN. Glossary of attributes response in https://developers.mercadopago.com
    if($_GET["topic"] == 'payment'){
    	$payment_info = $mp->get("/collections/notifications/" . $_GET["id"], $params, false);
    	$merchant_order_info = $mp->get("/merchant_orders/" . $payment_info["response"]["collection"]["merchant_order_id"], $params, false);
    // Get the merchant_order reported by the IPN. Glossary of attributes response in https://developers.mercadopago.com
    }else if($_GET["topic"] == 'merchant_order'){
    	$merchant_order_info = $mp->get("/merchant_orders/" . $_GET["id"], $params, false);
    }

    //If the payment's transaction amount is equal (or bigger) than the merchant order's amount you can release your items
    if ($merchant_order_info["status"] == 200) {
    	$transaction_amount_payments= 0;
    	$transaction_amount_order = $merchant_order_info["response"]["total_amount"];
        $payments=$merchant_order_info["response"]["payments"];
        foreach ($payments as  $payment) {
        	if($payment['status'] == 'approved'){
    	    	$transaction_amount_payments += $payment['transaction_amount'];
    	    }
        }
        if($transaction_amount_payments >= $transaction_amount_order){


          $transactions->save( array(
            'id_user' => 1
          , 'name' => ''
          , 'email' => ''
          , 'transaction_id' => ''
          , 'transaction_amount' => $merchant_order_info["response"]["total_amount"]
          , 'transaction_fee' => ''
          ) );


          print_r( $merchant_order_info['response'] );

        } else{
          print_r( $merchant_order_info['response'] );
    		echo "dont release your items";
    	}
    }

  });
