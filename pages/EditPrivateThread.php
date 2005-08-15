<?php


class EditPrivateThread extends NewPrivateThread{

protected $post 		= 0;

private $db_poll_question 	= '';
private $db_poll_options 	= '';

protected $title 		= 'Thema bearbeiten';


protected function checkInput()
	{
	try
		{
		$data = $this->Sql->fetchRow
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
				AND threads.id = '.$this->Io->getInt('thread').'
			ORDER BY
				posts.dat ASC
			');
		}
	catch (IoException $e)
		{
		$this->showFailure('Kein Thema angegeben!');
		}
	catch (SqlNoDataException $e)
		{
		$this->showFailure('Thema nicht gefunden!');
		}

	try
		{
		$this->poll_question = $this->Sql->fetchValue
			('
			SELECT
				question
			FROM
				polls
			WHERE
				id = '.$this->Io->getInt('thread')
			);

		$this->poll_options = implode("\n", $this->Sql->fetchCol
			('
			SELECT
				value
			FROM
				poll_values
			WHERE
				pollid = '.$this->Io->getInt('thread').'
			ORDER BY
				id ASC
			'));
		}
	catch (SqlNoDataException $e)
		{
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
		$access = $this->Sql->fetchValue
			('
			SELECT
				userid
			FROM
				posts
			WHERE
				id = '.$this->post.'
				AND userid = '.$this->User->getId()
			);
		}
	catch (SqlNoDataException $e)
		{
		$this->showFailure('Kein Beitrag gefunden.');
		}
	}


protected function sendForm()
	{
	$this->Sql->query(
		'
		UPDATE
			threads
		SET
			name = \''.$this->Sql->formatString($this->topic).'\'
		WHERE
			id = '.$this->thread
		);

	if ($this->Io->isRequest('poll_question') && $this->Io->isRequest('poll_options'))
		{
		/** FIXME: Warum schlägt hier ein gewöhnlicher Stringvergleich fehl? */
		if (metaphone($this->poll_options) != metaphone($this->db_poll_options) || metaphone($this->poll_question) != metaphone($this->db_poll_question))
			{
			$this->Sql->query
				('
				DELETE FROM
					polls
				WHERE
					id = '.$this->thread
				);

			$this->Sql->query
				('
				DELETE FROM
					poll_values
				WHERE
					pollid = '.$this->thread
				);

			$this->Sql->query
				('
				DELETE FROM
					poll_voters
				WHERE
					pollid = '.$this->thread
				);

			parent::sendPoll();
			}
		}

	$this->Markup->enableSmilies($this->smilies);
	$this->text = $this->Markup->toHtml($this->text);

	$this->Sql->query
		('
		UPDATE
			posts
		SET
			text = \''.$this->Sql->escapeString($this->text).'\',
			editdate = '.$this->time.',
			editby = '.$this->User->getId().',
			smilies = '.($this->smilies ? 1 : 0).'
		WHERE
			id = '.$this->post
		);

	$this->redirect();
	}

}

?>