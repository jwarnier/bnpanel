<?php

//echo $style->prepare($data); # Prepare and output it

//Memory usage
if (SERVER_STATUS == 'test') {
	echo ('MemoryUsage').': '.number_format((memory_get_usage()/1048576), 3, '.', '') .'Mb' ;
	echo '<br />';
	echo ('MemoryUsagePeak').': '.number_format((memory_get_peak_usage()/1048576), 3, '.', '').'Mb';
	$mtime = microtime();
	$mtime = explode(" ",$mtime);
	$mtime = $mtime[1] + $mtime[0];
	$endtime = $mtime;
	$totaltime = ($endtime - $starttime);
	echo '<br />'.$totaltime = number_format(($totaltime), 4, '.', '');
}