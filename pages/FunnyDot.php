<?php

require (PATH.'modules/IOutput.php');

class FunnyDot extends Modul implements IOutput{


public function prepare()
	{
	$time = time();
	$expireTime = $time + $this->Settings->getValue('antispam_timeout');

	$this->Io->setCookie('AntiSpamTime', $time, $expireTime);
	$this->Io->setCookie('AntiSpamHash', sha1($time.$this->Settings->getValue('antispam_hash')), $expireTime);
	}

public function show()
	{
	header("Cache-Control: no-cache, must-revalidate");
	header('Content-type: image/png');
	$im = imagecreatetruecolor(1, 1);
	$black = imagecolorallocate($im, 0, 0, 0);
	imagecolortransparent($im, $black);
	imagepng($im);
	imagedestroy($im);
	exit();
	}

}


?>