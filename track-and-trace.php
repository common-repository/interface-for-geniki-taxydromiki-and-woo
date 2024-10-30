<?php 

//Shortcode
function ifgtapifwoo_track_and_trace(){

	if(isset($_GET['voucher'])){

		$voucherNumber = sanitize_title($_GET['voucher']);

	} else {

		$form = "<form method='GET' action=''>";
		$form .= "<input type='text' name='voucher' placeholder='Voucher Number'/>";
		$form .= "<input type='submit' value='Search Voucher'/>";
		$form .= "</form>";

		// return 'No valid voucher Number. Check the URL.';
		return $form;
	}

	if(isset($_GET['lang'])){
		
		$lang = sanitize_title($_GET['lang']);

	} else {

		$lang = 'el';
	}
	
	// $lang = $_GET['lang'];
	

	// return $voucherNumber;

	$username = get_option('username');
    $password = get_option('password');
    $appkey = get_option('appkey');
    $soapurl = "https://voucher.taxydromiki.gr/JobServicesV2.asmx";

    $soap = new SoapClient($soapurl . "?WSDL");
    $auth = genikiAuth($soap, $username, $password, $appkey);

    if ($auth['result'] != 0) {

    	return 'Auth Error. Contact us.';

    } else {

    	$form = "<form method='GET' action=''>";
		$form .= "<input type='text' name='voucher' placeholder='Voucher Number'/>";
		$form .= "<input type='submit' value='Search Voucher'/>";
		$form .= "</form>";
		$form .= "<hr>";

    	$xml = array(
                'authKey' => $auth['key'],
                'voucherNo' => $voucherNumber,
                'language' => $lang
            );

    	$ttResult = $soap->TrackAndTrace($xml);

    	// return print_r($ttResult, true);

    	if ($ttResult->TrackAndTraceResult->Result == 0){

    		$checkpoints = $ttResult->TrackAndTraceResult->Checkpoints->Checkpoint;

    		$string = $form;

    		$string .= '<h4>Voucher Number: '.$voucherNumber . '</h4>';

    		$string .= '<table>';
    		$string .= '<tr><th>Ημερομηνία / Ώρα</th><th>Κατάσταση</th><th>Τοποθεσία</th></tr>';

    		if(is_array($checkpoints)){
    			foreach ($checkpoints as $checkpoint){

	    			$date=date_create($checkpoint->StatusDate);
	    			$dateFinal = date_format($date, 'd-m-Y \/ G:i:s');

	    			$string .= '<tr>';
	    			$string .= '<td><center>' . $dateFinal . '</center></td>';
	    			$string .= '<td><center>' . $checkpoint->Status . '</center></td>';
	    			$string .= '<td><center>' . $checkpoint->Shop . '</center></td>';
	    			$string .= '</tr>';
    			}
    		} else {

    			$date=date_create($checkpoints->StatusDate);
	    		$dateFinal = date_format($date, 'd-m-Y \/ G:i:s');
    			$string .= '<tr>';
    			$string .= '<td><center>' . $dateFinal . '</center></td>';
    			$string .= '<td><center>' . $checkpoints->Status . '</center></td>';
    			$string .= '<td><center>' . $checkpoints->Shop . '</center></td>';
    			$string .= '</tr>';
    		}

    		

    		$string .= '</table>';
    		$string .= '<br><br><br>';

    		//For Debugging
    		// $string .= '<pre>' . print_r($checkpoints, true) . '</pre>';

    		return $string;
    		// return print_r($checkpoints, true);
    		
    	} else {
    		return 'error';
    	}
    }


}

// register shortcode
add_shortcode('ifgtapifwoo-track-and-trace', 'ifgtapifwoo_track_and_trace');

?>