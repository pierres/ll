<?php


abstract class Page extends Modul{

protected $variables = array();


public function __construct()
	{
	$this->variables['body']	 = '';
	$this->variables['title']	 = '';
	}

protected function makeMenu()
	{
	$menu =	'<a href="?page=Forums;id='.$this->Board->getId().'"><span class="button" id="start">Übersicht</span></a> <a href="?page=Search;id='.$this->Board->getId().'"><span class="button" id="search">Suche</span></a> <a href="?page=Recent;id='.$this->Board->getId().'"><span class="button" id="recent">Aktuelles</span></a>';

	if ($this->User->isOnline())
		{
		$menu .=
			' <a href="?page=MyProfile;id='.$this->Board->getId().'"><span class="button" id="myprofile">Mein Profil</span></a> <a href="?page=UserRecent;id='.$this->Board->getId().'"><span class="button" id="userrecent">Meine Favoriten</span></a> <a href="?page=Logout;id='.$this->Board->getId().'"><span class="button" id="logout">Abmelden</span></a>';

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

protected function debug()
	{
	global $start;

	$modules = implode(', ', array_keys(self::$modules));
	$time = substr((mTime()-$start), 0, 7);

	return <<<eot
<div style="clear:left;text-align:center;">
<pre style="border:1px solid;width:500px;text-align:left;">
DB-Verbindungen: 	{$this->Sql->connects}
DB-Abfragen: 		{$this->Sql->queries}
Aktive Module:		{$modules}
Ausführungsdauer:	{$time}s
</pre>
</div>
eot;
	}

private function getWebring()
	{
	$boards = $this->Sql->fetch
		('
		SELECT
			id,
			name
		FROM
			boards
		ORDER BY
			id ASC
		');

	$menu = <<<eot
<script type="text/javascript">
document.write("<form action=\"\"><div>	<select name=\"link\" onchange=\"location.href='?page=Forums;id='+this.form.link.options[this.form.link.selectedIndex].value\">
eot;

	foreach ($boards as $board)
		{
		$selected = ($this->Board->getId() == $board['id'] ? ' selected=\"selected\"': '');
		$menu .= '<option value=\"'.$board['id'].'\"'.$selected.'>'.$board['name'].'<\/option>';
		}

	return $menu.'<\/select><\/div><\/form>");</script>';
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

// 	if ($this->User->isLevel(User::ROOT))
// 		{
// 		$this->setValue('body', $this->getValue('body').$this->debug());
// 		}

	$this->setValue('body', $this->getValue('body').'<div style="text-align:right;font-size:8px;"><a href="?page=Impressum">Impressum</a></div>');

	foreach ($this->variables as $key => $value)
		{
		$file = str_replace('<!-- '.$key.' -->', $value, $file);
		}

	$this->Io->out($file);
	}
}

?>