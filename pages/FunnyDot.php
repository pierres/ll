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
	header("Cache-Control: no-cache, must-revalidate");
	header('Content-type: image/png');
	$im = imagecreatetruecolor(1, 1);
	imagepng($im);
	imagedestroy($im);
	}

}


?>