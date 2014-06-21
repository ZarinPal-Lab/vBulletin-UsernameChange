<?php
if ($_REQUEST['do'] == 'editprofile')
{
	// Temporarily unregister this, we need to hook it
	$page_templater->unregister('customfields');
	
	// Create the edit username template
	$templater = vB_Template::create('username_change_editusername');
	
	// Defaults
	$show['changeusername'] 			= true;	// Can we change username?
	
	// Detect when the last edit was made (if at all)
	if ($lastedit = $db->query_first("
		SELECT MAX(changeid) AS changeid, MAX(change_time) AS change_time, COUNT(changeid) AS numchanges
		FROM " . TABLE_PREFIX . "userchangelog
		WHERE fieldname = 'username'
			AND userid = " . $db->sql_prepare($vbulletin->userinfo['userid']) . "
			AND adminid = " . $db->sql_prepare($vbulletin->userinfo['userid']) . "
		GROUP BY userid
	"))
	{
		// Register the last change time
		$templater->register('lastchange', vbdate($vbulletin->options['dateformat'], $lastedit['change_time'], true));
		$templater->register('numchanges', $lastedit['numchanges'], true);
	}
	else
	{
		// Might not be needed, but for bug prevention in the log if() below
		$lastedit['change_time'] = 0;
		$lastedit['numchanges'] = 0;
	}
	
	if (!($vbulletin->userinfo['permissions']['usernamechangepermissions'] & $vbulletin->bf_ugp_usernamechangepermissions['canchangeown']))
	{
		// Don't show editusername
		$show['changeusername'] = false;
		
		// Include reason
		$templater->register('changeusername_reason', $vbphrase['change_username_nopermission']);
	}
	
	if ($vbulletin->userinfo['permissions']['username_changedelay'] AND $lastedit['change_time'] >= (TIMENOW - ($vbulletin->userinfo['permissions']['username_changedelay'] * 86400)))
	{
		// Don't show editusername
		$show['changeusername'] = false;
		
		// Include reason
		$templater->register('changeusername_reason', $vbphrase['change_username_cooldown']);
		
	}

	if ($vbulletin->userinfo['permissions']['username_numchanges'] AND $lastedit['numchanges'] >= $vbulletin->userinfo['permissions']['username_numchanges'])
	{
		// Don't show editusername
		$show['changeusername'] = false;
		
		// Include reason
		$templater->register('changeusername_reason', $vbphrase['change_username_limit']);
	}

	// Finally render the template
	$customfields['required'] .= $templater->render();
	
	// Re-register this
	$page_templater->register('customfields', $customfields);
}
?>