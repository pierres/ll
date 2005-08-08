<?php


abstract class AdminForm extends Form{


public function __construct()
	{
	AdminPage::__construct();
	}

protected function makeMenu()
	{
	$menu =	'<a href="?page=Forums;id='.$this->Board->getId().'"><span class="button" id="start">Übersicht</span></a>
	<a href="?page=AdminSettings;id='.$this->Board->getId().'"><span class="button" id="settings">Einstellungen</span></a>
	<a href="?page=AdminCats;id='.$this->Board->getId().'"><span class="button">Kategorien &amp; Foren</span></a>
	<a href="?page=AdminDesign;id='.$this->Board->getId().'"><span class="button">Layout &amp; Design</span></a>
	<a href="?page=Logout;id='.$this->Board->getId().'"><span class="button" id="logout">Abmelden</span></a>';

	return $menu;
	}

}


?>