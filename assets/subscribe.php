<?php
/*
	KD Coming Soon

	Copyright (c) 2015-2016 Kalli Dan. (email : kallidan@yahoo.com)

	KD Coming Soon is free software: you can redistribute it but NOT modify it
	under the terms of the GNU Lesser Public License as published by the Free Software Foundation,
	either version 3 of the LGPL License, or any later version.

	KD Coming Soon is distributed in the hope that it will be useful,
	but WITHOUT ANY WARRANTY; without even the implied warranty of
	MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
	See the GNU Lesser Public License for more details.

	You should have received a copy of the GNU Lesser Public License along with KD Coming Soon.
	If not, see <http://www.gnu.org/licenses/>.
*/

if($_POST) {
	require_once 'emailer/PHPMailerAutoload.php';

	$subscriber_email = addslashes(trim($_POST['email']));
	$emailData = unserialize(base64_decode($_POST['cetitle']));
	$emailSiteName = $emailData['cs_name'] . ' - website';
	$emailToName   = $emailData['cs_name'];
	
	$array = array('valid' => 0, 'message' => "");

	if(!isEmail($subscriber_email)) {
		$array['message'] =  $emailData['cs_emailerr'];
	}else {
		if($emailData['cs_email'] && isEmail($emailData['cs_email'])){
			$subject = 'New Subscriber!';
			$body = "You have a new subscriber at ".$emailData['cs_name']."!\n\nEmail: " . $subscriber_email;

			$mail = new PHPMailer();
			$mail->IsHTML(true);
			if(!isset($emailData['cs_emedhod'])){ //&& $emailData['cs_emedhod'] == 'on'
				$mail->IsMail();
				$mail->From = $subscriber_email;
			}else{
				$mail->isSMTP();
				$mail->Host = $emailData['cs_ehost'];
				if($emailData['cs_esec'] == 'on'){
					$mail->SMTPAuth = true;
					$mail->SMTPSecure = 'ssl'; 
				}else{
					$mail->SMTPAuth = false;
					$mail->SMTPSecure = ''; 
				}
				$mail->Username = $emailData['cs_euser'];
				$mail->Password = $emailData['cs_epass'];
				$mail->Port = $emailData['cs_eport'];
				$mail->From = $emailData['cs_email'];
			}

			$mail->FromName = $emailSiteName;
			$mail->AddReplyTo($subscriber_email);
			$mail->AddAddress($emailData['cs_email'], $emailToName);
			$mail->Subject  = $subject;
			$mail->AltBody  = 'To view this message, you need a HTML compatible email viewer!';
			$mail->WordWrap = 70;
			$mail->MsgHTML($body);

			$CharSet = 'utf-8';
			$Priority = 3;
			if(!$mail->Send()) {
				$array['message'] = $emailData['cs_emailfail']; //$mail->ErrorInfo;
			}else{
				$array['valid'] = 1;
				$array['message'] = $emailData['cs_emailsucc'];

				$res = storeSubscription($subscriber_email, $emailData['cs_db']);
			}

			$mail->ClearAddresses();
			$mail->ClearAllRecipients();
			$mail->ClearAttachments();
		}else{
			$array['valid'] = 1;
			$array['message'] = $emailData['cs_emailsucc'];

			$res = storeSubscription($subscriber_email, $emailData['cs_db']);
		}
	}
	echo json_encode($array);
}

function storeSubscription($email, $cs_db){
	$db = get_db_conn($cs_db);
	$err = 'ERROR';

	if($db){
		$all_emails = array();
		$result = $db->query("SELECT option_id, option_value FROM ".$cs_db['dbprefix']."options WHERE option_name='kd_cs_emails'");
		$err = $db->error;
		if($result && !$err){
			while ($row = $result->fetch_assoc()) {
				$all_emails = explode("~", $row['option_value']);
			}
		}

		$found = 0;
		foreach($all_emails as $e){
			$dat = explode('|', $e);
			if(isset($dat[1]) && $dat[1] == $email){
				$found=1; break;
			}
		}
		if(!$found && $all_emails){
			$now = date('m/d/Y');
			$all_emails[] = $now . '|' . $email; 

			$new_emails = implode('~', $all_emails);

			if($new_emails){
				$result = $db->query("UPDATE ".$cs_db['dbprefix']."options set option_value='".$new_emails."' WHERE option_name='kd_cs_emails'");
				$err = $db->error;
				if($result && !$err){
					$err = 'OK';
				}else{
					$err = 'ERROR';
				}
			}
		}
	}

	return $err;
}

// verify email address...
function isEmail($email) {
    return(preg_match("/^[-_.[:alnum:]]+@((([[:alnum:]]|[[:alnum:]][[:alnum:]-]*[[:alnum:]])\.)+(ad|ae|aero|af|ag|ai|al|am|an|ao|aq|ar|arpa|as|at|au|aw|az|ba|bb|bd|be|bf|bg|bh|bi|biz|bj|bm|bn|bo|br|bs|bt|bv|bw|by|bz|ca|cc|cd|cf|cg|ch|ci|ck|cl|cm|cn|co|com|coop|cr|cs|cu|cv|cx|cy|cz|de|dj|dk|dm|do|dz|ec|edu|ee|eg|eh|er|es|et|eu|fi|fj|fk|fm|fo|fr|ga|gb|gd|ge|gf|gh|gi|gl|gm|gn|gov|gp|gq|gr|gs|gt|gu|gw|gy|hk|hm|hn|hr|ht|hu|id|ie|il|in|info|int|io|iq|ir|is|it|jm|jo|jp|ke|kg|kh|ki|km|kn|kp|kr|kw|ky|kz|la|lb|lc|li|lk|lr|ls|lt|lu|lv|ly|ma|mc|md|mg|mh|mil|mk|ml|mm|mn|mo|mp|mq|mr|ms|mt|mu|museum|mv|mw|mx|my|mz|na|name|nc|ne|net|nf|ng|ni|nl|no|np|nr|nt|nu|nz|om|org|pa|pe|pf|pg|ph|pk|pl|pm|pn|pr|pro|ps|pt|pw|py|qa|re|ro|ru|rw|sa|sb|sc|sd|se|sg|sh|si|sj|sk|sl|sm|sn|so|sr|st|su|sv|sy|sz|tc|td|tf|tg|th|tj|tk|tm|tn|to|tp|tr|tt|tv|tw|tz|ua|ug|uk|um|us|uy|uz|va|vc|ve|vg|vi|vn|vu|wf|ws|ye|yt|yu|za|zm|zw)$|(([0-9][0-9]?|[0-1][0-9][0-9]|[2][0-4][0-9]|[2][5][0-5])\.){3}([0-9][0-9]?|[0-1][0-9][0-9]|[2][0-4][0-9]|[2][5][0-5]))$/i", $email));
}

// create mySQLi connection...
function get_db_conn($cs_db) {
	$conn = mysqli_init();
	if (!$conn) {
		return; //die('mysqli_init failed');
	}
	if (!$conn->options(MYSQLI_INIT_COMMAND, 'SET AUTOCOMMIT = 1')) {
		return; //die('Setting MYSQLI_INIT_COMMAND failed');
	}
	if (!$conn->options(MYSQLI_OPT_CONNECT_TIMEOUT, 5)) {
		return; //die('Setting MYSQLI_OPT_CONNECT_TIMEOUT failed');
	}
	if (!$conn->real_connect($cs_db['dbhost'], $cs_db['dbuser'], $cs_db['dbpassword'], $cs_db['dbname'])) {
		return; //die('Connect Error (' . mysqli_connect_errno() . ') '. mysqli_connect_error());
	}

	return $conn;
}
?>