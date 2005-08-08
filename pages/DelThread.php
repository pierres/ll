<?php

class DelThread extends EditThread{


public function prepare()
	{
	$this->allow_deleted = true;
	$this->allow_closed = true;
	$this->checkInput();
	$this->checkAccess();
	$this->sendForm();
	}

protected function sendForm()
	{
	$this->Sql->query
		('
		UPDATE
			threads
		SET
			deleted = ABS(deleted - 1)
		WHERE
			id = '.$this->thread
		);

	$this->updateForum();
	$this->redirect();
	}


protected function redirect()
	{
	$this->Io->redirect('Threads', 'forum='.$this->forum);
	}

}

?>