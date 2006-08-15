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
	$stm = $this->DB->prepare
		('
		UPDATE
			posts
		SET
			deleted = ABS(deleted - 1)
		WHERE
			id = ?'
		);
	$stm->bindInteger($this->post);
	$stm->execute();
	$stm->close();

	$this->updateThread();
 	$this->updateForum();
	$this->redirect();
	}

protected function updateThread()
	{
	/** TODO: nicht optimal */
	AdminFunctions::updateThread($this->thread);
	}

protected function updateForum()
	{
	/** TODO: nicht optimal */
	AdminFunctions::updateForum($this->forum);
	}

protected function redirect()
	{
	$this->Io->redirect('Postings', 'thread='.$this->thread);
	}

}

?>