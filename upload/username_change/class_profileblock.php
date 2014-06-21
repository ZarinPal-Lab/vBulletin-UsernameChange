<?php

if (!isset($GLOBALS['vbulletin']->db))
{
	exit;
}

/**
* Class for Profile Blog Block
*
* @package vBulletin
*/
class vB_ProfileBlock_UsernameHistory extends vB_ProfileBlock
{
	/**
	* The name of the template to be used for the block
	*
	* @var string
	*/
	var $template_name = 'username_change_member_block';

	/**
	* Whether or not the block is enabled
	*
	* @return bool
	*/
	function block_is_enabled()
	{
		// Default to other's profile
		$canview = 'canviewhistory';
		
		if ($this->profile->userinfo['userid'] == $this->registry->userinfo['userid'])
		{
			// Own profile
			$canview = 'canviewownhistory';
		}
		
		return ($this->registry->userinfo['permissions']['usernamechangepermissions'] & $this->registry->bf_ugp_usernamechangepermissions["$canview"]);
	}

	/**
	* Whether to return an empty wrapper if there is no content in the blocks
	*
	* @return bool
	*/
	function confirm_empty_wrap()
	{
		return false;
	}

	/**
	* Should we actually display anything?
	*
	* @return	bool
	*/
	function confirm_display()
	{		
		return !empty($this->block_data['usernamehistory']);
	}

	/**
	* Prepare any data needed for the output
	*
	* @param	string	The id of the block
	* @param	array	Options specific to the block
	*/
	function prepare_output($id = '', $options = array())
	{
		global $show, $vbphrase;
		
		$entries = $this->registry->db->query_read_slave("
			SELECT userchangelog.*, adminuser.username AS admin_username, user.username AS affected_username
			FROM " . TABLE_PREFIX . "userchangelog AS userchangelog
			LEFT JOIN " . TABLE_PREFIX . "user AS user ON(user.userid = userchangelog.userid)
			LEFT JOIN " . TABLE_PREFIX . "user AS adminuser ON(adminuser.userid = userchangelog.adminid)
			WHERE fieldname = 'username'
				AND userchangelog.userid = " . intval($this->profile->userinfo['userid']) . "
			ORDER BY change_time DESC
			LIMIT " . intval($this->registry->userinfo['permissions']['username_historylimit'])
		);
		
		while ($entry = $this->registry->db->fetch_array($entries))
		{
			// Parse this into a readable date
			$entry['change_time'] = vbdate($this->registry->options['timeformat'], $entry['change_time']) . ', ' . vbdate($this->registry->options['dateformat'], $entry['change_time']);
			
			// Create the "bit" template
			$templater = vB_Template::create('username_change_member_block_bit');
				$templater->register('entry', $entry);
				
			// Render it onto the history array
			$this->block_data['usernamehistory'] .= $templater->render();
		}
		
	}
}

?>