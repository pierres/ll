<?php


class EditThread extends NewThread{

protected $post 		= 0;
protected $allow_closed 	= false;
protected $allow_deleted 	= false;

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
		$this->showFailure('Thema nicht gefunden oder geschlossen!');
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
		$access = $this->Sql->fetchValue
			('
			SELECT
				userid
			FROM
				posts
			WHERE
				id = '.$this->post
			);
		}
	catch (SqlNoDataException $e)
		{
		$this->showFailure('Kein Beitrag gefunden.');
		}

	try
		{
		$mods = $this->Sql->fetchValue
			('
			SELECT
				mods
			FROM
				forums
			WHERE
				id ='.$this->forum
			);
		}
	catch (SqlNoDataException $e)
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

	$this->sendFile();

	$this->redirect();
	}

protected function sendFile()
	{
	if($this->User->isOnline() && $this->Io->isRequest('addfile'))
		{
		$files = $this->Io->getArray();

		$this->Sql->query
			('
			DELETE FROM
				post_file
			WHERE
				postid = '.$this->post
			);

		$this->Sql->query
			('
			UPDATE
				posts
			SET
				file = 0
			WHERE
				id ='.$this->post
			);

		if (empty($files))
			{
			return;
			}

		$success = false;

		foreach($files as $file => $blubb)
			{
			try
				{
				$this->Sql->fetchValue
					('
					SELECT
						id
					FROM
						files
					WHERE
						id = '.intval($file).'
						AND userid = '.$this->User->getId()
					);
				}
			catch (SqlNoDataException $e)
				{
				continue;
				}

			$this->Sql->query
				('
				INSERT INTO
					post_file
				SET
					postid = '.$this->post.',
					fileid = '.intval($file)
				);

			$success = true;
			}

		if ($success)
			{
			$this->Sql->query
				('
				UPDATE
					posts
				SET
					file = 1
				WHERE
					id ='.$this->post
				);
			}
		}
	}

}

?>