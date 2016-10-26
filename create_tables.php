<?php
	require_once 'connect_db.php';

	
	$sql_table[] = "CREATE TABLE ship_list(
					id_no INT(6) UNSIGNED ZEROFILL AUTO_INCREMENT NOT NULL PRIMARY KEY,
					imo_no INT(7) UNSIGNED NOT NULL,
					vessel_name CHAR(20) NOT NULL,
					ism_manager_name CHAR(50) NOT NULL,
					ship_manager_name CHAR(50) NOT NULL,
					last_visit_date CHAR(10) NULL
					) DEFAULT CHARACTER SET utf8;";

	$sql_table[] = "CREATE TABLE ism_manager_list(
					id_no INT(6) UNSIGNED ZEROFILL AUTO_INCREMENT NOT NULL PRIMARY KEY,
					ism_manager_name CHAR(50) NOT NULL,
					mail_address CHAR(20) NULL,
					phone_number INT(15) NULL
					) DEFAULT CHARACTER SET utf8;";

	$sql_table[] = "CREATE TABLE ship_manager_list(
					id_no INT(6) UNSIGNED ZEROFILL AUTO_INCREMENT NOT NULL PRIMARY KEY,
					ship_manager_name CHAR(50) NOT NULL,
					mail_address CHAR(20) NULL,
					phone_number INT(15) NULL
					) DEFAULT CHARACTER SET utf8;";

	foreach($sql_table as $table){
		$sql= mysql_query($table, $db) or die(mysql_error());		
		
}
		if($sql)
			echo "Tablo(lar) oluşturuldu.";

		mysql_close($db);	

?>

