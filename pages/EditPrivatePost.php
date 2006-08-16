<?php


class EditPrivatePost extends NewPrivatePost{

protected $post 	= 0;
protected $title 	= 'Beitrag bearbeiten';


protected function checkInput()
	{
	try
		{
		$this->post = $this->Io->getInt('post');
		$this->addHidden('post', $this->post);
		}
	catch (IoException $e)
		{
		$this->showFailure('Kein Beitrag angegeben!');
		}

	try
		{
		$stm = $this->DB->prepare
			('
			SELECT
				text,
				threadid,
				smilies
			FROM
				posts
			WHERE
				id = ?'
			);
		$stm->bindInteger($this->post);
		$data = $stm->getRow();
		$stm->close();

		$this->text =  $this->UnMarkup->fromHtml($data['text']);
		$this->thread = $data['threadid'];
		$this->smilies = ($data['smilies'] == 0 ? false : true);
		}
	catch (DBNoDataException $e)
		{
		$stm->close();
		$this->showFailure('Beitrag nicht gefunden!');
		}
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