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
		$stm = $this->DB->prepare
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
	$this->id 		= $data['id'];
	$this->level 		= $data['level'];
	$this->name 		= $data['name'];
	$this->groups 		= explode(',', $data['groups']);

	if (time() - $data['lastupdate'] > $this->Settings->getValue('session_refresh'))
		{
		$stm = $this->DB->prepare
			('
			UPDATE
				session
			SET
				lastupdate = ?
			WHERE
				sessionid = ?'
			);
		$stm->bindInteger(time());
		$stm->bindString($this->sessionid);
		$stm->execute();
		$stm->close();
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
	/** nur zur Übergangszeit md5 -> sha1 */
	try
		{
		$stm = $this->DB->prepare
			('
			SELECT
				id
			FROM
				users
			WHERE
				name = ?
				AND password = ?'
			);
		$stm->bindString($name);
		$stm->bindString(md5($password));
		$user_id = $stm->getColumn();
		$stm->close();

		$stm = $this->DB->prepare
			('
			UPDATE
				users
			SET
				new_password = ?
			WHERE
				id = ?'
			);
		$stm->bindString(sha1($password));
		$stm->bindInteger($user_id);
		$stm->execute();
		$stm->close();
		}
	catch (DBNoDataException $e)
		{
		$stm->close();
		}

	/** / nur zur Übergangszeit md5 -> sha1 */

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
					AND new_password = ?'
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
					AND new_password = ?'
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

	$this->sessionid = sha1(uniqid(rand(), true));

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
			lastupdate = ?'
		);
	$stm->bindString($this->sessionid);
	$stm->bindInteger($this->id);
	$stm->bindString($this->name);
	$stm->bindInteger($this->level);
	$stm->bindString(implode(',', $this->groups));
	$stm->bindInteger(time());
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

}

class LoginException extends RuntimeException{

function __construct($message)
	{
	parent::__construct($message, 0);
	}

}

?>
