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
class Board extends Modul{

private $id 		= 1;
private $name 		= '';
private $host 		= '';
private $html 		= '';
private $mods		= 0;
private $admin 		= 0;
private $admins 	= 0;


public function __construct()
	{
	try
		{
		$id = $this->Io->getInt('id');

		try
			{
			$board = $this->getBoard($id);
			}
		catch (DBNoDataException $e)
			{
			$board = $this->getBoard(1);
			}
		}
	catch(IoRequestException $e)
		{
		try
			{
			$board = $this->getBoardByHost();
			}
		catch (DBNoDataException $e)
			{
			/** @TODO: evtl. ein schlechter Fallback */
			$board = $this->getBoard(1);
			}
		}

	$this->name 	= $board['name'];
	$this->id	= $board['id'];
	$this->admin 	= $board['admin'];
	$this->admins 	= $board['admins'];
	$this->mods 	= $board['mods'];
	$this->host 	= $board['host'];
	$this->html 	= $board['html'];
	}

private function getBoard($id)
	{
	$stm = $this->DB->prepare
		('
		SELECT
			id,
			name,
			admin,
			admins,
			mods,
			host,
			html
		FROM
			boards
		WHERE
			id = ?'
		);
	$stm->bindInteger($id);

	$board = $stm->getRow();
	$stm->close();
	return $board;
	}

private function getBoardByHost()
	{
	$stm = $this->DB->prepare
		('
		SELECT
			id,
			name,
			admin,
			admins,
			mods,
			host,
			html
		FROM
			boards
		WHERE
			host = ?'
		);
	$stm->bindString($this->Io->getHost());

	$board = $stm->getRow();
	$stm->close();
	return $board;
	}

public function getId()
	{
	return $this->id;
	}

public function getName()
	{
	return $this->name;
	}

public function getAdmin()
	{
	return $this->admin;
	}

public function getAdmins()
	{
	return $this->admins;
	}

public function getMods()
	{
	return $this->mods;
	}

public function getHost()
	{
	return $this->host;
	}

public function getHtml()
	{
	return $this->html;
	}

}

?>