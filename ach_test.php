<?php

$bypass_auth = 1;

$DebitXML = '<?xml version="1.0" encoding="utf-8"?>
<request>
	<credentials>
		<accountid>627</accountid>
		<securekey>ix4y33AmDUNk3D5OA2EW8n</securekey>
		<apiversion>3.3.17</apiversion>
	</credentials>
	<auth>
		<transtype>Debit</transtype>
		<defaultinfo>
			<ipaddress>10.23.27.157</ipaddress>
			<ordernumber>22222</ordernumber>
			<description>Invoice #22222 (03/05/2018) for patient #99999</description>
		</defaultinfo>
		<check>
			<amount>135.00</amount>
			<seccode>WEB</seccode>
			<bankinfo>
				<nameonaccount>John Test</nameonaccount>
				<accounttype>Checking</accounttype>
				<subaccounttype>Personal</subaccounttype>
				<routing>111111111</routing>
				<account>2222222222</account>
			</bankinfo>
		</check>
	</auth>
</request>';

$ch = curl_init();

curl_setopt($ch, CURLOPT_URL, 'https://www.myachgateway.com/gw/processxml.cfm');
curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, $DebitXML);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

$ach_result = curl_exec($ch);
$error = curl_error($ch);

echo 'Request:<br>';
var_dump(htmlspecialchars($DebitXML));

echo '<br><br>Result:<br>';
var_dump($ach_result);

echo '<br><br>Error:<br>';
var_dump($error);
