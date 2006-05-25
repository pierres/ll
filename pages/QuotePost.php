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
		}
	catch (IoException $e)
		{
		$this->showFailure('Kein Beitrag angegeben!');
		}
	catch (DBNoDataException $e)
		{
		$this->showFailure('Beitrag nicht gefunden oder Thema geschlossen!');
		}

	$this->text = '<quote '.unhtmlspecialchars($data['username']).">\n".$this->UnMarkup->fromHtml($data['text'])."\n</quote>\n\n";

	$this->thread = $data['thread'];
	$this->forum = $data['forumid'];

	$this->addHidden('post', $data['post']);
	}

}

?>