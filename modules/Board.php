<?php


class Board extends Modul{

private $id 		= 1;
private $name 		= '';
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
			/**@TODO: evtl. ein schlechter Fallback */
			$board = $this->getBoard(1);
			}
		}

	$this->name 	= $board['name'];
	$this->id	= $board['id'];
	$this->admin 	= $board['admin'];
	$this->admins 	= $board['admins'];
	$this->mods 	= $board['mods'];
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
			mods
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
			mods
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

}

?>