<?php

require('NewPrivatePost.php');

class QuotePrivatePost extends NewPrivatePost{


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
				posts.text,
				posts.username
			FROM
				posts,
				threads,
				thread_user
			WHERE
				threads.forumid = 0
				AND thread_user.threadid = threads.id
				AND thread_user.userid = ?
				AND posts.threadid = threads.id
				AND posts.id = ?'
			);
		$stm->bindInteger($this->User->getId());
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
		$this->showFailure('Beitrag nicht gefunden!');
		}

	$this->text = '<quote '.unhtmlspecialchars($data['username']).">\n".$this->UnMarkup->fromHtml($data['text'])."\n</quote>\n\n";

	$this->thread = $data['thread'];

	$this->addHidden('post', $data['post']);
	}

}

?>