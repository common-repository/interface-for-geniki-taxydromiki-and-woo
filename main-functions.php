<?php
function ifgtapifwoo_generateVoucher( $post_id ) {

    $order = wc_get_order( $post_id );

    //get Settings
    $username = get_option('username');
    $password = get_option('password');
    $appkey = get_option('appkey');
    $pagetype = get_option('pagetype');
    $testmode = get_option('testmode');

    $soapurl = ($testmode==1 ? 'https://testvoucher.taxydromiki.gr/JobServicesV2.asmx' : 'https://voucher.taxydromiki.gr/JobServicesV2.asmx');

    
    $soap = new SoapClient($soapurl . "?WSDL");
    $auth = genikiAuth($soap, $username, $password, $appkey);

    //get shipping method
    $shipping_items = $order->get_items( 'shipping' );
    $shipping_method = '';

    if( $shipping_items ){
        $method_name = reset($shipping_items)->get_method_id();
    }
    //get shipping method - END

    //get payment method
    $payment_method_name = $order->get_payment_method();

    $services = '';
    $CodAmount = 0;

    //check if cash on delivery
    if (strcmp($payment_method_name, 'cod') == 0)
    {
        $services .= 'αμ';
        $CodAmount = $order->get_total();
    }

    if ( $method_name != 'local_pickup' && $method_name) {

        $last_name = $order->get_shipping_last_name();
        $first_name = $order->get_shipping_first_name();
        $name = $last_name . ' ' . $first_name;

        $address = $order->get_shipping_address_1() . ', ' . $order->get_shipping_address_2();
        $country = $order->get_shipping_country();
        $city = $order->get_shipping_city() . ', ' . $country;
        $phone = $order->get_billing_phone();
        $weight = ifgtapifwoo_calculate_order_weight( $order );
        $pieces = 1;
        $zip = $order->get_shipping_postcode();
        $message = $order->get_customer_note();
        $ReceivedDate = date("Y-m-d");

        if($country == 'CY'){
            $services .= ',φρ';
        }
        
        //create voucher data
        $oVoucher = array(
            'OrderId' => $post_id,
            'Name' => $name,
            'Address' => $address,
            'City' => $city,
            'Telephone' => $phone,
            'Zip' => $zip,
            'Destination' => "",
            'Courier' => "",
            'Pieces' => $pieces,
            'Weight' => $weight,
            'Comments' => $message,
            'Services' => $services,
            'CodAmount' => $CodAmount,
            'InsAmount' => 0,
            'VoucherNumber' => "",
            'SubCode' => "",
            'BelongsTo' => "",
            'DeliverTo' => "",
            'ReceivedDate' => $ReceivedDate
        );

        if ($auth['result'] != 0) {

            $order->add_order_note(__('Order not sent to Geniki Taxydromiki due to authentication failure', ''));

        } else {
        
            $xml = array(
                'sAuthKey' => $auth['key'],
                'oVoucher' => $oVoucher,
                'eType' => "Voucher"
            );

            $oResult = $soap->CreateJob($xml);

            if ($oResult->CreateJobResult->Result != 0) {

                $order->add_order_note(__('Job was not created successfully, please contact Geniki Taxydromiki. Result Code: ' . $oResult->CreateJobResult->Result, ''));

            } else { //if createjob was successful
                $jobID = $oResult->CreateJobResult->JobId;

                $voucherLink = $soapurl . '/GetVouchersPdf?authKey=' . urlencode($auth['key']) . '&voucherNumbers=' . $oResult->CreateJobResult->Voucher . '&Format=' . $pagetype . '&extraInfoFormat=None';

                $order->add_order_note(__('Job with ID: ' . wp_kses_post($jobID) . ' was sent successfully to Gen. Taxydromiki.<br> Voucher number is ' . wp_kses_post($oResult->CreateJobResult->Voucher) . ' <br><a target="_blank" href="' . esc_url( $voucherLink ) . '"><strong>Print</strong><a>', ''));

                //for testing - Get Voucher Job and add meta to order
                $vResult = $soap->GetVoucherJob(array(
                    'sAuthKey' => $auth['key'], 
                    'nJobId' => $jobID
                ));
                $voucherJob = $vResult->GetVoucherJobResult->Job;

                // if (get_option('testmode') == 1){
                //     $order->add_order_note( print_r($voucherJob, true) );
                // }
                

                //Add meta data to order
                ifgtapifwoo_add_meta($post_id, 'geniki-taxidromiki-jobid', $jobID);
                ifgtapifwoo_add_meta($post_id, 'geniki-taxidromiki-voucher-date', $voucherJob->Date);
                ifgtapifwoo_add_meta($post_id, 'geniki-taxidromiki-is-closed', $voucherJob->IsClosed);
                ifgtapifwoo_add_meta($post_id, 'geniki-taxidromiki-is-canceled', $voucherJob->IsCanceled);
                ifgtapifwoo_add_meta($post_id, 'geniki-taxidromiki-voucher-url', sanitize_url($voucherLink));
                ifgtapifwoo_add_meta($post_id, 'geniki-taxidromiki-voucher-number', $oResult->CreateJobResult->Voucher);

            }

        }


    } else {
        //If shipping method is local_pickup, there's no need to create voucher
        $order->add_order_note(__('Voucher was not created because shipping method is Local Pickup or no shipping method has been set.', ''));
    }



}


