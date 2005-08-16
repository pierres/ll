<?php


class EditPost extends NewPost{

protected $post 		= 0;
protected $allow_deleted 	= false;
protected $allow_closed 	= false;

protected $title 		= 'Beitrag bearbeiten';



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
				threads.forumid
			FROM
				posts,
				threads
			WHERE
				posts.id = '.$this->Io->getInt('post').'
				'.($this->allow_deleted ? '' : 'AND posts.deleted = 0').'
				'.($this->allow_deleted ? '' : 'AND threads.deleted = 0').'
				'.($this->allow_closed ? '' : 'AND threads.closed = 0').'
				AND threads.id = posts.threadid
			');
		}
	catch (IoException $e)
		{
		$this->showFailure('Kein Beitrag angegeben!');
		}
	catch (SqlNoDataException $e)
		{
		$this->showFailure('Beitrag nicht gefunden oder Thema geschlossen!');
		}

	$this->post = $data['id'];
	$this->text =  $this->UnMarkup->fromHtml($data['text']);
	$this->thread = $data['threadid'];
	$this->forum = $data['forumid'];
	$this->smilies = ($data['smilies'] == 0 ? false : true);

	$this->addHidden('post', $this->post);
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

	$this->sendFile($this->post);

	$this->redirect();
	}

protected function sendFile($postid)
	{
	if($this->User->isOnline() && $this->Io->isRequest('addfile'))
		{
		$this->Sql->query
			('
			DELETE FROM
				post_file
			WHERE
				postid = '.$postid
			);

		$this->Sql->query
			('
			UPDATE
				posts
			SET
				file = 0
			WHERE
				id ='.$postid
			);

		parent::sendFile($postid);
		}
	}

}

?>