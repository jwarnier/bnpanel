<?php
//Web Server Status v 1.4, Copyright 2002 By Ryan Schwiebert, visit http://www.schwebdesigns.com/
//This script may be freely distributed providing all copyright headers are kept intact. 

//Concept from:
//Abax Server Status v1.04, Copyright 2002 By Nathan Dickman, visit http://www.NathanDickman.com/
//Location of the live or dead server images
//@author Julio Montoya <gugli100@gmail.com> BeezNest - Fixing the url/port management

//Please change to your server specifications
$live = "../themes/icons/lightbulb.png";
$dead = "../themes/icons/lightbulb_off.png";

//The status checking script
$link = $_GET['link'];
$link_array = parse_url($link);
$domain = $link_array['host'];
//Test the server connection
$churl = @fsockopen($domain, $link_array['port'], $errno, $errstr, 5);
/*var_dump($churl);
var_dump($errstr);
exit;*/
if (!$churl) {    
    header("Location: $dead");
} else {
   header("Location: $live");             
}
exit;