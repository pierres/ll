<?php


class EditPost extends NewPost{

protected $post 			= 0;
protected $allow_deleted 	= false;
protected $allow_closed 	= false;

protected $title 			= 'Beitrag bearbeiten';



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
				threads.forumid
			FROM
				posts,
				threads
			WHERE
				posts.id = ?
				'.($this->allow_deleted ? '' : 'AND posts.deleted = 0').'
				'.($this->allow_deleted ? '' : 'AND threads.deleted = 0').'
				'.($this->allow_closed ? '' : 'AND threads.closed = 0').'
				AND threads.id = posts.threadid
			');
		$stm->bindInteger($this->Io->getInt('post'));
		$data = $stm->getRow();
		$stm->close();
		}
	catch (IoException $e)
		{
		$stm->close();
		$this->showFailure('Kein Beitrag angegeben!');
		}
	catch (DBNoDataException $e)
		{
		$stm->close();
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
		$stm->close();
		}
	catch (DBNoDataException $e)
		{
		$stm->close();
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
				id =?'
			);
		$stm->bindInteger($this->forum);
		$mods = $stm->getColumn();
		$stm->close();
		}
	catch (DBNoDataException $e)
		{
		$stm->close();
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