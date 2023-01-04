<?php

    function GenerateAcessToken()
    {

        $callUrl            =   TRIM("https://zoom.us/oauth/token?grant_type=account_credentials&account_id=".env('ACCOUNT_ID'));
        $CLIENT_ID          =   env('CLIENT_ID');
        $CLIENT_SECRET      =   env('CLIENT_SECRET');
        $header             =   ["Authorization: Basic ".base64_encode($CLIENT_ID . ":" . $CLIENT_SECRET),"Content-Type: application/json","Accept: application/json",];

        $curl = curl_init( $callUrl );

        curl_setopt( $curl, CURLOPT_POST, true);
        curl_setopt( $curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt( $curl, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt( $curl, CURLOPT_CUSTOMREQUEST, "POST");
        curl_setopt( $curl, CURLOPT_POSTFIELDS, "");
        curl_setopt( $curl, CURLOPT_HTTPHEADER, $header);

        $response = curl_exec($curl);
        curl_close($curl);
        return $result = json_decode($response);

    }

    function SendRequest( $data )
    {
        $isValidate 		= 	GenerateAcessToken();
        $header             =   ["Authorization: Bearer ".$isValidate->access_token,"Content-Type: application/json","Accept: application/json",];
        $callUrl            =   TRIM( $data['url'] );
        $fields             =   ( isset( $data['fields'] ) && !empty( $data['fields'] ) ) ? json_encode($data['fields']) : '';

        $curl = curl_init( $callUrl );
        // return $isValidate;exit;

        curl_setopt( $curl, CURLOPT_POST, true);
        curl_setopt( $curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt( $curl, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt( $curl, CURLOPT_CUSTOMREQUEST, $data['method']);
        curl_setopt( $curl, CURLOPT_POSTFIELDS, $fields);
        curl_setopt( $curl, CURLOPT_HTTPHEADER, $header);

        $response = curl_exec($curl);

        curl_close($curl);
        return json_decode($response);

    }


?>
