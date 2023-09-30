<?php 

  
$sql="SELECT * FROM USER WHERE id=".$_GET['id'];
//  $result=mysql_fetch_row(mysql_query($sql));
  $name = 'test';
  $company_name = 'State Farm Auto Claims';
  $email = 'admin@statefarm.com';
  $mobile_no = '(855) 341-8184';
  $image = 'https://dev.manage.prescriptionhope.com/rxi_use/cron_salesforce5min/images/logo_no_bckgd_vHa.png';
  $image = 'https://manage.prescriptionhope.com/images/cg_vcf_logo.png?v=2';
  // define here all the variable like $name,$image,$company_name & all other
//  header('Content-Type: text/x-vcard');  
//  header('Content-Disposition: inline; filename= "'.$name.'.vcf"');  

  if($image!=""){ 
    $getPhoto               = file_get_contents($image);
    $b64vcard               = base64_encode($getPhoto);
    $b64mline               = chunk_split($b64vcard,74,"\n");
    $b64final               = preg_replace('/(.+)/', ' $1', $b64mline);
    $photo                  = $b64final;
  }
  echo $photo;
  die;;
  $vCard = "BEGIN:VCARD\r\n";
  $vCard .= "VERSION:3.0\r\n";
  $vCard .= "FN:" . $company_name . "\r\n";
  $vCard .= "TITLE:" . $company_name . "\r\n";

  if($email){
    $vCard .= "EMAIL;TYPE=internet,pref:" . $email . "\r\n";
  }
  $vCard .= "URL;TYPE=WEBSITE,pref:http://www.statefarm.com\r\n";
  if($getPhoto){
    $vCard .= "PHOTO;ENCODING=b;TYPE=PNG:";
    $vCard .= $photo . "\r\n";
  }

  if($mobile_no){
    $vCard .= "TEL;TYPE=work;type=pref,voice:" . $mobile_no . "\r\n"; 
  }
$vCard .= "TEL;type=CELL:(855) 231-1590\r\nTEL;type=HOME:(844) 292-8615\r\nTEL;type=WORK:(855) 468-4696\r\n";
  $vCard .= "END:VCARD\r\n";
  
  
//  $vCard = 'BOGIN:VCARD BDAY;VALUE=DATE:1963-09-21 VERSION:3.0 N:Stenerson;Derik FN:Derik Stenerson ORG:Microsoft Corporation ADR;TYPE=WORK,POSTAL,PARCEL:;;One Microsoft Way;Redmond;WA;98052-6399;USA TEL;TYPE=WORK,MSG:+1-425-936-5522 TEL;TYPE=WORK,FAX:+1-425-936-7329 EMAIL;TYPE=INTERNET:deriks@Microsoft.com END:VCARD BEGIN:VCARD VERSION:3.0 N:Ganguly;Anik FN:Anik Ganguly ORG: Open Text Inc. ADR;TYPE=WORK,POSTAL,PARCEL:;Suite 101;38777 West Six Mile Road;Livonia;MI;48152;USA TEL;TYPE=WORK,MSG:+1-734-542-5955 EMAIL;TYPE=INTERNET:ganguly@acm.org END:VCARD BEGIN:VCARD VERSION:3.0 N:Moskowitz;Robert FN:Robert Moskowitz EMAIL;TYPE=INTERNET:rgm-ietf@htt-consult.com END:VCARD';
  
  
  $vCard = "";
  
  echo $vCard;