function ifgtapifwoo_closePendingJob( $post_id ) {

    //get Settings
    $username = get_option('username');
    $password = get_option('password');
    $appkey = get_option('appkey');
    $testmode = get_option('testmode');

    $soapurl = ($testmode==1 ? 'https://testvoucher.taxydromiki.gr/JobServicesV2.asmx' : 'https://voucher.taxydromiki.gr/JobServicesV2.asmx');


    $order = wc_get_order( $post_id );

    $soap = new SoapClient($soapurl . "?WSDL");
    $auth = genikiAuth($soap, $username, $password, $appkey);

    $date = get_post_meta($post_id, 'geniki-taxidromiki-voucher-date', true);


    $soap->ClosePendingJobsByDate(array(
        'sAuthKey' => $auth['key'],
        'dFr' => $date,
        'dTo' => $date
    ));

    $jobID = get_post_meta($post_id, 'geniki-taxidromiki-jobid', true);
    $oResult = $soap->GetVoucherJob(array(
        'sAuthKey' => $auth['key'], 
        'nJobId' => $jobID
    ));
    $voucherJob = $oResult->GetVoucherJobResult->Job;
    
    $order->add_order_note( print_r($voucherJob, true) );

    $isClosed = print_r($voucherJob->IsClosed, true);

    ifgtapifwoo_add_meta( $post_id, 'geniki-taxidromiki-is-closed', $isClosed);

}

function ifgtapifwoo_cancelJob( $post_id ) {

    $username = get_option('username');
    $password = get_option('password');
    $appkey = get_option('appkey');
    $testmode = get_option('testmode');

    $soapurl = ($testmode==1 ? 'https://testvoucher.taxydromiki.gr/JobServicesV2.asmx' : 'https://voucher.taxydromiki.gr/JobServicesV2.asmx');


    $order = wc_get_order( $post_id );

    $soap = new SoapClient($soapurl . "?WSDL");
    $auth = genikiAuth($soap, $username, $password, $appkey);

    //Get jobId meta value from post meta
    $jobID = get_post_meta($post_id, 'geniki-taxidromiki-jobid', true);

    $isCanceled = get_post_meta($post_id, 'geniki-taxidromiki-is-canceled', true);

    $xml = array(
        'sAuthKey' => $auth['key'],
        'nJobId' => $jobID,
        'bCancel' => ($isCanceled ? false : true )
    );

    $oResult = $soap->CancelJob($xml);
    $cancelResult = print_r($oResult->CancelJobResult, true);            

    if ( $cancelResult == 0 ) {

        $order->add_order_note(__('Job with ID: ' . $jobID . ' was ' . ($isCanceled ? 're-enabled' : 'canceled') . ' successfully.', ''));

        //for testing - Get Voucher Job and add meta to order
        $oResult = $soap->GetVoucherJob(array(
            'sAuthKey' => $auth['key'], 
            'nJobId' => $jobID
        ));
        $voucherJob = $oResult->GetVoucherJobResult->Job;

        ifgtapifwoo_add_meta($post_id, 'geniki-taxidromiki-is-canceled', $voucherJob->IsCanceled);

    }
    else
    {
        $order->add_order_note(__('Job with ID: ' . $jobID . ' was not cancelled successfully, please contact Geniki Taxydromiki. Result Code: ' . $cancelResult, ''));
    }

}

function ifgtapifwoo_getVouchersPDF( $order_ids, $pagetype ){

    //get Settings
    $username = get_option('username');
    $password = get_option('password');
    $appkey = get_option('appkey');
    $testmode = get_option('testmode');

    $soapurl = ($testmode==1 ? 'https://testvoucher.taxydromiki.gr/JobServicesV2.asmx' : 'https://voucher.taxydromiki.gr/JobServicesV2.asmx');

    $vouchersString = '';

    $voucherNumToSkip = ($testmode==1 ? 45 : 15);

    foreach ($order_ids as $order_id) {

        $voucherNumber = get_post_meta($order_id, 'geniki-taxidromiki-voucher-number', true);

        if( $voucherNumber && substr($voucherNumber, 0, 2) != $voucherNumToSkip ){
            $vouchersString .= '&voucherNumbers=' . $voucherNumber;
        }
    }

    $soap = new SoapClient($soapurl . "?WSDL");
    $auth = genikiAuth($soap, $username, $password, $appkey);

    if ($auth['result'] == 0) {

        $vouchersLink = $soapurl . '/GetVouchersPdf?authKey=' . urlencode($auth['key']) . $vouchersString . '&Format=' . $pagetype . '&extraInfoFormat=None';

        return $vouchersLink;

    } else {

       return 0;
    }

}

?>
