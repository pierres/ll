<?php


class EditPrivateThread extends NewPrivateThread{

protected $post 			= 0;

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
				threads.name
			FROM
				posts,
				threads
			WHERE
				threads.id = posts.threadid
				AND threads.id = ?
			ORDER BY
				posts.dat ASC
			');
		$stm->bindInteger($this->Io->getInt('thread'));
		$data = $stm->getRow();
		$stm->close();
		}
	catch (IoException $e)
		{
		$stm->close();
		$this->showFailure('Kein Thema angegeben!');
		}
	catch (DBNoDataException $e)
		{
		$stm->close();
		$this->showFailure('Thema nicht gefunden!');
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
		$stm->close();

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
		$stm->close();
		}
	catch (DBNoDataException $e)
		{
		$stm->close();
		}

	$this->post = $data['id'];
	$this->text =  $this->UnMarkup->fromHtml($data['text']);
	$this->thread = $data['threadid'];

	$this->topic = unhtmlspecialchars($data['name']);
	$this->smilies = ($data['smilies'] == 0 ? false : true);


	$this->db_poll_question = $this->poll_question;
	$this->db_poll_options = $this->poll_options;

	$this->addHidden('thread', $this->thread);

	parent::checkInput();
	}

protected function addRecipients()
	{
	}

protected function checkRecipients()
	{
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
				id = ?
				AND userid = ?'
			);
		$stm->bindInteger($this->post);
		$stm->bindInteger($this->User->getId());
		$access = $stm->getColumn();
		$stm->close();
		}
	catch (DBNoDataException $e)
		{
		$stm->close();
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
	$stm->close();

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
			$stm->close();

			$stm = $this->DB->prepare
				('
				DELETE FROM
					poll_values
				WHERE
					pollid = ?'
				);
			$stm->bindInteger($this->thread);
			$stm->execute();
			$stm->close();

			$stm = $this->DB->prepare
				('
				DELETE FROM
					poll_voters
				WHERE
					pollid = ?'
				);
			$stm->bindInteger($this->thread);
			$stm->execute();
			$stm->close();

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
	$stm->close();

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
		$stm->close();

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
		$stm->close();

		parent::sendFile($postid);
		}
	}

}

?>