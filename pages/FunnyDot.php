<?php

require ('modules/IOutput.php');

class FunnyDot extends Modul implements IOutput{


public function prepare()
	{
	$time = time();

	$this->Io->setCookie('AntiSpamTime', $time);
	$this->Io->setCookie('AntiSpamHash', sha1($time.$this->Settings->getValue('antispam_hash')));
	}

public function show()
	{
	$im = imagecreatetruecolor(1, 1);

	ob_start();

	header('HTTP/1.1 200 OK');
	header("Cache-Control: no-cache, must-revalidate");
	header('Content-Type: image/png');
	header('Content-Length: '.ob_get_length());

	imagepng($im);
	imagedestroy($im);

 	while (ob_get_level() > 0)
 		{
		ob_end_flush();
 		}

	exit;
	}

}


?>