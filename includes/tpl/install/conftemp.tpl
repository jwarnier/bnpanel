<?php
/* For licensing terms, see /license.txt */

//Are we being called by the script?
if(THT != 1){die("FATAL: Trying to hack?");}

//MAIN SQL CONFIG - Change values accordingly
$sql['host']	= "%HOST%"; #The mySQL Host, usually default - localhost
$sql['user']	= "%USER%"; #The mySQL Username
$sql['pass']	= "%PASS%"; #The mySQL Password
$sql['db']		= "%DB%"; #The mySQL DB, remember to have your username prefix
$sql['pre'] 	= "%PRE%"; #The mySQL Prefix, usually default unless otherwise

//LEAVE
$sql['install'] = %TRUE%;	//Determines if the system is installed or not
$sql['upgrade'] = %UPGRADE%; // set to true to update the system
?>