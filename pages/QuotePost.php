<?php


class QuotePost extends NewPost{


protected $title = 'Beitrag zitieren';


protected function checkInput()
	{
	/** Hier noch weitere Test bzgl. PrivateThreads nötig */
	try
		{
		$stm = $this->DB->prepare
			('
			SELECT
				posts.id AS post,
				threads.id AS thread,
				threads.forumid,
				posts.text,
				posts.username
			FROM
				posts,
				threads
			WHERE
				posts.deleted = 0
				AND threads.closed = 0
				AND threads.deleted = 0
				AND threads.forumid != 0
				AND posts.threadid = threads.id
				AND posts.id = ?'
			);
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

	$this->text = '<quote '.unhtmlspecialchars($data['username']).">\n".$this->UnMarkup->fromHtml($data['text'])."\n</quote>\n\n";

	$this->thread = $data['thread'];
	$this->forum = $data['forumid'];

	$this->addHidden('post', $data['post']);
	}

protected function checkForm()
	{
	if (!$this->User->isOnline())
		{
		$text = preg_replace('/\s*<quote .+?>.+<\/quote>\s*/s', '', $this->Io->getString('text'));
		if (empty($text))
			{
			$this->showWarning('Du mußt auch selbst etwas dazu schreiben!');
			}
		}

	parent::checkForm();
	}

}

?>