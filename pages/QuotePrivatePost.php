<?php


class QuotePrivatePost extends NewPrivatePost{


protected $title = 'Beitrag zitieren';


protected function checkInput()
	{
	/** Hier noch weitere Test bzgl. PrivateThreads nötig */
	try
		{
		$data = $this->Sql->fetchRow
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
				AND thread_user.userid = '.$this->User->getId().'
				AND posts.threadid = threads.id
				AND posts.id = '.$this->Io->getInt('post')
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

	$this->text = '<quote='.unhtmlspecialchars($data['username']).'>'.$this->UnMarkup->fromHtml($data['text']).'</quote>'."\n\n";

	$this->thread = $data['thread'];

	$this->addHidden('post', $data['post']);
	}

}

?>