<?php
if (THIS_SCRIPT == 'member')
{
	// Add display to the cache
	$cache = array_merge($cache, array(
		'username_change_member_block',
		'username_change_member_block_bit',
		'username_change_member_css',		
	));
}
if (THIS_SCRIPT == 'profile' AND $_REQUEST['do'] == 'editprofile')
{
	// Profile editing form
	$cache = array_merge($cache, array(
		'username_change_editusername',
	));
}
?>