<?php

	/**
	 *  Sends a shipping confirmation HTML email via your gmail account to the given recipient
	 *  with the contents of their order and tracking details
	 *
	 *  @author Matthew Grindell
	 *  @version 1.3
	 *  @since 2013-05-16
	 */



	/**
	 *	Sends an HTML email through your gmail account
	 *
	 *  @param string $email Email address of recipient
	 *  @param string $name Name of recipient
	 *  @param string $subject Subject of email
	 *  @param string $content HTML content of email
	 *  @return boolean $email_sent Returns true if email successfully sent, otherwise false
	*/
	function sendEmailContent($email, $name, $subject, $content){

		/* Enter your gmail credentials below */
		$sender_name = "Joe Bloggs";
		$gmail_username = "your.gmail.username";
		$email_password = "enter.your.password.here";
		
		$mail  = new PHPMailer();
		$mail->IsSMTP();
		 
		/* Begin gmail config */
		$mail->SMTPAuth   = true;                  	// enable SMTP authentication
		$mail->SMTPSecure = "ssl";                 	// sets the prefix to the server
		$mail->Host       = "smtp.gmail.com";      	// sets gmail as the SMTP server
		$mail->Port       = 465;                   	// set the SMTP port for the gmail server
		$mail->Username   = $gmail_username;  		// gmail username
		$mail->Password   = $email_password;        // gmail password
		$mail->From       = $gmail_username."@gmail.com";
		$mail->FromName   = $sender_name;
		$mail->Subject    = $subject;
		$mail->MsgHTML($content);
		//$mail->AddReplyTo("reply@email.com","reply name"); //they answer here, optional
		$mail->AddAddress($email,$name);
		$mail->IsHTML(true); 						// flag as HTML email message
		
		$email_sent = true;
		if(!$mail->Send()) {						// send email, check if successfully sent
			$email_sent = false;
			echo "Mailer Error: " . $mail->ErrorInfo;
		} else {
			$email_sent = true;
			echo "Message sent!";
		}
		return $email_sent;
	}
	
	/**
	 *	Encapsulate purchase id with Trademe url to link to the purchase page
	 *
	 *  @param string $purchase_id Trademe purchase ID
	 *  @return string $link Returns a url to the Trademe purchase
	*/
	function createURLLink($purchase_id){
		$p_removed_id = substr($purchase_id, 1);
		$link = '<a href="https://www.trademe.co.nz/MyTradeMe/PurchaseSummary.aspx?asid='.$p_removed_id.'">'.$purchase_id.'</a>';
		return $link;
	}


	/**
	 *	Generate the HTML content of the shipping confirmation email, and send the email
	 *
	 *  @param string $email_address Email address of recipient
	 *  @param string $name Name of recipient
	 *  @param string $delivery_address Address of recipient for parcel delivery
	 *  @param array $purchase_id_arr Array of purchase ids, as strings
	 *  @param array $auction_title_arr Array of auction titles that buyer purchased, as strings
	 *  @param array $auction_qtys_arr Array of item quantities that buyer purchased, as strings
	 *  @param string $tracking_num Tracking code for parcel (NZ Post, Courier Post)
	 *  @return boolean $email_sent Returns true if email successfully sent, otherwise false
	*/
	function sendShipmentConfirmationEmail($email_address, $name, $delivery_address, $purchase_id_arr, $auction_title_arr, $auction_qtys_arr, $tracking_num){

		/* Generate option banner image for dispaly as header of email */
		$banner_image_url = "";										// Shop logo image or similar, eg "http://farm6.static.flickr.com/123.img"
		$banner_link = "<img src=\"".$banner_image_url."\">";
		if(strcmp($banner_image_url,"") == 0){						// If no image url, remove image source tag
			$banner_link = "";
		}

		/* Generate tracking url */
		$tracking_num_val = '<a href="https://www.nzpost.co.nz/tools/tracking/item/'.$tracking_num.'">'.$tracking_num.'</a>';
		if(strcmp($tracking_num,"") == 0){							// If tracking box was left empty, then no tracking number
			$tracking_num_val = "none";
		}
		
		/* Begin HTML email content */
		$content = <<<HEADER
		<html>
		<head>
		</head>
		<body>
		<table style="font-family:Trebuchet MS, sans-serif; font-size: 14px" cellspacing="0">
		<td bgcolor="#000000" colspan=3 height="150" width="759">
HEADER;
		$content .= $banner_link;
		$content .= <<<HEADERCONT
		</td>
		<tr><td bgcolor="#7CB342" height="6" colspan="3"></td></tr>
		<tr><td width="7" bgcolor="#7CB342"></td>
		<td bgcolor="#FAFAFA" style="padding:20px">
		<br>
HEADERCONT;

		$content .= "Hi ".$name;
		$content .= <<<MSG
		<br><br>
		Great news! Your payment has been received, and your item(s) are being prepared for shipping.<br>
		Orders are usually <b>dispatched within 24 hours</b> - Monday to Friday, of receiving this shipping confirmation.<br><br><br>
MSG;
		$content .= '<table style="font-family:Trebuchet MS, sans-serif; font-size: 16px" bgcolor="#EEEEEE" cellpadding="5">';
		$pos = 0;
		while($pos < sizeof($auction_title_arr)){
			$content .= "<tr><td>&nbsp;&nbsp;&nbsp;".$auction_title_arr[$pos]."&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</td><td>Qty: ".$auction_qtys_arr[$pos]."&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</td><td>".createURLLink($purchase_id_arr[$pos])."&nbsp;&nbsp;&nbsp;</td></tr>";
			$pos += 1;
		}
		$content .= '</table><br>';

		$content .= "<table><tr><td>";
		$content .= '<table style="font-family:Trebuchet MS, sans-serif; font-size: 14px"><tr><td><b>Delivery address:</b></td></tr></table>';
		$content .= '<table style="font-family:Trebuchet MS, sans-serif; font-size: 14px"><tr><td>&nbsp;&nbsp;&nbsp;&nbsp;';
		$pos = 0;
		$addy = str_replace("\n","<br>&nbsp;&nbsp;&nbsp;&nbsp;",$delivery_address);
		$content .= $addy;
		$content .= '</td></tr></table><br><br>';
		$content .= '</td><td width="100"></td><td>';
		$content .= '<table style="font-family:Trebuchet MS, sans-serif; font-size: 14px" cellpadding="0">
					 <tr><td><br>Shipping company:&nbsp;&nbsp;</td><td><br>NZ Post</td></tr>
					 <tr><td>Delivery target:</td><td>1 - 3 working days</td></tr>
					 <tr><td>Tracking number:</td><td>';
		$content .= $tracking_num_val;
		$content .= "</td></tr>
					 </table><br><br>";
		$content .= "</td></tr></table>";

		$content .= <<<FOOTER
		Thanks for your order<br>
		Kind regards<br>
		Your Name

		</td>
		<td width="7" bgcolor="#7CB342"></td></tr>
		<tr><td bgcolor="#7CB342" height="6" colspan="3"></td></tr>
		</table>

		</body>
		</html>
FOOTER;

		/* Generate email subject line */
		$subject_auction_number = 0;								// default for non-Trademe sales
		$pos = 0;
		while($pos < sizeof($purchase_id_arr)){
			if($purchase_id_arr[$pos] != '0'){
				$subject_auction_number = $purchase_id_arr[$pos];	// set purchase ID number for email subject, 
				$pos = sizeof($purchase_id_arr);					// use first if more than one
			}
			$pos += 1;
		}

		$subject_auction_title = "";								// default for non-Trademe sales
		$pos = 0;
		while($pos < sizeof($auction_title_arr)){
			if(strcmp($auction_title_arr[$pos], "") != 0){
				$subject_auction_title = $auction_title_arr[$pos];	// set auction title for email subject, 
				$pos = sizeof($auction_title_arr);					// use first if more than one
			}
			$pos += 1;
		}

		$subject = "Shipping Confirmation - Trademe #".$subject_auction_number." - ".$subject_auction_title;

		/* Send Email */
		$email_sent = Send_Email_Content($email_address, $name, $subject, $content);

		return $email_sent;
	}
	
?>