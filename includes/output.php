<?php
/* For licensing terms, see /license.txt */

$data = ob_get_contents(); # Get all the HTML created by the script
ob_end_clean(); # Erase that data
echo $style->prepare($data); # Prepare and output it