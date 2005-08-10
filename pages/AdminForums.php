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
		$forums = $this->Sql->fetch
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
				AND forum_cat.catid = '.$this->cat.'
				AND cats.boardid = '.$this->Board->getId().'
			ORDER BY
				forum_cat.position
			');
		}
	catch (SqlNoDataException $e)
		{
		$forums = array();
		}

	foreach ($forums as $forum)
		{
		if ($forum['boardid'] == $this->Board->getId())
			{
			$this->addOutput
				(
				'<table><tr><td>'.
				AdminFunctions::buildPositionMenu('position['.$forum['id'].']', count($forums), $forum['position']).'
				<input type="text" name="name['.$forum['id'].']" size="74" value="'.$forum['name'].'" />
				<br />
				<textarea name="description['.$forum['id'].']" cols="80" rows="4">'.$forum['description'].'</textarea>
				<br />
				<a href="?page=AdminForumsMove;id='.$this->Board->getId().';forum='.$forum['id'].'"><span class="button">verschieben</span></a>
				<a href="?page=AdminForumsDel;id='.$this->Board->getId().';forum='.$forum['id'].'"><span class="button" style="background-color:#CC0000">löschen</span></a>
				</td>
					<td style="vertical-align:bottom">
					'.implode('<br />', $this->getMods($forum['mods'])).'
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
				AdminFunctions::buildPositionMenu('position['.$forum['id'].']', count($forums), $forum['position']).'
				<input disabled="disabled" type="text" name="name['.$forum['id'].']" size="74" value="'.$forum['name'].'" />
				<br />
				<textarea disabled="disabled" name="description['.$forum['id'].']" cols="80" rows="4">'.$forum['description'].'</textarea>
				<br />
				<a href="?page=AdminForumsMove;id='.$this->Board->getId().';forum='.$forum['id'].'"><span class="button">verschieben</span></a>
				<a href="?page=AdminForumsDelEx;id='.$this->Board->getId().';forum='.$forum['id'].'"><span class="button" style="background-color:#CC6600">löschen</span></a>
				</td>
					<td style="vertical-align:bottom">
					'.implode('<br /> ', $this->getMods($forum['mods'])).'
					</td>
				</tr>
				</table>
				<br /><br />
				');
			}
		}

	$this->addOutput
		(
		'<table style="width:100%"><tr><td>'.
		AdminFunctions::buildPositionMenu('newposition', count($forums)+1, count($forums)+1).'
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
	try
		{
		$mods = $this->Sql->fetchCol
			('
			SELECT
				users.name
			FROM
				users,
				user_group
			WHERE
				user_group.userid = users.id
				AND user_group.groupid = '.$group
			);
		}
	catch (SqlNoDataException $e)
		{
		$mods = array();
		}

	return $mods;
	}

protected function checkForm()
	{
	try
		{
		$this->Sql->fetchValue
			('
			SELECT
				id
			FROM
				cats
			WHERE
				id = '.$this->cat.'
				AND boardid = '.$this->Board->getId()
			);
		}
	catch(SqlNoDataException $e)
		{
		$this->Io->redirect('AdminCats');
		}
	}

protected function sendForm()
	{
	$forums = $this->Io->getArray();

	try
		{
		foreach($forums as $forum => $value)
			{
			if(isset($value['name']) && isset($value['description']))
				{
				$this->Sql->query
					('
					UPDATE
						forums
					SET
						name = \''.$this->Sql->escapeString(htmlspecialchars($value['name'])).'\',
						description = \''.$this->Sql->escapeString(htmlspecialchars($value['description'])).'\'
					WHERE
						boardid = '.$this->Board->getId().'
						AND id = '.intval($forum)
					);
				}

			$this->Sql->query
				('
				UPDATE
					forum_cat
				SET
					position = '.intval($value['position']).'
				WHERE
					forumid = '.intval($forum).'
					AND catid = '.$this->cat
				);
			}
		}
	catch(SqlException $e)
		{
		/** FIXME */
		}

	if (!$this->Io->isEmpty('newname'))
		{
		try
			{
			$this->Sql->query
				('
				INSERT INTO
					forums
				SET
					name = \''.$this->Sql->escapeString($this->Io->getHtml('newname')).'\',
					description = \''.$this->Sql->escapeString($this->Io->getHtml('newdescription')).'\',
					boardid = '.$this->Board->getId()
				);

			$this->Sql->query
				('
				INSERT INTO
					forum_cat
				SET
					forumid = LAST_INSERT_ID(),
					position = '.intval($this->Io->getInt('newposition')).',
					catid = '.$this->cat
				);
			}
		catch(SqlException $e)
			{
			/** FIXME */
			}
		}

	$this->redirect();
	}

protected function redirect()
	{
	$this->Io->redirect('AdminForums', 'cat='.$this->cat);
	}


}


?>