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

	if (!$this->User->isLevel(User::ROOT))
		{
		$this->showFailure('nur root darf das!');
		}

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
		$stm = $this->DB->prepare
			('
			SELECT
				id
			FROM
				boards
			WHERE
				name = ?
			');
		$stm->bindString(htmlspecialchars($this->name));
		$stm->getColumn();
		$stm->close();

		$this->showWarning('Name bereits vergeben!');
		}
	catch (DBNoDataException $e)
		{
		$stm->close();
		}
	}

protected function sendForm()
	{
	$stm = $this->DB->prepare
		('
		INSERT INTO
			boards
		SET
			admin = ?,
			name =  ?,
			regdate = ?'
		);
	$stm->bindInteger($this->User->getId());
	$stm->bindString(htmlspecialchars($this->name));
	$stm->bindInteger(time());
	$stm->execute();
	$stm->close();

	$id = $this->DB->getInsertId();

	/** @TODO: remove hardcoded domain name */
	$stm = $this->DB->prepare
		('
		UPDATE
			boards
		SET
			host = ?
		WHERE
			id = ?'
		);
	$stm->bindString($id'.forum.laber-land.de');
	$stm->bindInteger($id);
	$stm->execute();
	$stm->close();

	$stm = $this->DB->prepare
		('
		INSERT INTO
			cats
		SET
			name = \'Allgemeines\',
			boardid = ?'
		);
	$stm->bindInteger($id);
	$stm->execute();
	$stm->close();

	$cat = $this->DB->getInsertId();

	$stm = $this->DB->prepare
		('
		INSERT INTO
			forum_cat
		SET
			catid = ?,
			forumid = 5,
			position = 1
		');
	$stm->bindInteger($cat);
	$stm->execute();
	$stm->close();

	$stm = $this->DB->prepare
		('
		INSERT INTO
			forum_cat
		SET
			catid = ?,
			forumid = 8,
			position = 2
		');
	$stm->bindInteger($cat);
	$stm->execute();
	$stm->close();

	$stm = $this->DB->prepare
		('
		INSERT INTO
			forum_cat
		SET
			catid = ?,
			forumid = 9,
			position = 3
		');
	$stm->bindInteger($cat);
	$stm->execute();
	$stm->close();

	$stm = $this->DB->prepare
		('
		INSERT INTO
			forum_cat
		SET
			catid = ?,
			forumid = 202,
			position = 4
		');
	$stm->bindInteger($cat);
	$stm->execute();
	$stm->close();

	$stm = $this->DB->prepare
		('
		INSERT INTO
			forum_cat
		SET
			catid = ?,
			forumid = 7,
			position = 5
		');
	$stm->bindInteger($cat);
	$stm->execute();
	$stm->close();

	copy('html/default.html', 'html/'.$id.'.html');
	copy('html/default.css', 'html/'.$id.'.css');


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