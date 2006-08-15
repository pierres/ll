<?php

class StickThread extends EditThread{


public function prepare()
	{
	$this->allow_closed = true;
	$this->checkInput();
	$this->checkAccess();
	$this->sendForm();
	}

protected function showForm()
	{
	}

protected function sendForm()
	{
	$stm = $this->DB->prepare
		('
		UPDATE
			threads
		SET
			sticky = ABS(sticky - 1)
		WHERE
			id = ?'
		);
	$stm->bindInteger($this->thread);
	$stm->execute();
	$stm->close();

// 	$this->updateForum();
	$this->redirect();
	}


protected function redirect()
	{
	$this->Io->redirect('Threads', 'forum='.$this->forum);
	}

}

?>