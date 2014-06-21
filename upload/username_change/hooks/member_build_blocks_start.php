<?php
require_once(DIR . '/username_change/class_profileblock.php');

$blocklist['usernamehistory'] = array(
	'class' => 'UsernameHistory',
	'title' => $vbphrase['username_history'],
	'hook_location' => 'profile_tabs_last',
	'wrap' => false,
);
?>