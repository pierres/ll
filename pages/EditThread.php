<?php


class EditThread extends NewThread{

protected $post 			= 0;
protected $allow_closed 	= false;
protected $allow_deleted 	= false;
protected $thread		= 0;

private $db_poll_question 	= '';
private $db_poll_options 	= '';

protected $title 			= 'Thema bearbeiten';


protected function checkInput()
	{
	try
		{
		$stm = $this->DB->prepare
			('
			SELECT
				posts.id,
				posts.text,
				posts.threadid,
				posts.smilies,
				threads.forumid,
				threads.name
			FROM
				posts,
				threads
			WHERE
				threads.id = posts.threadid
				'.($this->allow_deleted ? '' : 'AND posts.deleted = 0').'
				'.($this->allow_deleted ? '' : 'AND threads.deleted = 0').'
				'.($this->allow_closed ? '' : 'AND threads.closed = 0').'
				AND threads.id = ?
			ORDER BY
				posts.dat ASC
			');
		$this->thread = $this->Io->getInt('thread');
		$stm->bindInteger($this->thread);
		$data = $stm->getRow();
		}
	catch (IoException $e)
		{
		$this->showFailure('Kein Thema angegeben!');
		}
	catch (DBNoDataException $e)
		{
		$this->showFailure('Thema nicht gefunden oder geschlossen!');
		}

	try
		{
		$stm = $this->DB->prepare
			('
			SELECT
				question
			FROM
				polls
			WHERE
				id = ?'
			);
		$stm->bindInteger($this->Io->getInt('thread'));
		$this->poll_question = $stm->getColumn();

		$stm = $this->DB->prepare
			('
			SELECT
				value
			FROM
				poll_values
			WHERE
				pollid = ?
			ORDER BY
				id ASC
			');
		$stm->bindInteger($this->Io->getInt('thread'));
		foreach($stm->getColumnSet() as $poll_option)
			{
			$this->poll_options .= $poll_option."\n";
			}
		}
	catch (DBNoDataException $e)
		{
		}

	$this->post = $data['id'];
	$this->text =  $this->UnMarkup->fromHtml($data['text']);
	$this->thread = $data['threadid'];
	$this->forum = $data['forumid'];
	$this->topic = unhtmlspecialchars($data['name']);
	$this->smilies = ($data['smilies'] == 0 ? false : true);


	$this->db_poll_question = $this->poll_question;
	$this->db_poll_options = $this->poll_options;

	$this->addHidden('thread', $this->thread);

	parent::checkInput();
	}

protected function checkAccess()
	{
	parent::checkAccess();

	try
		{
		$stm = $this->DB->prepare
			('
			SELECT
				userid
			FROM
				posts
			WHERE
				id = ?'
			);
		$stm->bindInteger($this->post);
		$access = $stm->getColumn();
		}
	catch (DBNoDataException $e)
		{
		$this->showFailure('Kein Beitrag gefunden.');
		}

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
		$stm->bindInteger($this->forum);
		$mods = $stm->getColumn();
		}
	catch (DBNoDataException $e)
		{
		$mods = 0;
		}

	if (!$this->User->isUser($access) && !$this->User->isMod() && !$this->User->isGroup($mods))
		{
		// Tun wir so, als wüssten wir von nichts
		$this->showFailure('Kein Beitrag gefunden.');
		}
	}

protected function sendForm()
	{
	$stm = $this->DB->prepare(
		'
		UPDATE
			threads
		SET
			name = ?
		WHERE
			id = ?'
		);
	$stm->bindString(htmlspecialchars($this->topic));
	$stm->bindInteger($this->thread);
	$stm->execute();

	if ($this->Io->isRequest('poll_question') && $this->Io->isRequest('poll_options'))
		{
		/** FIXME: Warum schlägt hier ein gewöhnlicher Stringvergleich fehl? */
// 		if (metaphone($this->poll_options) != metaphone($this->db_poll_options) || metaphone($this->poll_question) != metaphone($this->db_poll_question))
		if ($this->poll_options != $this->db_poll_options || $this->poll_question != $this->db_poll_question)
			{
			$stm = $this->DB->prepare
				('
				DELETE FROM
					polls
				WHERE
					id = ?'
				);
			$stm->bindInteger($this->thread);
			$stm->execute();

			$stm = $this->DB->prepare
				('
				DELETE FROM
					poll_values
				WHERE
					pollid = ?'
				);
			$stm->bindInteger($this->thread);
			$stm->execute();

			$stm = $this->DB->prepare
				('
				DELETE FROM
					poll_voters
				WHERE
					pollid = ?'
				);
			$stm->bindInteger($this->thread);
			$stm->execute();

			parent::sendPoll();
			}
		}

	$stm = $this->DB->prepare
		('
		UPDATE
			posts
		SET
			text = ?,
			editdate = ?,
			editby = ?,
			smilies = ?
		WHERE
			id = ?'
		);
	$stm->bindString($this->text);
	$stm->bindInteger($this->time);
	$stm->bindInteger($this->User->getId());
	$stm->bindInteger($this->smilies ? 1 : 0);
	$stm->bindInteger($this->post);
	$stm->execute();

	$this->sendFile($this->post);
	$this->sendThreadSummary();

	$this->redirect();
	}

protected function sendFile($postid)
	{
	if($this->User->isOnline() && $this->Io->isRequest('addfile'))
		{
		$stm = $this->DB->prepare
			('
			DELETE FROM
				post_attachments
			WHERE
				postid = ?'
			);
		$stm->bindInteger($postid);
		$stm->execute();

		$stm = $this->DB->prepare
			('
			UPDATE
				posts
			SET
				file = 0
			WHERE
				id = ?'
			);
		$stm->bindInteger($postid);
		$stm->execute();

		parent::sendFile($postid);
		}
	}

}

?>