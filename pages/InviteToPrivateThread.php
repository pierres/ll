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
		$stm = $this->DB->prepare
			('
			SELECT
				userid
			FROM
				thread_user
			WHERE
				threadid = ?
				AND userid = ?'
			);
		$stm->bindInteger($this->thread);
		$stm->bindInteger($this->User->getId());
		$stm->getColumn();
		}
	catch (Exception $e)
		{
		$this->showFailure('Thema nicht gefunden');
		}

	$stm = $this->DB->prepare
		('
		SELECT
			users.id,
			users.name
		FROM
			users,
			thread_user
		WHERE
			thread_user.threadid = ?
			AND thread_user.userid = users.id
		');
	$stm->bindInteger($this->thread);

	$users = array();
	foreach ($stm->getRowSet() as $recipient)
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
		catch(DBNoDataException $e)
			{
			$this->showWarning('Empfänger "'.htmlspecialchars($recipient).'" ist unbekannt.');
			}
		}
	}

protected function sendForm()
	{
	foreach ($this->newto as $user)
		{
		$stm = $this->DB->prepare
			('
			INSERT INTO
				thread_user
			SET
				threadid = ?,
				userid = ?'
			);
		$stm->bindInteger($this->thread);
		$stm->bindInteger($user);
		$stm->execute();
		}

	$this->Io->redirect('PrivatePostings', 'thread='.$this->thread);
	}

}

?>