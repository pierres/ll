<?php


class Logout extends Page{


public function prepare()
	{
	$this->User->logout();

	$this->Io->redirect('Forums');
	}


}

?>