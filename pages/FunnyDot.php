<?php

class FunnyDot extends Modul implements IOutput{


public function prepare()
	{
	$time = time();

	$this->Io->setCookie('AntiSpamTime', $time);
	$this->Io->setCookie('AntiSpamHash', sha1($time.$this->Settings->getValue('antispam_hash')));
	}

public function show()
	{
	header('HTTP/1.1 200 OK');
	header("Cache-Control: no-cache, must-revalidate");
	header('Content-Type: image/png');
	header('Content-Length: 69');

	/** transparent png (1px*1px) */
	echo base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAIAAACQd1PeAAAADElEQVQImWNgYGAAAAAEAAGjChXjAAAAAElFTkSuQmCC');

	exit;
	}

}


?>