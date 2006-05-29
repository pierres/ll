<?php

class SpamPost extends DelPost{


protected function sendForm()
	{
	$stm = $this->DB->prepare
		('
		UPDATE
			posts
		SET
			deleted = 1
		WHERE
			id = ?'
		);
	$stm->bindInteger($this->post);
	$stm->execute();
	$stm->close();

	$this->updateThread();
	$this->updateForum();

	$this->AntiSpam->addSpam($this->text);

	$this->redirect();
	}

}

?>