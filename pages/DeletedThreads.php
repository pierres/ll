<?php

class DeletedThreads extends AdminForm{

protected function setForm()
	{
	$this->setValue('title', 'Gelöschte Themen');
	$this->addSubmit('Löschen');

	if (!$this->User->isLevel(User::ROOT))
		{
		$this->showFailure('kein Zugriff!');
		}

	try
		{
		$threads = $this->DB->getRowSet
			('
			SELECT
				id,
				name,
				summary
			FROM
				threads
			WHERE
				deleted = 1
			ORDER BY
				lastdate DESC
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

		foreach ($threads as $thread)
			{
			$this->addOutput('<input type="checkbox" id="id'.$thread['id'].'" name="thread[]" value="'.$thread['id'].'" /><label for="id'.$thread['id'].'"><a onmouseover="javascript:document.getElementById(\'post'.$thread['id'].'\').style.visibility=\'visible\'"
			onmouseout="javascript:document.getElementById(\'post'.$thread['id'].'\').style.visibility=\'hidden\'"  href="?page=Postings;id='.$this->Board->getId().';thread='.$thread['id'].'">'.$thread['name'].'</a></label><br /><div class="summary" style="visibility:hidden;" id="post'.$thread['id'].'">
			<script type="text/javascript">
				/* <![CDATA[ */
				writeText("'.$thread['summary'].'");
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
	foreach($this->Io->getArray('thread') as $thread)
		{
		AdminFunctions::delThread($thread);
		}
	$this->redirect();
	}


protected function redirect()
	{
	$this->Io->redirect('DeletedThreads');
	}

}

?>