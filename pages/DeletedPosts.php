<?php

class DeletedPosts extends Form{

protected function setForm()
	{
	$this->setValue('title', 'Gelöschte Beiträge');
	$this->addSubmit('Löschen');

	if (!$this->User->isLevel(User::ROOT))
		{
		$this->showFailure('kein Zugriff!');
		}

	try
		{
		$posts = $this->DB->getRowSet
			('
			SELECT
				posts.id,
				posts.threadid,
				posts.text,
				threads.name
			FROM
				posts,
				threads
			WHERE
				posts.deleted = 1
				AND threads.id = posts.threadid
			ORDER BY
				posts.dat DESC
			');

		foreach ($posts as $post)
			{
			$this->addOutput('<input type="checkbox" id="id'.$post['id'].'" name="post[]" value="'.$post['id'].'" /><label for="id'.$post['id'].'"><a onmouseover="javascript:document.getElementById(\'post'.$post['id'].'\').style.visibility=\'visible\'"
			onmouseout="javascript:document.getElementById(\'post'.$post['id'].'\').style.visibility=\'hidden\'"  href="?page=Postings;id='.$this->Board->getId().';thread='.$post['threadid'].'">'.$post['name'].'</a></label><br /><script type="text/javascript">
						<!--
						document.write("<div class=\"summary\" style=\"visibility:hidden;\" id=\"post'.$post['id'].'\">'.cutString(strip_tags($post['text']), 300).'</div>");
						-->
					</script>');
			}
		}
	catch (DBNoDataException $e)
		{
		}
	}

protected function sendForm()
	{
	foreach($this->Io->getArray('post') as $post)
		{
		AdminFunctions::delPost($post);
		}
	$this->redirect();
	}


protected function redirect()
	{
	$this->Io->redirect('DeletedPosts');
	}

}

?>