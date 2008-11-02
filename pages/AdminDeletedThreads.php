<?php
/*
	Copyright 2002-2007 Pierre Schmitz <pschmitz@laber-land.de>

	This file is part of LL.

	LL is free software: you can redistribute it and/or modify
	it under the terms of the GNU General Public License as published by
	the Free Software Foundation, either version 3 of the License, or
	(at your option) any later version.

	LL is distributed in the hope that it will be useful,
	but WITHOUT ANY WARRANTY; without even the implied warranty of
	MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
	GNU General Public License for more details.

	You should have received a copy of the GNU General Public License
	along with LL.  If not, see <http://www.gnu.org/licenses/>.
*/
class AdminDeletedThreads extends AdminForm{

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
	try
		{
		foreach($this->Input->Request->getArray('thread') as $thread)
			{
			AdminFunctions::delThread($thread);
			}
		}
	catch (RequestException $e)
		{
		}
	$this->redirect();
	}


protected function redirect()
	{
	$this->Output->redirect('AdminDeletedThreads');
	}

}

?>