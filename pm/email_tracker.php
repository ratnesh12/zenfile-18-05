#!/usr/bin/php -q
<?php

// read from stdin
$fd = fopen("php://stdin", "r");
$email = "";
while (!feof($fd))
{
	$email .= fread($fd, 1024);
}
fclose($fd);
 
 
mail('koshkarev.ss@gmail.com','From my email pipe!','"' . $email . '"');
 
?>