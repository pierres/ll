<?php

class AdminCats extends AdminForm{



protected function setForm()
	{
	$this->setValue('title', 'Kategorien');

	$this->addSubmit('Speichern');

	try
		{
		$cats = $this->Sql->fetch
			('
			SELECT
				id,
				name,
				position
			FROM
				cats
			WHERE
				boardid = '.$this->Board->getId().'
			ORDER BY
				position
			');
		}
	catch (SqlNoDataException $e)
		{
		$cats = array();
		}

	foreach ($cats as $cat)
		{
		$this->addOutput
			(
			AdminFunctions::buildPositionMenu('position['.$cat['id'].']', count($cats), $cat['position']).'
			<input type="text" name="name['.$cat['id'].']" size="77" value="'.$cat['name'].'" />
			<a href="?page=AdminForums;id='.$this->Board->getId().';cat='.$cat['id'].'"><span class="button">Foren</span></a>
			<a href="?page=AdminCatsDel;id='.$this->Board->getId().';cat='.$cat['id'].'"><span class="button" style="background-color:#CC0000">löschen</span></a>
			<br /><br />
			');
		}

	$this->addOutput
		(
		AdminFunctions::buildPositionMenu('newposition', count($cats)+1, count($cats)+1).'
		<input type="text" name="newname" size="77" value="" />
		');
	}

protected function checkForm()
	{
	/** FIXME */
	}

protected function sendForm()
	{
	$cats = $this->Io->getArray();

	try
		{
		foreach($cats as $cat => $value)
			{
			$this->Sql->query
				('
				UPDATE
					cats
				SET
					position = '.intval($value['position']).',
					name = \''.$this->Sql->escapeString(htmlspecialchars($value['name'])).'\'
				WHERE
					boardid = '.$this->Board->getId().'
					AND id = '.intval($cat)
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
					cats
				SET
					position = '.($this->Io->isEmpty('position') ? 0 : $this->Io->getInt('newposition')).',
					name = \''.$this->Sql->escapeString($this->Io->getHtml('newname')).'\',
					boardid = '.$this->Board->getId()
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
	$this->Io->redirect('AdminCats');
	}

}


?>