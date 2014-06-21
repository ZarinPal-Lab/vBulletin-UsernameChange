<?php
if (($vbulletin->userinfo['permissions']['usernamechangepermissions'] & $vbulletin->bf_ugp_usernamechangepermissions['canchangeown']))
{
	// We are able to change our own username
	$vbulletin->input->clean_gpc('p', 'username', TYPE_STR);
	
	if(isset($_SESSION['u']) AND isset($_SESSION['verify_ok'])){
		if ($_SESSION['verify_ok'] == 1) {
			$userdata->set('username', $_SESSION['u']);
			unset($_SESSION['u'], $_SESSION['verify_ok']);
		}	
	}
		
	if ($vbulletin->GPC['username'] AND $vbulletin->GPC['username'] != $vbulletin->userinfo['username'])
	{	
		
		function reqest_cun() {
			global $vbulletin;

			$_SESSION['u'] = $vbulletin->GPC['username'];	
			$price = $vbulletin->userinfo['permissions']['username_price'];				
			$order_id = rand(1000, 99999);
			$_SESSION['order_id'] = $order_id;
			$callback = $vbulletin->options['bburl'].'/forum.php?do=cun&order_id='.$order_id;
			$description_zp = 'Change username from '.$vbulletin->userinfo['username'].' to '.$_POST['username'];
						
			$client = new nusoap_client('https://de.zarinpal.com/pg/services/WebGate/wsdl', 'wsdl');				
			$res = $client->call("PaymentRequest", array(
														array(
															'MerchantID' 	=> $vbulletin->options['username_pin_zp'],
															'Amount' 	    => $price,
															'Description' 	=> $description_zp,
															'Email' 	    => $vbulletin->userinfo['email'],
															'Mobile' 	    => 0,
															'CallbackURL' 	=> $callback
														)
													)
			);
			$_SESSION['Authority'] = $res['Authority'];
			if ($res['Status'] == 100){
				header('location:https://www.zarinpal.com/pg/StartPay/'.$res['Authority'] .'/ZarinGate');
				exit;
			} else 
				eval(standard_error('؛با عرض پوزش درگاه پرداخت در حال حاضر آماده نمیباشد ؛ کد خطا :  .' . $res['Status'] ));							
		}
		
		if ($vbulletin->userinfo['permissions']['username_changedelay'])
		{
			// Username has changed, check last edit time that we made ourselves
			$show['changeusername'] = true;
			
			// Detect when the last edit was made (if at all)
			if (!$lastedit = $db->query_first("
				SELECT MAX(changeid) AS changeid, MAX(change_time) AS change_time, COUNT(changeid) AS numchanges
				FROM " . TABLE_PREFIX . "userchangelog
				WHERE fieldname = 'username'
					AND userid = " . $db->sql_prepare($vbulletin->userinfo['userid']) . "
					AND adminid = " . $db->sql_prepare($vbulletin->userinfo['userid']) . "
				GROUP BY userid
			"))
			{
				// Might not be needed, but for bug prevention in the log if() below
				$lastedit['change_time'] = 0;
				$lastedit['numchanges'] = 0;
			}
			
			if ($vbulletin->userinfo['permissions']['username_changedelay'] AND $lastedit['change_time'] >= (TIMENOW - ($vbulletin->userinfo['permissions']['username_changedelay'] * 86400)))
			{
				// Don't show editusername
				$show['changeusername'] = false;
			}
		
			if ($vbulletin->userinfo['permissions']['username_numchanges'] AND $lastedit['numchanges'] >= $vbulletin->userinfo['permissions']['username_numchanges'])
			{
				// Don't show editusername
				$show['changeusername'] = false;
			}
			
			if ($show['changeusername'])
			{
				if($vbulletin->userinfo['permissions']['username_price'] == 0) {
					$userdata->set('username', $vbulletin->GPC['username']);
				} else 
					reqest_cun();			
			}
			else
			{
				// We can't change our username at this time
				$userdata->error('username_change_nochange');
			}
		}
		else
		{
			// Username has changed, check last edit time that we made ourselves
			$show['changeusername'] = true;
			
			// Detect when the last edit was made (if at all)
			if (!$lastedit = $db->query_first("
				SELECT COUNT(changeid) AS numchanges
				FROM " . TABLE_PREFIX . "userchangelog
				WHERE fieldname = 'username'
					AND userid = " . $db->sql_prepare($vbulletin->userinfo['userid']) . "
					AND adminid = " . $db->sql_prepare($vbulletin->userinfo['userid']) . "
				GROUP BY userid
			"))
			{
				// Might not be needed, but for bug prevention in the log if() below
				$lastedit['change_time'] = 0;
				$lastedit['numchanges'] = 0;
			}
			
			if ($vbulletin->userinfo['permissions']['username_numchanges'] AND $lastedit['numchanges'] >= $vbulletin->userinfo['permissions']['username_numchanges'])
			{
				// Don't show editusername
				$show['changeusername'] = false;
			}
			
			if ($show['changeusername'])
			{
				if($vbulletin->userinfo['permissions']['username_price'] == 0) {
					$userdata->set('username', $vbulletin->GPC['username']);
				} else 
					reqest_cun();
			}					
			else
			{
				// We can't change our username at this time
				$userdata->error('username_change_nochange');
			}
		}
	}
}
?>