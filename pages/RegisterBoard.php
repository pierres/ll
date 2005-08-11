<?php

class RegisterBoard extends Form{

private $name = '';


protected function setForm()
	{
	if(!$this->User->isOnline())
		{
		$this->Io->redirect('Login');
		}

	$this->setValue('title', 'Eigenes Forum einrichten');

	$this->addSubmit('Registrieren');

	$this->addText('name', 'Der Name des Forums', '', 25);
	$this->requires('name');
	$this->setLength('name', 3, 25);
	}

protected function checkForm()
	{
	$this->name = $this->Io->getString('name');

	try
		{
		 $this->Sql->fetchValue
			('
			SELECT
				id
			FROM
				boards
			WHERE
				name = \''.$this->Sql->formatString($this->name).'\'
			');

		$this->showWarning('Name bereits vergeben!');
		}
	catch (SqlNoDataException $e)
		{
		}
	}

protected function sendForm()
	{
	$this->Sql->query
		('
		INSERT INTO
			boards
		SET
			admin = '.$this->User->getId().',
			name =  \''.$this->Sql->formatString($this->name).'\',
			regdate = '.time()
		);

	$id = $this->Sql->fetchValue('SELECT LAST_INSERT_ID()');

	$this->Sql->query
		('
		INSERT INTO
			cats
		SET
			name = \'Allgemeines\',
			boardid ='. $id
		);

	$cat = $this->Sql->fetchValue('SELECT LAST_INSERT_ID()');

	$this->Sql->query
		('
		INSERT INTO
			forum_cat
		SET
			catid = '.$cat.',
			forumid = 5,
			position = 1
		');

	$this->Sql->query
		('
		INSERT INTO
			forum_cat
		SET
			catid = '.$cat.',
			forumid = 8,
			position = 2
		');

	$this->Sql->query
		('
		INSERT INTO
			forum_cat
		SET
			catid = '.$cat.',
			forumid = 9,
			position = 3
		');

	$this->Sql->query
		('
		INSERT INTO
			forum_cat
		SET
			catid = '.$cat.',
			forumid = 202,
			position = 4
		');

	$this->Sql->query
		('
		INSERT INTO
			forum_cat
		SET
			catid = '.$cat.',
			forumid = 7,
			position = 5
		');

	copy(PATH.'/html/default.html', PATH.'/html/'.$id.'.html');
	copy(PATH.'/html/default.css', PATH.'/html/'.$id.'.css');
	copy(PATH.'/html/default.js', PATH.'/html/'.$id.'.js');


	$body =
		'
		<table class="frame">
			<tr>
				<td class="title">
					Registrierung erfolgreich
				</td>
			</tr>
			<tr>
				<td class="main">
					Dein Forum wurde eingerichtet und ist unter folgender Adresse erreichbar:
					<ul>
					<li><strong>Forum:</strong> <a href="?page=Forums;id='.$id.'">http://www.laber-land.de/?page=Forums;id='.$id.'</a></li>
					<li><strong>Administration:</strong> <a href="?page=AdminIndex;id='.$id.'">http://www.laber-land.de/?page=AdminIndex;id='.$id.'</a></li>
					</ul>
				</td>
			</tr>
		</table>
		';

	$this->setValue('title', 'Forum erfolgreich eingerichtet');
	$this->setValue('body', $body);
	}


}


?>