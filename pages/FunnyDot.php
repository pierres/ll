<?php
/*
	Copyright 2002-2007 Pierre Schmitz <pschmitz@laber-land.de>

	This file is part of LL.

	LL is free software: you can redistribute it and/or modify
	it under the terms of the GNU General Public License as published by
	the Free Software Foundation, either version 3 of the License, or
	(at your option) any later version.

	LL is distributed in the hope that it will be useful,
	but WITHOUT ANY WARRANTY; without even the implied warranty of
	MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
	GNU General Public License for more details.

	You should have received a copy of the GNU General Public License
	along with LL.  If not, see <http://www.gnu.org/licenses/>.
*/
class FunnyDot extends Modul implements IOutput{


public function prepare()
	{
	$time = time();

	$this->Output->setCookie('AntiSpamTime', $time);
	$this->Output->setCookie('AntiSpamHash', sha1($time.$this->Settings->getValue('antispam_hash')));
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