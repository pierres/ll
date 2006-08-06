<?php

require (PATH.'modules/DB.php');
require (PATH.'modules/User.php');
require (PATH.'modules/Board.php');
require (PATH.'modules/IOutput.php');

abstract class Page extends Modul implements IOutput{

protected $variables = array();


public function __construct()
	{
	self::__set('DB', new DB(
		$this->Settings->getValue('sql_user'),
		$this->Settings->getValue('sql_password'),
		$this->Settings->getValue('sql_database')
		));

	self::__set('Board', new Board());
	self::__set('User', new User());

	$this->variables['body']	 = '';
	$this->variables['title']	 = '';
	$this->variables['meta.robots']	 = 'index,follow';
	}

protected function makeMenu()
	{
	$menu =	'<a href="?page=Forums;id='.$this->Board->getId().'"><span class="button" id="start">Übersicht</span></a> <a href="?page=Search;id='.$this->Board->getId().'"><span class="button" id="search">Suche</span></a> <a href="?page=Recent;id='.$this->Board->getId().'"><span class="button" id="recent">Aktuelles</span></a> <a href="?page=UserList;id='.$this->Board->getId().'"><span class="button" id="userlist">Benutzerliste</span></a>';

	if ($this->User->isOnline())
		{
		$menu .=
			' <a href="?page=MyProfile;id='.$this->Board->getId().'"><span class="button" id="myprofile">Mein Profil</span></a> <a href="?page=Logout;id='.$this->Board->getId().'"><span class="button" id="logout">Abmelden</span></a>';

		if ($this->User->isAdmin())
			{
			$menu .=
			' <a href="?page=AdminIndex;id='.$this->Board->getId().'"><span class="button" id="admin">Administration</span></a>';
			}

		}
	else
		{
		$menu .=
			' <a href="?page=Register;id='.$this->Board->getId().'"><span class="button" id="register">Registrieren</span></a> <a href="?page=Login;id='.$this->Board->getId().'"><span class="button" id="login">Anmelden</span></a>';
		}

	return $menu;
	}

public function setValue($key, $value)
	{
	$this->variables[$key] = $value;
	}

public function getValue($key)
	{
	return $this->variables[$key];
	}

public function showWarning($text)
	{
	$this->setValue('title', 'Warnung');
	$this->setValue('body', '<div class="warning">'.$text.'</div>');
	$this->show();
	}

private function getWebring()
	{
	try
		{
		$boards = $this->DB->getRowSet
			('
			SELECT
				id,
				name
			FROM
				boards
			ORDER BY
				id ASC
			');
		}
	catch (DBNoDataException $e)
		{
		$boards = array();
		}

	$menu = <<<eot
<script type="text/javascript">
			<!--
			document.write("<form action=\"\"><select name=\"link\" onchange=\"location.href='?page=Forums;id='+this.form.link.options[this.form.link.selectedIndex].value\">
eot;

	foreach ($boards as $board)
		{
		$selected = ($this->Board->getId() == $board['id'] ? ' selected=\"selected\"': '');
		$menu .= '<option value=\"'.$board['id'].'\"'.$selected.'>'.$board['name'].'<\/option>';
		}

	return $menu.'<\/select><\/form>");
			-->
		</script>';
	}

public function prepare()
	{
	$this->setValue('title', 'Warnung');
	$this->setValue('body', 'kein Text');
	}

public function show()
	{
	$file = file_get_contents(PATH.'html/'.$this->Board->getId().'.html');

	$this->variables['id'] = $this->Board->getId();
	$this->variables['name'] = $this->Board->getName();
	$this->variables['menu'] = $this->makeMenu();

	if ($this->User->isOnline())
		{
		$this->variables['user'] = $this->User->getName();
		}

	$this->setValue('webring', $this->getWebring());

	$this->setValue('body', $this->getValue('body').'<div style="text-align:right;font-size:8px;"><a href="?page=Impressum;id='.$this->Board->getId().'">Impressum</a></div>');

	foreach ($this->variables as $key => $value)
		{
		$file = str_replace('<!-- '.$key.' -->', $value, $file);
		}

	$this->Io->out($file);
	}
}

?>