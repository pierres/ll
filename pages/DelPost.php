<?php

class DelPost extends EditPost{


public function prepare()
	{
	$this->allow_deleted = true;
	$this->checkInput();
	$this->checkAccess();
	$this->sendForm();
	}

protected function sendForm()
	{
	$this->Sql->query
		('
		UPDATE
			posts
		SET
			deleted = ABS(deleted - 1)
		WHERE
			id = '.$this->post
		);

	$this->updateThread();
	$this->updateForum();
	$this->redirect();
	}


protected function redirect()
	{
	$this->Io->redirect('Postings', 'thread='.$this->thread);
	}

}

?>