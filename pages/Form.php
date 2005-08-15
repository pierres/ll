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


public function prepare()
	{
	$this->setForm();

	if ($this->Io->isRequest('submit') && count($this->warning) == 0)
		{
		// weitere Prüfungen
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

protected function checkForm()
	{
	}

protected function showForm()
	{
	$body =
		$this->getWarning().'
		<form '.$this->encoding.' method="post" action="?page='.$this->getName().';id='.$this->Board->getId().'">
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
						<input accesskey="r" class="button" type="reset" name="reset" value="Zurüksetzen" />
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

protected function addFile($name, $description, $cols = 80)
	{
	$this->addElement($name, '<label for="id'.$name.'">'.$description.'</label><br /><input id="id'.$name.'" type="file" name="'.$name.'" size="'.$cols.'" />');
	$this->descriptions[$name] = $description;

	if(empty($this->focus))
		{
		$this->focus = $name;
		}

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

		$elements .= '<input type="radio" name="'.$name.'"'.($checked ? ' checked="checked"' : '').' value="'.$value.'" />'.$key;
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

	if(empty($this->focus))
		{
		$this->focus = $name;
		}

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

	if(empty($this->focus))
		{
		$this->focus = $name;
		}

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

	if(empty($this->focus))
		{
		$this->focus = $name;
		}

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
		if ($this->Io->getLength($name) > 0 && $this->Io->getLength($name) < $min)
			{
			$this->showWarning('Im Feld "'.$this->descriptions[$name].'" fehlen noch '.($min-$this->Io->getLength($name)).' Zeichen.');
			$this->elements[$name] = preg_replace('/<\w+? /', '$0style="border-color:yellow" ', $this->elements[$name]);
			}
		elseif ($this->Io->getLength($name) > $max)
			{
			$this->showWarning('Im Feld "'.$this->descriptions[$name].'" sind '.($this->Io->getLength($name)-$max).' Zeichen zuviel.');
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
}


?>