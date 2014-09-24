<?php
// grab default image from cache if possible, otherwise pull from Icom server
$imagefilename = 'product_default.gif';
$im = file_get_contents('http://www.exertismicro-p.co.uk/ImagesPortal/UK/Catalogue/' . $imagefilename);

header("Content-type: image/jpeg");
echo $im;
?>
