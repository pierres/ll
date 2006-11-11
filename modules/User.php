<?php


class User extends Modul{


const ROOT		= 3;
const ADMIN		= 2;
const MOD		= 1;

private $sessionid	= '';
private $securityToken	= '';
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
		$stm = $this->DB->prepare
			('
			SELECT
				id,
				name,
				level,
				groups,
				lastupdate,
				security_token
			FROM
				session
			WHERE
				sessionid = ?'
			);
		$stm->bindString($sessionid);
		$data = $stm->getRow();
		$stm->close();
		}
	catch (DBNoDataException $e)
		{
		$stm->close();
		$this->Io->setCookie('sessionid', '');
		return $this->cookieLogin();
		}

	$this->sessionid 	= $sessionid;
	$this->securityToken 	= $data['security_token'];
	$this->id 		= $data['id'];
	$this->level 		= $data['level'];
	$this->name 		= $data['name'];
	$this->groups 		= explode(',', $data['groups']);

	if (time() - $data['lastupdate'] > $this->Settings->getValue('session_refresh'))
		{
		$this->updateSession();
		}
	}

private function updateSession()
	{
	$this->securityToken = $this->getRandomHash();
	$stm = $this->DB->prepare
		('
		UPDATE
			session
		SET
			lastupdate = ?,
			security_token = ?
		WHERE
			sessionid = ?'
		);
	$stm->bindInteger(time());
	$stm->bindString($this->securityToken);
	$stm->bindString($this->sessionid);
	$stm->execute();
	$stm->close();
	}

private function getRandomHash()
	{
	return sha1(uniqid(rand(), true));
	}

public function getSecurityToken()
	{
	return $this->securityToken;
	}

public function getNextSecurityToken()
	{
	$this->updateSession();
	return $this->securityToken;
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

	$stm = $this->DB->prepare
		('
		DELETE FROM
			session
		WHERE
			id = ?'
		);
	$stm->bindInteger($this->id);
	$stm->execute();
	$stm->close();

	$this->Io->setCookie('sessionid', '');
	$this->Io->setCookie('cookieid', '');
	$this->Io->setCookie('cookiepw', '');

	$this->id 	= 0;
	$this->level 	= 0;
	$this->name 	= '';
	$this->groups 	= array();
	}
/** TODO: Methode sollte gesplittet werden */
public function login($name, $password, $cookie = false)
	{
	try
		{
		if ($cookie)
			{
			$stm = $this->DB->prepare
				('
				SELECT
					id,
					name,
					level
				FROM
					users
				WHERE
					id = ?
					AND password = ?'
				);
			$stm->bindInteger($name);
			$stm->bindString($password);
			}
		else
			{
			$stm = $this->DB->prepare
				('
				SELECT
					id,
					name,
					level
				FROM
					users
				WHERE
					name = ?
					AND password = ?'
				);
			$stm->bindString($name);
			$stm->bindString(sha1($password));
			}

		$data = $stm->getRow();
		$stm->close();
		}
	catch (DBNoDataException $e)
		{
		$stm->close();
		throw new LoginException('Falsche Benutzername/Passwort-Kombination');
		}

	$gruopArray = array();

	try
		{
		$stm = $this->DB->prepare
			('
			SELECT
				groupid
			FROM
				user_group
			WHERE
				userid = ?'
			);
		$stm->bindInteger($data['id']);

		foreach ($stm->getColumnSet() as $group)
			{
			$gruopArray[] = $group;
			}
		$stm->close();
		}
	catch (DBNoDataException $e)
		{
		$stm->close();
		}

	$this->start($data['id'], $data['name'], $data['level'], $gruopArray);
	}

private function start($id, $name ,$level, $groups)
	{
	$this->collectGarbage();//evtl. könnte man überlegen den Müll öfters zu entfernen

	$this->sessionid = $this->getRandomHash();
	$this->securityToken = $this->getRandomHash();

	$this->id 	= $id;
	$this->name 	= $name;
	$this->level 	= $level;
	$this->groups 	= $groups;

	$stm = $this->DB->prepare
		('
		DELETE FROM
			session
		WHERE
			id = ?'
		);
	$stm->bindInteger($this->id);
	$stm->execute();
	$stm->close();

	$stm = $this->DB->prepare
		('
		INSERT INTO
			session
		SET
			sessionid = ?,
			id = ?,
			name = ?,
			level = ?,
			groups = ?,
			lastupdate = ?,
			security_token = ?'
		);
	$stm->bindString($this->sessionid);
	$stm->bindInteger($this->id);
	$stm->bindString($this->name);
	$stm->bindInteger($this->level);
	$stm->bindString(implode(',', $this->groups));
	$stm->bindInteger(time());
	$stm->bindString($this->securityToken);
	$stm->execute();
	$stm->close();

	$this->Io->setCookie('sessionid', $this->sessionid);

	$stm = $this->DB->prepare
		('
		UPDATE
			users
		SET
			lastlogin = ?
		WHERE
			id = ?
		');
	$stm->bindInteger(time());
	$stm->bindInteger($this->id);
	$stm->execute();
	$stm->close();
	}

private function collectGarbage()
	{
	$stm = $this->DB->prepare
		('
		DELETE FROM
			session
		WHERE
			lastupdate <= ?'
		);
	$stm->bindInteger(time() - $this->Settings->getValue('session_timeout'));
	$stm->execute();
	$stm->close();
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
		return $this->DB->getRowSet
			('
			SELECT
				id,
				name
			FROM
				session
			');
		}
	catch (DBNoDataException $e)
		{
		return array();
		}
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

public function isForumMod($forumid)
	{
	$isMod = false;

	if ($this->isMod())
		{
		$isMod = true;
		}
	else
		{
		try
			{
			$stm = $this->DB->prepare
				('
				SELECT
					mods
				FROM
					forums
				WHERE
					id = ?'
				);
			$stm->bindInteger($forumid);
			$mods = $stm->getColumn();
			$stm->close();

			if ($this->isGroup($mods))
				{
				$isMod = true;
				}
			}
		catch (DBNoDataException $e)
			{
			$stm->close();
			}
		}

	return $isMod;
	}

}

class LoginException extends RuntimeException{

function __construct($message)
	{
	parent::__construct($message, 0);
	}

}

?>
