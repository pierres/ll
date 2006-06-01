<?php

class SpamPost extends Form{

protected $post = 0;
protected $thread = 0;
protected $forum = 0;

protected function setForm()
	{
	try
		{
		$this->post = $this->Io->getInt('post');
		}
	catch (IoException $e)
		{
		$this->showFailure('Kein Beitrag angegeben');
		}

	if (!$this->User->isLevel(User::MOD))
		{
		$this->showFailure('Zutritt nur für Moderatoren!');
		}

	try
		{
		$stm = $this->DB->prepare
			('
			SELECT
				posts.text,
				posts.threadid,
				threads.forumid
			FROM
				posts JOIN threads ON threads.id = posts.threadid
			WHERE
				posts.id = ?
			');
		$stm->bindInteger($this->post);
		$data = $stm->getRow();
		$text = $data['text'];
		$this->thread = $data['threadid'];
		$this->forum = $data['forumid'];
		$stm->close();
		}
	catch (DBNoDataException $e)
		{
		$stm->close();
		$this->showFailure('Kein Beitrag gefunden');
		}

	$this->showDomainList($text);
	$this->addHidden('post', $this->post);
	}

protected function showDomainList($text)
	{
	$this->setValue('title', 'Beitrag als Spam markieren');
	$this->addSubmit('Abschicken');
	$this->addElement('hint', 'Wähle die Domain aus, die in Zukunft blockiert werden soll.');

	$AntiSpam = new AntiSpam($text);

	foreach ($AntiSpam->getListedDomains() as $listedDomain)
		{
		$this->addElement($listedDomain, '<input type="checkbox" id="id'.$listedDomain.'" name="'.$listedDomain.'" checked="checked" disabled="disabled" /><label for="id'.$listedDomain.'">'.$listedDomain.'</label><br />');
		}

	foreach ($AntiSpam->getNonListedDomains() as $nonListedDomain)
		{
		$nonListedDomain = htmlspecialchars($nonListedDomain);
		$this->addElement($nonListedDomain, '<input type="checkbox" id="id'.md5($nonListedDomain).'" value="'.$nonListedDomain.'" name="domains[]" checked="checked" /><label for="id'.md5($nonListedDomain).'">'.$nonListedDomain.'</label>');
		}
	}

protected function checkForm()
	{
	}

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

	AdminFunctions::updateThread($this->thread);
	AdminFunctions::updateForum($this->forum);

	$this->sendDomainList();

	$this->Io->redirect('Postings', 'thread='.$this->thread);
	}

protected function sendDomainList()
	{
	try
		{
		$stm = $this->DB->prepare
			('
			INSERT INTO
				domain_blacklist
			SET
				domain = ?,
				inserted = UNIX_TIMESTAMP(),
				lastmatch = UNIX_TIMESTAMP()
			');
		foreach ($this->Io->getArray('domains') as $domain)
			{
			$stm->bindString($domain);
			$stm->execute();
			}
		$stm->close();
		}
	catch (IoException $e)
		{
		$this->showWarning('Keine Domain ausgewählt.');
		}
	}

}

?>