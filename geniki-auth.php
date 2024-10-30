<?php 

function genikiAuth($soapClient, $username, $password, $appkey){
	//Authentication
    try {

        // $soap = new SoapClient($soapurl . "?WSDL");
        $oAuthResult = $soapClient->Authenticate(array(
            'sUsrName' => $username,
            'sUsrPwd' => $password,
            'applicationKey' => $appkey
        ));

        return array(
        	'result' => $oAuthResult->AuthenticateResult->Result, 
        	'key' => $oAuthResult->AuthenticateResult->Key
        );

    } catch(SoapFault $fault) {
        // $order->add_order_note(__('Error ' . $fault, ''));
        return $fault;
    }
    //Authentication - END
}

?>