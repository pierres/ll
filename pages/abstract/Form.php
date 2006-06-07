<?php


abstract class Form extends Page{


protected $elements 	= array();
protected $descriptions = array();
protected $buttons 	= array();
protected $hidden 	= array();

protected $warning	= array();
protected $required 	= array();

protected $focus	= '';

private $encoding 	= '';
private $request	= '';


public function prepare()
	{
	$this->setForm();
	$this->addAntiSpamHash();

	if ($this->Io->isRequest('submit') && count($this->warning) == 0)
		{
		$this->checkAntiSpamHash();
		$this->checkForm();

		if (count($this->warning) == 0)
			{
			$this->sendForm();
			}
		else
			{
			$this->showForm();
			}
		}
	else
		{
		$this->showForm();
		}
	}

protected function setForm()
	{
	if (empty($this->buttons['submit']))
		{
		$this->addSubmit('Abschicken');
		}
	}

private function addAntiSpamHash()
	{
	if (!$this->User->isOnline())
		{
		$time = time();
		$this->addHidden('AntiSpamTime', $time);
		$this->addHidden('AntiSpamHash', sha1($time.$this->Settings->getValue('antispam_hash')));
		}
	}

private function checkAntiSpamHash()
	{
	if (!$this->User->isOnline())
		{
		try
			{
			$time = $this->Io->getInt('AntiSpamTime');
			$hash = $this->Io->getHex('AntiSpamHash');
			}
		catch (IoRequestException $e)
			{
			$this->showFailure('Ungültige Formulardaten empfangen. Geh weg!');
			}

		if ($hash != sha1($time.$this->Settings->getValue('antispam_hash')))
			{
			$this->showFailure('Manipulierte Formulardaten empfangen. Geh weg!');
			}

		if (time() - $time >= $this->Settings->getValue('antispam_timeout'))
			{
			$this->showWarning('Deine Zeit ist abgelaufen. Schicke den Beitrag bitte erneut.');
			}
		}
	}

protected function checkForm()
	{
	}

protected function showForm()
	{
	$body =
		$this->getWarning().'
		<form '.$this->encoding.' method="post" action="?page='.$this->getName().';id='.$this->Board->getId().$this->request.'">
			<table class="frame">
				<tr>
					<td class="title">
						'.$this->getValue('title').'
					</td>
				</tr>
				<tr>
					<td class="main">
						'.implode('', $this->elements).'
					</td>
				</tr>
				<tr>
					<td class="main">
						'.implode('', $this->hidden)
						.implode(' ', $this->buttons).'
						&nbsp;&nbsp;
						<input accesskey="r" class="button" type="reset" name="reset" value="Zurücksetzen" />
					</td>
				</tr>
			</table>
		</form>
		<script type="text/javascript">
			document.getElementById("id'.$this->focus.'").focus();
		</script>
		';

	$this->setValue('body', $body);
	}

protected function sendForm()
	{
	}

protected function redirect()
	{
	}

protected function addOutput($value)
	{
	$this->elements[] = $value;
	}

protected function addElement($name,  $value)
	{
	$this->elements[$name] = '<div>'.$value.'</div>';
	}

protected function addButton($name, $value)
	{
	$this->buttons[$name] = '<input class="button" type="submit" name="'.$name.'" value="'.$value.'" />';
	return $name;
	}

protected function addSubmit($value)
	{
	$this->buttons['submit'] = '<input accesskey="s" class="button" type="submit" name="submit" value="'.$value.'" />';

	return 'submit';
	}

protected function isSubmit()
	{
	foreach ($this->buttons as $name => $value)
		{
		if ($this->Io->isRequest($name))
			{
			return true;
			}
		}

	return false;
	}

protected function addFile($name, $description, $cols = 50)
	{
	$this->addHidden('MAX_FILE_SIZE', $this->Settings->getValue('file_size'));
	/** Workaround for PHP-"Bug". See http://de3.php.net/manual/en/ini.core.php#ini.post-max-size */
	$this->request .= ';submit=1';

	$this->addElement($name, '<label for="id'.$name.'">'.$description.'</label><br /><input id="id'.$name.'" type="file" name="'.$name.'" size="'.$cols.'" />');
	$this->descriptions[$name] = $description;

	$this->setFocus($name);

	$this->encoding = 'enctype="multipart/form-data"';

	return $name;
	}

protected function addCheckbox($name, $description, $checked = false)
	{
	if ($this->isSubmit())
		{
		$checked = $this->Io->isRequest($name);
		}

	$this->addElement($name, '<input type="checkbox" id="id'.$name.'" name="'.$name.'"'.($checked ? ' checked="checked"' : '').' /><label for="id'.$name.'">'.$description.'</label>');
	$this->descriptions[$name] = $description;

	return $name;
	}

protected function addRadio($name, $description, $array, $default = '')
	{
	$elements = '';

	foreach ($array as $key => $value)
		{
		if ($this->isSubmit() && $this->Io->isRequest($name))
			{
			$checked = ($this->Io->getString($name) == $value);
			}
		else
			{
			$checked = ($default == $value);
			}

		$elements .= '<input type="radio" name="'.$name.'"'.($checked ? ' checked="checked"' : '').' value="'.$value.'" />'.$key.'<br />';
		}

	$this->addElement($name, '<fieldset><legend>'.$description.'</legend>'.$elements.'</fieldset>');
	$this->descriptions[$name] = $description;

	return $name;
	}

protected function addHidden($name, $value)
	{
	$this->hidden[$name] = '<input type="hidden" name="'.$name.'" value="'.$value.'" />';
	return $name;
	}

protected function addTextarea($name, $description = '', $text = '', $cols = 80, $rows = 20)
	{
	try
		{
		$text = $this->Io->getString($name);
		}
	catch (IoRequestException $e)
		{
		}

	$this->addElement($name, '<label for="id'.$name.'">'.$description.'</label><br /><textarea id="id'.$name.'" name="'.$name.'" cols="'.$cols.'" rows="'.$rows.'">'.htmlspecialchars($text).'</textarea>');
	$this->descriptions[$name] = $description;

	$this->setFocus($name);

	return $name;
	}

protected function addText($name, $description = '', $text = '', $cols = 80)
	{
	try
		{
		$text = $this->Io->getString($name);
		}
	catch (IoRequestException $e)
		{
		}

	$this->addElement($name, '<label for="id'.$name.'">'.$description.'</label><br /><input id="id'.$name.'" type="text" name="'.$name.'" size="'.$cols.'" value="'.htmlspecialchars($text).'" />');
	$this->descriptions[$name] = $description;

	$this->setFocus($name);

	return $name;
	}

protected function addPassword($name, $description = '', $text = '', $cols = 80)
	{
	try
		{
		$text = $this->Io->getString($name);
		}
	catch (IoRequestException $e)
		{
		}

	$this->addElement($name,  '<label for="id'.$name.'">'.$description.'</label><br /><input id="id'.$name.'" type="password" name="'.$name.'" size="'.$cols.'" value="'.htmlspecialchars($text).'" />');
	$this->descriptions[$name] = $description;

	$this->setFocus($name);

	return $name;
	}

protected function requires($name)
	{
	if ($this->isSubmit() && $this->Io->isEmpty($name))
		{
		if (isset($this->elements[$name]))
			{
			$this->showWarning('Das Feld "'.$this->descriptions[$name].'" muß noch ausgefüllt werden!');
			// ich war das nicht ;-)
			$this->elements[$name] = preg_replace('/<\w+? /', '$0style="border-color:red" ', $this->elements[$name]);
			}
			else
			{
			$this->showWarning('Ich weiß, daß ich nichts weiß.');
			}
		}
	}

protected function setLength($name, $min, $max)
	{
	if ($this->isSubmit() && $this->Io->isRequest($name))
		{
		$length = strlen(htmlspecialchars($this->Io->getString($name)));

		if ($length > 0 && $length < $min)
			{
			$this->showWarning('Im Feld "'.$this->descriptions[$name].'" fehlen noch '.($min-$length).' Zeichen.');
			$this->elements[$name] = preg_replace('/<\w+? /', '$0style="border-color:yellow" ', $this->elements[$name]);
			}
		// Workaround for Bug #1 (will not Work for Markup!)
		elseif ($length > $max)
		//elseif ($this->Io->getLength($name) > $max)
			{
			$this->showWarning('Im Feld "'.$this->descriptions[$name].'" sind '.($length-$max).' Zeichen zuviel.');
			$this->elements[$name] = preg_replace('/<\w+? /', '$0style="border-color:orange" ', $this->elements[$name]);
			}
		}
	}

protected function getWarning()
	{
	return (empty($this->warning) ? '' : '<div class="warning">'.implode('<br />', $this->warning).'</div>');
	}

public function showWarning($text)
	{
	$this->warning[] = $text;
	}

protected function showFailure($text)
	{
	parent::showWarning($text);
	}

private function setFocus($name)
	{
	if(empty($this->focus))
		{
		$this->focus = $name;
		}
	}

}


?>