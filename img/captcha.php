<?php

/**
 * generates the captcha image 
 */

/*Configblok*/
$colors = [
	'bg'     => [255,255,255],
	'fg'     => [23,62,105],
	'border' => [198,215,235],
	'line'   => [59,109,170,100],
	'junk'   => [59,79,170,100]
];

$width = 240;//breedte afbeelding
$height = 40;//hoogte afbeelding

$rotatie = [-30, 30];

$lines = 3;//aantal gebogen en rechte lijnen (1 is 1 gebogen en 1 rechte, dus 2 totaal)
$noise = 5;//aantal letters in de achtergrond

$fonts = array('arial', 'tahoma', 'trebuc');//voor alle deze fonts moet een tff bestand in de map van dit script staan
$fontsize = 11;

/*vanaf hier niet zomaar bewerken*/
$img = imagecreatetruecolor($width, $height);
foreach($colors as $id=>$color){
	if(count($color) === 3){
		$$id = imagecolorallocate($img, $color[0], $color[1], $color[2]);
	} else {
		$$id = imagecolorallocatealpha($img, $color[0], $color[1], $color[2],$color[3]);
	}
}
imagefill($img,0,0,$bg);

$font = 'fonts/'.$fonts[rand(0,count($fonts)-1)].'.ttf';

/* noise */
$str = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz123456890';
for($i=0;$i<$noise;$i++){
	imagettftext($img, rand($fontsize-1,$fontsize+1),rand($rotatie[0], $rotatie[1]), rand(6,$width-15), rand(15, $height-6), $junk, $font, $str{rand(0, strlen($str)-1)});
}

session_start();
$captcha = $_SESSION[$_GET['id']]['captcha'];
session_write_close();

$characterWidth = $width/strlen($captcha);

for($i=0;$i<strlen($captcha);$i++){
	imagettftext($img, rand($fontsize-1,$fontsize+1), rand($rotatie[0],$rotatie[1]), $characterWidth*$i+rand(6,$characterWidth-15), rand(15,$height-6), $fg, $font, $captcha{$i});
}

/*lijntjes trekken*/
for($i=0;$i<$lines;$i++){
	$y = rand(0,$height-1);
	$y2 = $y+rand(10,20)*rand(-1,1);
	imageline($img, 0,$y,$width,$y2,$line);
	
	$x = (rand(0,1) == 0) ? -1*rand(0,10) : $width+rand(0,10);
	$y = (rand(0,1) == 0) ? -1*rand(0,10) : $height+rand(0,10);
	imagearc($img, $x, $y, 2*rand(100, $width), 2*rand(15, $height), 0,360, $line);
}

imagerectangle($img,0,0,$width-1,$height-1,$border);
header ('Content-type: image/png');
header('Expires: Mon, 26 Jul 1990 05:00:00 GMT');
header('Last-Modified: ' . gmdate('D, d M Y H:i:s') . ' GMT');
header('Cache-Control: no-store, no-cache, must-revalidate');
header('Cache-Control: post-check=0, pre-check=0', false);
header('Pragma: no-cache');

imagepng($img);