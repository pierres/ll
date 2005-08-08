<?php


class Board extends Modul{

private $id 	= 1;
private $name 	= '';
private $mods	= 0;
private $admin 	= 0;
private $admins = 0;


public function __construct()
	{
	try
		{
		$id = $this->Io->getInt('id');
		}
	catch(IoRequestException $e)
		{
		$id = 1;
		}

	try
		{
		$board = $this->getBoard($id);
		}
	catch (SqlNoDataException $e)
		{
		$board = $this->getBoard(1);
		}

	$this->name 	= htmlspecialchars($board['name']);
	$this->id	= $board['id'];
	$this->admin 	= $board['admin'];
	$this->admins 	= $board['admins'];
	$this->mods 	= $board['mods'];
	}

private function getBoard($id)
	{
	return $this->Sql->fetchRow
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
			id = '.$id
		);
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