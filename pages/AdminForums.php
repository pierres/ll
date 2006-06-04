<?php

class AdminForums extends AdminForm{

private $cat = 0;

protected function setForm()
	{
	try
		{
		$this->cat = $this->Io->getInt('cat');
		}
	catch (IoRequestException $e)
		{
		$this->showFailure('Keine Kategorie angegeben');
		}

	$this->setValue('title', 'Foren');

	$this->addSubmit('Speichern');

	try
		{
		$stm = $this->DB->prepare
			('
			SELECT
				forums.id,
				forums.name,
				forums.description,
				forums.boardid,
				forums.mods,
				forum_cat.position
			FROM
				forums,
				forum_cat,
				cats
			WHERE
				forum_cat.forumid = forums.id
				AND forum_cat.catid = cats.id
				AND forum_cat.catid = ?
				AND cats.boardid = ?
			ORDER BY
				forum_cat.position
			');
		$stm->bindInteger($this->cat);
		$stm->bindInteger($this->Board->getId());
		$forums = $stm->getRowSet();
		$totalForums = $stm->getNumRows();
		}
	catch (DBNoDataException $e)
		{
		$forums = array();
		$totalForums = 0;
		}

	foreach ($forums as $forum)
		{
		if ($forum['boardid'] == $this->Board->getId())
			{
			$this->addOutput
				(
				'<table><tr><td>'.
				AdminFunctions::buildPositionMenu('forums['.$forum['id'].'][position]', $totalForums, $forum['position']).'
				<input type="text" name="forums['.$forum['id'].'][name]" size="74" value="'.$forum['name'].'" />
				<br />
				<textarea name="forums['.$forum['id'].'][description]" cols="80" rows="4">'.$forum['description'].'</textarea>
				<br />
				<a href="?page=AdminForumsMove;id='.$this->Board->getId().';forum='.$forum['id'].'"><span class="button">verschieben</span></a>
				<a href="?page=AdminForumsDel;id='.$this->Board->getId().';forum='.$forum['id'].'"><span class="button" style="background-color:#CC0000">löschen</span></a>
				</td>
					<td style="vertical-align:bottom">
					'.$this->getMods($forum['mods']).'
					<br />
					<a href="?page=AdminForumsMods;id='.$this->Board->getId().';forum='.$forum['id'].'"><span class="button">Moderatoren</span></a>
					</td>
				</tr>
				</table>
				<br /><br />
				');
			}
		else
			{
			$this->addOutput
				(
				'<table style="width:100%"><tr><td>'.
				AdminFunctions::buildPositionMenu('forums['.$forum['id'].'][position]', $totalForums, $forum['position']).'
				<input disabled="disabled" type="text" name="forums['.$forum['id'].'][name]" size="74" value="'.$forum['name'].'" />
				<br />
				<textarea disabled="disabled" name="forums['.$forum['id'].'][description]" cols="80" rows="4">'.$forum['description'].'</textarea>
				<br />
				<a href="?page=AdminForumsMove;id='.$this->Board->getId().';forum='.$forum['id'].'"><span class="button">verschieben</span></a>
				<a href="?page=AdminForumsDelEx;id='.$this->Board->getId().';forum='.$forum['id'].'"><span class="button" style="background-color:#CC6600">löschen</span></a>
				</td>
					<td style="vertical-align:bottom">
					'.$this->getMods($forum['mods']).'
					</td>
				</tr>
				</table>
				<br /><br />
				');
			}
		}
	$stm->close();

	$this->addOutput
		(
		'<table style="width:100%"><tr><td>'.
		AdminFunctions::buildPositionMenu('newposition', $totalForums+1, $totalForums+1).'
		<input type="text" name="newname" size="74" value="" />
		<br />
		<textarea name="newdescription" cols="80" rows="4"></textarea>
		<br />
		<a href="?page=AdminForumsEx;id='.$this->Board->getId().';cat='.$this->cat.'"><span class="button">externe Foren hinzufügen</span></a>
		</td>
		</tr>
		</table>
		<input type="hidden" name="cat" value="'.$this->cat.'" />
		');
	}

private function getMods($group)
	{
	$mods = array();

	try
		{
		$stm = $this->DB->prepare
			('
			SELECT
				users.name
			FROM
				users,
				user_group
			WHERE
				user_group.userid = users.id
				AND user_group.groupid = ?'
			);
		$stm->bindInteger($group);

		foreach($stm->getColumnSet() as $mod)
			{
			$mods[] = $mod;
			}

		$stm->close();
		}
	catch (DBNoDataException $e)
		{
		}

	return implode('<br />',$mods);
	}

protected function checkForm()
	{
	/** TODO Eingaben genauer prüfen (vor allem auf LeerStrings) */
	try
		{
		$stm = $this->DB->prepare
			('
			SELECT
				id
			FROM
				cats
			WHERE
				id = ?
				AND boardid = ?'
			);
		$stm->bindInteger($this->cat);
		$stm->bindInteger($this->Board->getId());
		$stm->getColumn();
		$stm->close();
		}
	catch(DBNoDataException $e)
		{
		$stm->close();
		$this->Io->redirect('AdminCats');
		}
	}

protected function sendForm()
	{
	try
		{
		$forums = $this->Io->getArray('forums');
		}
	catch (IoRequestException $e)
		{
		if ($this->Io->isEmpty('newname'))
			{
			$this->redirect();
			}
		}

	if (!empty($forums))
		{
		$stm = $this->DB->prepare
			('
			UPDATE
				forums
			SET
				name = ?,
				description = ?
			WHERE
				boardid = ?
				AND id = ?'
			);

		$stm2 = $this->DB->prepare
			('
			UPDATE
				forum_cat
			SET
				position = ?
			WHERE
				forumid = ?
				AND catid = ?'
			);
		foreach($forums as $id => $forum)
			{
			if(isset($forum['name']) && isset($forum['description']) && isset($id))
				{
				$stm->bindString(htmlspecialchars($forum['name']));
				$stm->bindString(htmlspecialchars($forum['description']));
				$stm->bindInteger($this->Board->getId());
				$stm->bindInteger($id);
				$stm->execute();
				}

			$stm2->bindInteger($forum['position']);
			$stm2->bindInteger($id);
			$stm2->bindInteger($this->cat);
			$stm2->execute();
			}
		$stm->close();
		$stm2->close();
		}

	if (!$this->Io->isEmpty('newname'))
		{
		$stm = $this->DB->prepare
			('
			INSERT INTO
				forums
			SET
				name = ?,
				description = ?,
				boardid = ?'
			);
		$stm->bindString($this->Io->getHtml('newname'));
		$stm->bindString($this->Io->getHtml('newdescription'));
		$stm->bindInteger($this->Board->getId());
		$stm->execute();
		$stm->close();

		$stm = $this->DB->prepare
			('
			INSERT INTO
				forum_cat
			SET
				forumid = LAST_INSERT_ID(),
				position = ?,
				catid = ?'
			);
		$stm->bindInteger($this->Io->getInt('newposition'));
		$stm->bindInteger($this->cat);
		$stm->execute();
		$stm->close();
		}

	$this->redirect();
	}

protected function redirect()
	{
	$this->Io->redirect('AdminForums', 'cat='.$this->cat);
	}


}


?>