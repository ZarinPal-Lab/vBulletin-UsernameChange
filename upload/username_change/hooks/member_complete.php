<?php
eval('$template_hook[\'profile_left_last\'] .= $blocks[\'usernamehistory\'];');
$headinclude .= vB_Template::create('username_change_member_css')->render();
?>