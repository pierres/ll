<?php

class SpamThread extends DelThread{


protected function sendForm()
	{
	$stm = $this->DB->prepare
		('
		UPDATE
			threads
		SET
			deleted = 1
		WHERE
			id = ?'
		);
	$stm->bindInteger($this->thread);
	$stm->execute();
	$stm->close();

	$this->updateForum();

	$this->AntiSpam->addSpam($this->text);

	$this->redirect();
	}

}

?>