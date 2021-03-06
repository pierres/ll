<?php
/*
	Copyright 2002-2007 Pierre Schmitz <pschmitz@laber-land.de>

	This file is part of LL.

	LL is free software: you can redistribute it and/or modify
	it under the terms of the GNU Affero General Public License as published by
	the Free Software Foundation, either version 3 of the License, or
	(at your option) any later version.

	LL is distributed in the hope that it will be useful,
	but WITHOUT ANY WARRANTY; without even the implied warranty of
	MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
	GNU Affero General Public License for more details.

	You should have received a copy of the GNU Affero General Public License
	along with LL.  If not, see <http://www.gnu.org/licenses/>.
*/

class AdminDeletedPosts extends AdminForm {

protected function setForm()
	{
	$this->setTitle('Gelöschte Beiträge');
	$this->add(new SubmitButtonElement('Löschen'));

	if (!$this->User->isLevel(User::ROOT))
		{
		$this->showFailure('kein Zugriff');
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

		$this->add(new PassiveFormElement('<script type="text/javascript">
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
				</script>'));

		foreach ($posts as $post)
			{
			$this->add(new PassiveFormElement('<input type="checkbox" id="id'.$post['id'].'" name="post[]" value="'.$post['id'].'" /><label for="id'.$post['id'].'"><a onmouseover="javascript:document.getElementById(\'post'.$post['id'].'\').style.visibility=\'visible\'"
			onmouseout="javascript:document.getElementById(\'post'.$post['id'].'\').style.visibility=\'hidden\'"  href="'.$this->Output->createUrl('Postings', array('thread' => $post['threadid'])).'">'.$post['name'].'</a></label><br /><div class="summary" style="visibility:hidden;" id="post'.$post['id'].'">
				<script type="text/javascript">
						/* <![CDATA[ */
						writeText("'.cutString(strip_tags($post['text']), 300).'");
						/* ]]> */
				</script>
			</div>'));
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
		foreach($this->Input->Post->getArray('post') as $post)
			{
			AdminFunctions::delPost($post);
			}
		}
	catch (RequestException $e)
		{
		}

	$this->Output->redirect('AdminDeletedPosts');
	}

}

?>