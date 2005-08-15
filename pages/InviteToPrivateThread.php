<?php


class InviteToPrivateThread extends Form{

private $thread = 0;
private $newto	= array();
private $oldto	= array();

protected function setForm()
	{
	$this->setValue('title', 'Mitglieder einladen');

	if (!$this->User->isOnline())
		{
		$this->showFailure('nur für Mitglieder');
		}

	try
		{
		$this->thread = $this->Io->getInt('thread');
		$this->Sql->fetchValue
			('
			SELECT
				userid
			FROM
				thread_user
			WHERE
				threadid = '.$this->thread.'
				AND userid = '.$this->User->getId()
			);
		}
	catch (Exception $e)
		{
		$this->showFailure('Thema nicht gefunden');
		}

	$recipients = $this->Sql->fetch
		('
		SELECT
			users.id,
			users.name
		FROM
			users,
			thread_user
		WHERE
			thread_user.threadid ='.$this->thread.'
			AND thread_user.userid = users.id
		');

	$users = array();
	foreach ($recipients as $recipient)
		{
		$this->oldto[] = $recipient['id'];

		$users[] = '<a href="?page=ShowUser;id='.$this->Board->getId().';user='.$recipient['id'].'">'.$recipient['name'].'</a>';
		}

	$this->addOutput('Schon dabei: '.implode(', ', $users).'<br /><br />');

	$this->addSubmit('Hinzufügen');
	$this->addHidden('thread', $this->thread);
	$this->addText('recipients', 'Neue Empfänger');
	}

protected function checkForm()
	{
	if ($this->Io->isRequest('recipients'))
		{
		$recipients = array_map('trim', explode(',', $this->Io->getString('recipients')));

		try
			{
			foreach ($recipients as $recipient)
				{
				$user = AdminFunctions::getUserId($recipient);
				if (!in_array($user, $this->oldto) && !in_array($user, $this->newto))
					{
					$this->newto[] = $user;
					}
				}
			}
		catch(SqlNoDataException $e)
			{
			$this->showWarning('Empfänger "'.htmlspecialchars($recipient).'" ist unbekannt.');
			}
		}
	}

protected function sendForm()
	{
	foreach ($this->newto as $user)
		{
		$this->Sql->query
			('
			INSERT INTO
				thread_user
			SET
				threadid = '.$this->thread.',
				userid = '.$user
			);
		}

	$this->Io->redirect('InviteToPrivateThread', 'thread='.$this->thread);
	}

}

?>