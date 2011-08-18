<?php
/* For licensing terms, see /license.txt */

$data = preg_replace("/<URL>/si", URL, $data);
$data = preg_replace("/<IMG>/si", URL . "themes/". THEME ."/images/", $data);
