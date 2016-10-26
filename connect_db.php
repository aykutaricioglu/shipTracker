<?php
	require_once 'html_header.php';
	$host = "localhost";
	$db_name = "script";
	$user_name = "root";
	$user_pass = "toor";
	$db = @mysql_connect($host, $user_name, $user_pass);

	if(!$db){
		echo "Hesap bilgileri yanlış VEYA mysql sunucusu çalışır durumda değil.";
		exit;
}
	$db_check = @mysql_select_db($db_name, $db);
	if(!$db_check){
		echo "Veritabanı adı eşleşmesi bulunamadı.<br/>";
		echo "Veritabanı oluşturuluyor...<br/>";
		$sql = "CREATE DATABASE ".$db_name." DEFAULT CHARACTER SET utf8";
		$sql = mysql_query($sql, $db) or die(mysql_error());
		if($sql)
			echo "Veritabanı oluşturuldu.<br/>";
		$db_check = @mysql_select_db($db_name, $db);
}
	mysql_query("SET NAMES 'utf8'");	

?>
