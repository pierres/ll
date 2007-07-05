<?php

class DeletedPosts extends AdminForm{

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
				posts JOIN threads ON threads.id = posts.threadid
			WHERE
				posts.deleted = 1
			ORDER BY
				posts.dat DESC
			');

		$this->addOutput('<script type="text/javascript">
					/* <![CDATA[ */
					function writeText(text)
						{
						var pos;
						pos = document;
						while ( pos.lastChild && pos.lastChild.nodeType == 1 )
							pos = pos.lastChild;
						pos.parentNode.appendChild( document.createTextNode(text));
						}
					/* ]]> */
				</script>');

		foreach ($posts as $post)
			{
			$this->addOutput('<input type="checkbox" id="id'.$post['id'].'" name="post[]" value="'.$post['id'].'" /><label for="id'.$post['id'].'"><a onmouseover="javascript:document.getElementById(\'post'.$post['id'].'\').style.visibility=\'visible\'"
			onmouseout="javascript:document.getElementById(\'post'.$post['id'].'\').style.visibility=\'hidden\'"  href="?page=Postings;id='.$this->Board->getId().';thread='.$post['threadid'].'">'.$post['name'].'</a></label><br /><div class="summary" style="visibility:hidden;" id="post'.$post['id'].'">
				<script type="text/javascript">
						/* <![CDATA[ */
						writeText("'.cutString(strip_tags($post['text']), 300).'");
						/* ]]> */
				</script>
			</div>');
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