<?php

	$time = microtime(true);
	// Determining the microsecond fraction
	$microSeconds = sprintf("%06d", ($time - floor($time)) * 1000000);
	// Creating our DT object
	$tz = new DateTimeZone("Etc/UTC"); // NOT using a TZ yields the same result, and is actually quite a bit faster. This serves just as an example.
	$dt = new DateTime(date('Y-m-d H:i:s.'. $microSeconds, $time), $tz);
	$dt->modify('1 day');
	// Compiling the date. Limiting to milliseconds, without rounding
	$iso8601Date = sprintf(
		"%s%03d%s",
		$dt->format("Y-m-d\TH:i:s."),
		floor($dt->format("u")/1000),
		$dt->format("O")
	);
	
	$dt->modify('1 hour');
	$iso8601Date2 = sprintf(
		"%s%03d%s",
		$dt->format("Y-m-d\TH:i:s."),
		floor($dt->format("u")/1000),
		$dt->format("O")
	);

    $transactions = new \Transactions\Transactions();
    $user = new \User\User( ID_USER );
    $email = $user->__get('email');
    $mp = new MP( MERCADO_CLIENT, MERCADO_SECRET );

    $packages = json_decode( file_get_contents( PATH . '/packages.json' ) );
    $data = array();

    foreach( $packages AS $p => $d ) {
        $data[$p] = array(
            "payer_email" => $email,
            "back_url" => "http://presshunters.com/settings/payments",
            "reason" => "Preapproval preference",
            "external_reference" => "OP-1234",
            "auto_recurring" => array(
                "frequency" => 1,
                "frequency_type" => "months",
                "transaction_amount" => $d->price,
                "currency_id" => "ARS",
                "start_date" => $iso8601Date, //"2014-12-10T14:58:11.778-03:00",
                "end_date" => $iso8601Date2)//"2015-06-10T14:58:11.778-03:00")
        );

        /*$data[$p] = array(
            "payer_email" => $email,
            "back_url" => "http://presshunters.com/settings/payments",
            "reason" => "Preapproval preference",
            "external_reference" => null,
            "auto_recurring" => array(
                "frequency" => 1,
                "frequency_type" => "months",
                "transaction_amount" => $d->price,
                "currency_id" => "ARS"
            )
        );*/
    }

    $errCode = "";
    $errMsg = "";

    foreach( $packages AS $p => $d ) {
        try {
        //${'preference' . $p} = $mp->create_preference( $data[$p] );
            ${'preference' . $p} = $mp->create_preapproval_payment( $data[$p] );
        } catch (MercadoPagoException $e) {
            $errCode = $e->getCode(); // 400, por ejemplo
            $errMsg = $e->getMessage(); // 400, por ejemplo
        }
    }
    ?>
    <div class="page-head">
        <div class="page-title">
            <h1>Payments</h1>
        </div>
    </div>
    <?php if (empty($errCode)) { ?>
        <?php if( !$user->due_date OR strtotime( $user->due_date ) < time() ): ?>
            <div class="jumbotron" style="padding: 50px; background-color: #333; color: #fff;">

                <h2>Choose your plan</h2>

                <p>We have three available plans to choose from.. <strong>Free (50 Emails)</strong> and <strong>1000 Emails</strong> and <strong>5000 Emails</strong>. Choose which plan you would like to sign up for.</p>

                <?php foreach( $packages AS $p => $d ): ?>
                    <a href="<?php echo ${'preference' . $p}["response"]["init_point"]; ?>" target="_blank" name="MP-Checkout" class="orange-ar-m-sq-arall btn btn-danger btn-lg"><?php echo $d->emails; ?> Emails ( $<?php echo $d->price; ?> )</a>
                <?php endforeach; ?>

                <br /><br />
                <p style="font-size: 10px;">* All payments are processed with MercadoPago.com</p>

            </div>
        <?php endif; ?>
    <?php
        } else {
    ?>
        <div class="jumbotron" style="padding: 50px; background-color: #333; color: #fff;">
            <strong>Please Login With Another Account</strong>
            <?php
                //echo $errMsg;
            ?>
        </div>
    <?php
        }
    ?>

    <?php if (empty($errCode)) { ?>
        <div class="portlet box red">
            <div class="portlet-title">
                <div class="caption">
                    <i class="fa fa-cogs"></i> Transactions
                </div>
            </div>
            <div class="portlet-body">
                <div class="table-scrollable">
                    <table class="table table-hover">
                        <thead>
                        <tr>
                            <th>Title</th>
                            <th>List</th>
                            <th>Sent / Opens</th>
                        </tr>
                        </thead>
                        <tbody>
                        <?php foreach( $transactions->getList( ID_USER ) AS $t ): ?>
                            <tr>
                                <td><?php echo $t->title; ?></td>
                                <td>
                                    <?php
                                    $list = json_decode( $t->lists );
                                    foreach( $list AS $name => $key ) {
                                        $lists = new \Lists\Lists( $key );
                                        echo $lists->title . ', ';
                                    }
                                    ?>
                                </td>
                                <td><?php echo $t->sent; ?> / 0</td>
                            </tr>
                        <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    <?php } ?>
