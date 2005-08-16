<?php


class EditPrivatePost extends NewPrivatePost{

protected $post 		= 0;
protected $title 		= 'Beitrag bearbeiten';



protected function checkInput()
	{
	try
		{
		$data = $this->Sql->fetchRow
			('
			SELECT
				id,
				text,
				threadid,
				smilies
			FROM
				posts
			WHERE
				id = '.$this->Io->getInt('post')
			);
		}
	catch (IoException $e)
		{
		$this->showFailure('Kein Beitrag angegeben!');
		}
	catch (SqlNoDataException $e)
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