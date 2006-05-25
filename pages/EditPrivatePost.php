<?php


class EditPrivatePost extends NewPrivatePost{

protected $post 		= 0;
protected $title 		= 'Beitrag bearbeiten';



protected function checkInput()
	{
	try
		{
		$stm = $this->DB->prepare
			('
			SELECT
				id,
				text,
				threadid,
				smilies
			FROM
				posts
			WHERE
				id = ?'
			);
		$stm->bindInteger($this->Io->getInt('post'));
		$data = $stm->getRow();
		}
	catch (IoException $e)
		{
		$this->showFailure('Kein Beitrag angegeben!');
		}
	catch (DBNoDataException $e)
		{
		$this->showFailure('Beitrag nicht gefunden!');
		}

	$this->post = $data['id'];
	$this->text =  $this->UnMarkup->fromHtml($data['text']);
	$this->thread = $data['threadid'];
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
				id = ?
				AND userid = ?'
			);
		$stm->bindInteger($this->post);
		$stm->bindInteger($this->User->getId());
		$access = $stm->getColumn();
		}
	catch (DBNoDataException $e)
		{
		$this->showFailure('Kein Beitrag gefunden.');
		}
	}

protected function sendForm()
	{
	$this->Markup->enableSmilies($this->smilies);
	$this->text = $this->Markup->toHtml($this->text);
	// BugFix for Bug#1
	if ($length = strlen($this->text) > 65536)
		{
		$this->showWarning('Der Text ist '.($length-65536).' Zeichen zu lang!');
		$this->showForm();
		return;
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