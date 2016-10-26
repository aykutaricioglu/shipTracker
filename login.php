<?php
	require_once 'html_header.php';
	require_once 'connect_db.php';
	require_once dirname(__FILE__) . '/resources/PHPMailer-master/PHPMailerAutoload.php';
	$user       = trim("raspbuntu@gmail.com");
	$pass       = trim("a5051252");
	$login_data = "j_email=" . $user . "&j_password=" . $pass . "&submit=Ok";
	$login_url  = "http://www.equasis.org/EquasisWeb/authen/HomePage?fs=HomePage";
	$search_url = "http://www.equasis.org/EquasisWeb/restricted/ShipList?fs=ShipSearch";
	
	$ISM_manager  = "UNKNOWN"; //default
	$Ship_manager = "UNKNOWN"; //default
	$my_file      = dirname(__FILE__) . '/log.txt'; //log file location
	$handle       = fopen($my_file, 'w+');
	$dest_list    = array(
		"TUZLA",
		"IZMIT",
		"BOSPORUS",
		"BOSPOROS",
		"BOSPHORUS"
	);
	$delay        = 15; //seconds
	$days_to_wait = 30; //days
	
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
	
	fwrite($handle, "Elde edilen toplam IMO sayısı: " . count($imo_list) . "\n");
	foreach ($imo_list as $index => $imo_no) {
		
		if (mysql_fetch_array(mysql_query("SELECT imo_no FROM ship_list WHERE imo_no='{$imo_no}

	'")) !== false) {
			$visit_date = date("Y-m-d");
			$result = mysql_query("SELECT last_visit_date FROM ship_list WHERE imo_no={$imo_no}
	
	") or die(mysql_error());
			while ($row = mysql_fetch_array($result))
				$visit_date = $row[ 'last_visit_date' ];
			$visit_date = new DateTime($visit_date);
			$cur_date   = new DateTime("now");
			$diff       = $cur_date->diff($visit_date)->format("%a");
			
			if ($diff < $days_to_wait) {
				unset($imo_list[ $index ]);
				unset($vessel_list[ $index ]);
			} //$diff < $days_to_wait
			
		} //mysql_fetch_array(mysql_query("SELECT imo_no FROM ship_list WHERE imo_no='{$imo_no} '")) !== false
		
	} //$imo_list as $index => $imo_no
	
	$imo_list    = array_values($imo_list);
	$vessel_list = array_values($vessel_list);
	fwrite($handle, "İşlem yapılacak IMO sayısı: " . count($imo_list) . "\n");
	fwrite($handle, "Tahmini işlem süresi: " . (count($imo_list) / (60 / $delay)) . " dk.\n");
	fwrite($handle, date("H:i:s") . " İşlem başlatıldı...\n");

	$ch = curl_init();
	curl_setopt($ch, CURLOPT_URL, $login_url);
	curl_setopt($ch, CURLOPT_POST, 1);
	curl_setopt($ch, CURLOPT_POSTFIELDS, $login_data);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($ch, CURLOPT_COOKIEFILE, "c:/cookies/cookie.txt");
	curl_setopt($ch, CURLOPT_COOKIEJAR, "c:/cookies/cookie.txt");
	curl_exec($ch);

	foreach ($imo_list as $index => $imo_no) {
		$search_data = "P_PAGE=1&P_IMO=" . $imo_no . "&Submit=SEARCH";
		curl_setopt($ch, CURLOPT_URL, $search_url);
		curl_setopt($ch, CURLOPT_POST, 1);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $search_data);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_COOKIEFILE, "c:/cookies/cookie.txt");
		curl_setopt($ch, CURLOPT_COOKIEJAR, "c:/cookies/cookie.txt");
		$search_output = curl_exec($ch);
		
		if (strpos($search_output, "ISM Manager")) {
			$ISM_manager = substr($search_output, strpos($search_output, "ISM Manager") + strlen("ISM Manager") + 9);
			$ISM_manager = trim(substr($ISM_manager, 0, strpos($ISM_manager, "</TD>")));
		} //strpos($search_output, "ISM Manager")
		
		
		if (strpos($search_output, "Ship manager/Commercial manager")) {
			$Ship_manager = substr($search_output, strpos($search_output, "Ship manager/Commercial manager") + strlen("Ship manager/Commercial manager") + 9);
			$Ship_manager = substr($Ship_manager, 0, strpos($Ship_manager, "</TD>"));
		} //strpos($search_output, "Ship manager/Commercial manager")
		
		$ISM_list[]      = $ISM_manager;
		$Ship_man_list[] = trim($Ship_manager);
		fwrite($handle, "\n\n");
		fwrite($handle, "Currently working on: \n");
		fwrite($handle, "Ship Name: \t" . $vessel_list[ $index ] . "\n");
		fwrite($handle, "IMO No: \t" . $imo_no . "\n");
		fwrite($handle, "ISM Manager: \t" . $ISM_manager . "\n");
		fwrite($handle, "Ship Manager: \t" . $Ship_manager . "\n");
		fwrite($handle, "Status: \t" . ($index + 1) . "/" . count($imo_list) . "\n");
		fwrite($handle, "***********************************\n");
		fwrite($handle, "\n");

		if(!(mysql_num_rows(mysql_query("SELECT * FROM ship_list WHERE vessel_name='{$vessel_list[ $index ]}'")) > 0) ){//eğer bu kayıt yoksa ship_list'e ekle
			
			$sql = "INSERT INTO ship_list(imo_no, vessel_name, ism_manager_name, ship_manager_name, last_visit_date) VALUES('{$imo_no}','{$vessel_list[ $index ]}','{$ISM_manager}','{$Ship_manager}','2015-10-10')";
			$result = mysql_query($sql, $db) or die(mysql_error());

}
		
		if (mysql_fetch_array(mysql_query("SELECT * FROM ism_manager_list WHERE ism_manager_name='{$ISM_manager}'")) !== false) { //eğer bu kayıt varsa;
			$result = mysql_query("SELECT mail_address FROM ism_manager_list WHERE ism_manager_name='{$ISM_manager}'") or die(mysql_error());
			while ($row = mysql_fetch_array($result))
				$ISM_mail_address = $row[ 'mail_address' ];
			if ($Ship_mail_address !== NULL) {
				send_mail($ISM_mail_address, $vessel_list[ $index ]);
				$cur_date = date("Y-m-d");
				$sql      = "UPDATE ship_list SET last_visit_date='{$cur_date}' WHERE vessel_name='{$vessel_list[ $index ]}';";
				$query = mysql_query($sql, $db) or die(mysql_error());
			} //$Ship_mail_address !== NULL
			else
				fwrite($handle, "Mail containing NULL value \n");
		} //mysql_fetch_array(mysql_query("SELECT * FROM ism_manager_list WHERE ism_manager_name='{$ISM_manager}'")) !== false
		else {
			
			$sql = "INSERT INTO ism_manager_list(ism_manager_name) VALUES('{$ISM_manager}');";
			$query = mysql_query($sql, $db) or die(mysql_error());
			fwrite($handle, "ISM manager tablosuna kayıt eklendi. \n" . $ISM_manager . "\n");
			
		}
		
		if (mysql_fetch_array(mysql_query("SELECT * FROM ship_manager_list WHERE ship_manager_name='{$Ship_manager}'")) !== false) { //eğer bu kayıt varsa;
			$result = mysql_query("SELECT mail_address FROM ship_manager_list WHERE ship_manager_name='{$Ship_manager}'") or die(mysql_error());
			while ($row = mysql_fetch_array($result))
				$Ship_mail_address = $row[ 'mail_address' ];
			if ($Ship_mail_address !== NULL) {
				send_mail($Ship_mail_address, $vessel_list[ $index ]);
				$cur_date = date("Y-m-d");
				$sql      = "UPDATE ship_list SET last_visit_date='{$cur_date}' WHERE vessel_name='{$vessel_list[ $index ]}';";
				$query = mysql_query($sql, $db) or die(mysql_error());
			} //$Ship_mail_address !== NULL
			else
				fwrite($handle, "Mail containing NULL value \n");
		} //mysql_fetch_array(mysql_query("SELECT * FROM ship_manager_list WHERE ship_manager_name='{$Ship_manager}'")) !== false
		else {
			
			$sql = "INSERT INTO ship_manager_list(ship_manager_name) VALUES('{$Ship_manager}');";
			$query = mysql_query($sql, $db) or die(mysql_error());
			fwrite($handle, "Ship manager tablosuna kayıt eklendi. \n" . $Ship_manager . "\n");
			
		}
		
		
		if ($index < count($imo_list))
			sleep($delay);
	} //$imo_list as $index => $imo_no
	
	echo "İşlem tamamlandı.";
	fwrite($handle, "\n\n\n" . date("H:i:s") . "\nİşlem sonlandı. \nExit code:1");
	fclose($handle);
	
	function send_mail($send_to, $ship_name)
	{
		$user_mail      = 'operation@tmmarin.com';
		$user_pass      = 'beh2015gg';
		$mail_att1      = $_SERVER[ 'DOCUMENT_ROOT' ] . '/script/presentation.pdf';
		$mail_att2      = $_SERVER[ 'DOCUMENT_ROOT' ] . '/script/TANITIM.DOC';
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
		$mail->setFrom('operation@tmmarin.com', 'TurkaMarine'); // Add a recipient
		$mail->addReplyTo('sales@tmmarin.com', 'Gözde YÜKSEL');
		$mail->addCC('info@tmmarin.com');
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
