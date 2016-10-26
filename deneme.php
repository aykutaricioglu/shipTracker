<?php

$sent_to_mail;
$password_mail;
$cc_mail;
$company_name;
$reply_to_mail;
$reply_to_name;

//$data = imo listesi
$data = get_imo_list();
function get_imo_list() {

	$dest_list    = array(
		"TUZLA",
		"IZMIT",
		"BOSPORUS",
		"BOSPOROS",
		"BOSPHORUS"
	); 

	foreach ($dest_list as $dest) {
		$imo_addresses = "http://www.marinetraffic.com/en/ais/index/ships/all/destination:" . $dest . "/per_page:50";
		$raw_html      = preg_replace('/\s+/', '', file_get_contents($imo_addresses));
		for ($i = 0; strpos($raw_html, "my_fleet_column"); $i++) {
			$raw_html    = substr($raw_html, strpos($raw_html, "my_fleet_column") + 1, strpos($raw_html, "/table") + 5);
			$imo_pos     = strpos($raw_html, "<td>", strpos($raw_html, "<td>") + 4) + 4;
			$vessel_pos  = strpos($raw_html, "ShowDetailsFor:") + strlen("ShowDetailsFor:");
			$vessel_name = substr($raw_html, $vessel_pos);
			$vessel_name = substr($vessel_name, 0, strpos($vessel_name, "href") - 1);
			
			if (is_numeric($buff = substr($raw_html, $imo_pos+4, 7))) {
				$imo_list[]    = trim($buff);
				$vessel_list[] = trim($vessel_name);
			} //is_numeric($buff = substr($raw_html, $imo_pos, 7))
			
		} //$i = 0; strpos($raw_html, "my_fleet_column"); $i++
			
	} //$dest_list as $dest
	return $imo_list;

}


function add_record($imo_no){

$sql= "INSERT INTO imo_date(imo_no,date) VALUES ('{$imo_no}', '{date("Y-m-d")}')";
mysql_query($sql);


}

function send_mail($send_to, $ship_name)
	{
		$user_mail      = $sent_to_mail;
		$user_pass      = $password_mail;
		$mail_att1      = $_SERVER[ 'DOCUMENT_ROOT' ] . '/script/attachments/attachment1';
		$mail_att2      = $_SERVER[ 'DOCUMENT_ROOT' ] . '/script/attachments/attachment2';
		$mail_cont      = file_get_contents("mail.txt");
		$mail_html_cont = file_get_contents("mail_html.txt");
		
		$mail = new PHPMailer;
		
		$mail->isSMTP(); // Set mailer to use SMTP
		$mail->Host       = 'smtp.yandex.com'; // Specify main and backup SMTP servers
		$mail->SMTPAuth   = true; // Enable SMTP authentication
		$mail->Username   = $user_mail; // SMTP username
		$mail->Password   = $user_pass; // SMTP password
		$mail->SMTPSecure = 'tls'; // Enable TLS encryption, `ssl` also accepted
		$mail->Port       = 587; // TCP port to connect to
		//$mail->SMTPDebug = 2;
		$mail->setFrom($sent_to_mail, $company_name); // Add a recipient
		$mail->addReplyTo($reply_to_mail, $reply_to_name);
		$mail->addCC($cc_mail);
		$mail->addAddress($send_to); // Name is optional
		
		$mail->isHTML(true); // Set email format to HTML
		$mail->addAttachment($mail_att1); // Add attachments
		$mail->addAttachment($mail_att2);
		$mail->Subject  = 'Introduction ' . $ship_name;
		$mail->Body     = $mail_html_cont;
		$mail->AltBody  = $mail_cont;
		$mail->CharSet  = 'UTF-8';
		$mail->Encoding = "base64";
		if (!$mail->send()) {
			echo 'Message could not be sent.';
			fwrite($handle,'Mailer Error: ' . $mail->ErrorInfo . '\n'); 
			return false;
		} //!$mail->send()
		else {
			fwrite($handle,'Mail has been sent to ' . $send_to . '\n'); 
			return true;		
		}
	}


?>
