<?php


class User extends Modul{


const ROOT		= 3;
const ADMIN		= 2;
const MOD		= 1;

private $sessionid	= 0;
private $id 		= 0;
private $level		= 0;
private $name		= '';
private $groups		= array();


function __construct()
	{
	try
		{
		$sessionid = $this->Io->getHex('sessionid');
		}
	catch (IoRequestException $e)
		{
		return $this->cookieLogin();
		}

	try
		{
		$data = $this->Sql->fetchRow
			('
			SELECT
				id,
				name,
				level,
				groups,
				lastupdate
			FROM
				session
			WHERE
				sessionid = \''.$sessionid.'\''
			);
		}
	catch (SqlNoDataException $e)
		{
		$this->Io->setCookie('sessionid', '');
		return $this->cookieLogin();
		}

	$this->sessionid 	= $sessionid;
	$this->id 		= $data['id'];
	$this->level 		= $data['level'];
	$this->name 		= $data['name'];
	$this->groups 		= explode(',', $data['groups']);

	if (time() - $data['lastupdate'] > Settings::SESSION_REFRESH)
		{
		try
			{
			$this->Sql->query
				('
				UPDATE
					session
				SET
					lastupdate = '.time().'
				WHERE
					sessionid = \''.$this->sessionid.'\''
				);
			}
		catch (SqlException $e)
			{
			$this->logout();
			}
		}
	}

public function getId()
	{
	return $this->id;
	}

public function getName()
	{
	return $this->name;
	}

public function getLevel()
	{
	return $this->id;
	}

public function logout()
	{
	$this->collectGarbage();

	try
		{
		$this->Sql->query
			('
			DELETE FROM
				session
			WHERE
				id = '.$this->id
			);
		}
	catch (SqlException $e)
		{
		}

	$this->Io->setCookie('sessionid', '');
	$this->Io->setCookie('cookieid', '');
	$this->Io->setCookie('cookiepw', '');

	$this->id 	= 0;
	$this->level 	= 0;
	$this->name 	= '';
	$this->groups 	= array();
	}

public function login($name, $password, $cookie = false)
	{
	try
		{
		if ($cookie)
			{
			$data = $this->Sql->fetchRow
				('
				SELECT
					id,
					name,
					level
				FROM
					users
				WHERE
					id = '.$name.'
					AND password = \''.$this->Sql->escapeString($password).'\''
				);
			}
		else
			{
			$data = $this->Sql->fetchRow
				('
				SELECT
					id,
					name,
					level
				FROM
					users
				WHERE
					name = \''.$this->Sql->escapeString($name).'\'
					AND password = \''.md5($password).'\''
				);
			}
		}
	catch (SqlNoDataException $e)
		{
		throw new LoginException('Falsche Benutzername/Passwort-Kombination');
		}

	try
		{
		$groups = $this->Sql->fetchCol
			('
			SELECT
				groupid
			FROM
				user_group
			WHERE
				userid = '.$data['id']
			);
		}
	catch (SqlNoDataException $e)
		{
		$groups = array();
		}

	$this->start($data['id'], $data['name'], $data['level'], $groups);
	}

private function start($id, $name ,$level, $groups)
	{
	$this->collectGarbage();//evtl. könnte man überlegen den Müll öfters zu entfernen

	$this->sessionid = md5(uniqid(rand(), true));

	$this->id 	= $id;
	$this->name 	= $name;
	$this->level 	= $level;
	$this->groups 	= $groups;

	try
		{
		$this->Sql->query
			('
			DELETE FROM
				session
			WHERE
				id ='.$this->id
			);
		}
	catch (SqlException $e)
		{
		}

	$this->Sql->query
		('
		INSERT INTO
			session
		SET
			sessionid = \''.$this->sessionid.'\',
			id = '.$this->id.',
			name = \''.$this->name.'\',
			level = '.$this->level.',
			groups = \''.implode(',', $this->groups).'\',
			lastupdate = '.time()
		);

	$this->Io->setCookie('sessionid', $this->sessionid);
	}

private function collectGarbage()
	{
	try
		{
		$this->Sql->query
			('
			DELETE FROM
				session
			WHERE
				lastupdate <= '.(time() - SETTINGS::SESSION_TIMEOUT)
			);
		}
	catch (SqlException $e)
		{
		}
	}

private function cookieLogin()
	{
	try
		{
		$id = $this->Io->getInt('cookieid');
		$pw = $this->Io->getHex('cookiepw');
		}
	catch (IoRequestException $e)
		{
		return;
		}

	try
		{
		$this->login($id, $pw, true);
		}
	catch (LoginException $e)
		{
		$this->Io->setCookie('cookieid', '');
		$this->Io->setCookie('cookiepw', '');
		}
	}

public function getOnline()
	{
	try
		{
		$result = $this->Sql->fetch
			('
			SELECT
				id,
				name
			FROM
				session
			');
		}
	catch (SqlNoDataException $e)
		{
		$result = array();
		}

	return $result;
	}

public function isGroup($id)
	{
	if (!$this->isOnline() || empty($id))
		{
		return false;
		}

	return (in_array($id, $this->groups));
	}

public function isUser($id)
	{
	if (!$this->isOnline() || empty($id))
		{
		return false;
		}

	return ($this->id == $id);
	}

public function isOnline()
	{
	return ($this->id != 0);
	}


//Testet, ob der Benutzer mindests vom Level $level ist.
public function isLevel($level)
	{
	return ($this->isOnline() && $this->level >= $level);
	}

public function isAdmin()
	{
	return ($this->isLevel(self::ADMIN) || $this->isUser($this->Board->getAdmin()) || $this->isGroup($this->Board->getAdmins()));
	}

public function isMod()
	{
	return ($this->isAdmin() || $this->isLevel(self::MOD) || $this->isGroup($this->Board->getMods()));
	}

}

class LoginException extends WebException{

}

?>
