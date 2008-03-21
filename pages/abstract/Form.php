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

private $tail		= '';

private $isCheckSecurityToken = true;
private $isCheckAntiSpamHash = true;


public function prepare()
	{
	$this->setValue('meta.robots', 'noindex,nofollow');
	$this->setForm();

	if ($this->Io->isRequest('submit') && count($this->warning) == 0)
		{
		$this->checkAntiSpamHash();
		$this->checkSecurityToken();
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

private function addSecurityToken()
	{
	if ($this->isCheckSecurityToken && $this->User->isOnline())
		{
		$this->addHidden('SecurityToken', sha1($this->getName().$this->User->getNextSecurityToken()));
		}
	}

protected function isCheckSecurityToken($bool = true)
	{
	$this->isCheckSecurityToken = $bool;
	}

private function checkSecurityToken()
	{
	if ($this->isCheckSecurityToken && $this->User->isOnline())
		{
		try
			{
			$token = $this->Io->getHex('SecurityToken');
			}
		catch (IoRequestException $e)
			{
			$this->showFailure('Sicherheitsverletzung: Aktion nicht erlaubt!');
			}

		if (sha1($this->getName().$this->User->getSecurityToken()) != $token)
			{
			$this->showWarning('Sicherheitswarnung: Ungültiger Schlüssel!');
			}
		else
			{
			// dies verwirft das aktuelle Token
			$this->User->getNextSecurityToken();
			}
		}
	}

private function checkAntiSpamHash()
	{
	if ($this->isCheckAntiSpamHash && !$this->User->isOnline())
		{
		$now = time();

		try
			{
			$time = $this->Io->getInt('AntiSpamTime');
			$hash = $this->Io->getHex('AntiSpamHash');
			}
		catch (IoRequestException $e)
			{
			try
				{
				$time = $this->Io->getInt('AlternateAntiSpamTime');

				if ($this->Io->isEmpty('AlternateAntiSpamHashHead'))
					{
					return $this->showWarning('Bitte den Sicherheitscode bestätigen!');
					}

				$hash = $this->Io->getHex('AlternateAntiSpamHashHead').$this->Io->getHex('AlternateAntiSpamHashTail');
				}
			catch (IoRequestException $e)
				{
				sleep($this->Settings->getValue('antispam_wait'));
				$this->showFailure('Ungültige Formulardaten empfangen. Stelle sicher, daß Cookies für diese Domain angenommen werden.');
				}
			}

		if ($hash != sha1($time.$this->Settings->getValue('antispam_hash')))
			{
			sleep($this->Settings->getValue('antispam_wait'));
			$this->showFailure('Fehlerhafte Formulardaten empfangen. Überprüfe den Sicherheitscode!');
			}

		if ($now - $time > $this->Settings->getValue('antispam_timeout'))
			{
			$this->showWarning('Deine Zeit ist abgelaufen. Schicke das Formular bitte erneut ab, und zwar innherlab der nächsten '.$this->Settings->getValue('antispam_timeout').' Sekunden.');
			}
		elseif ($now - $time < $this->Settings->getValue('antispam_wait'))
			{
			sleep($this->Settings->getValue('antispam_wait'));
			$this->showWarning('Du warst zu schnell. Schicke das Formular bitte erneut ab. Laße Dir diesmal mindestens '.$this->Settings->getValue('antispam_wait').' Sekunden Zeit.');
			}
		}
	}

private function addAntiSpamHash()
	{
	if ($this->isCheckAntiSpamHash && !$this->User->isOnline())
		{
		$this->addAlternateAntiSpamHash();

		$this->appendOutput('<div style="background-image:url(?page=FunnyDot);background-repeat:no-repeat;visibility:hidden;width:1px;height:1px;">&nbsp;</div>');
		}
	}

private function addAlternateAntiSpamHash()
	{
	try
		{
		$hashHead = $this->Io->getHex('AlternateAntiSpamHashHead');

		if ($this->Io->isEmptyString('AlternateAntiSpamHashHead'))
			{
			throw new IoRequestException('AlternateAntiSpamHashHead');
			}

		$time = $this->Io->getInt('AlternateAntiSpamTime');
		}
	catch (IoRequestException $e)
		{
		$hashHead = '';
		$time = time();
		}

	$hash = sha1($time.$this->Settings->getValue('antispam_hash'));
	$this->Io->setCookie('AlternateAntiSpamTime', $time);
	$this->Io->setCookie('AlternateAntiSpamHashTail', substr($hash, 4));


	$name = 'AlternateAntiSpamHashHead';
	$description = 'Sicherheitscode bestätigen: <strong>'.substr($hash, 0, 4).'</strong>';

	$this->addElement($name, '<div style="display:none;"><label for="'.$this->getNextElementId().'">'.$description.'</label><br /><input id="'.$this->getNextElementId().'" type="text" name="'.$name.'" size="4" value="'.$hashHead.'" /></div>');
	$this->descriptions[$name] = $description;
	}

protected function isCheckAntiSpamHash($bool = true)
	{
	$this->isCheckAntiSpamHash = $bool;
	}

protected function checkForm()
	{
	}

protected function showForm()
	{
	$this->addAntiSpamHash();
	$this->addSecurityToken();

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
			/* <![CDATA[ */
			document.getElementById("'.$this->focus.'").focus();
			/* ]]> */
		</script>
		'.$this->tail;

	$this->setValue('body', $body);
	}

protected function appendOutput($text)
	{
	$this->tail .= $text;
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

private function getNextElementId()
	{
	return 'id-'.count($this->elements);
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
	$this->request .= ';fileUploadCheck'.$name.'=1';

	if ($this->Io->isRequest('fileUploadCheck'.$name) && empty($_POST) && empty($_FILES))
		{
		$this->showWarning('Die Datei ist größer als '.ini_get('upload_max_filesize').'Byte.');
		}

	$this->setFocus();
	$this->addElement($name, '<label for="'.$this->getNextElementId().'">'.$description.'</label><br /><input id="'.$this->getNextElementId().'" type="file" name="'.$name.'" size="'.$cols.'" />');
	$this->descriptions[$name] = $description;

	$this->encoding = 'enctype="multipart/form-data"';

	return $name;
	}

protected function addCheckbox($name, $description, $checked = false)
	{
	if ($this->isSubmit())
		{
		$checked = $this->Io->isRequest($name);
		}

	$this->addElement($name, '<input type="checkbox" id="'.$this->getNextElementId().'" name="'.$name.'"'.($checked ? ' checked="checked"' : '').' /><label for="'.$this->getNextElementId().'">'.$description.'</label>');
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

	$this->setFocus();
	$this->addElement($name, '<label for="'.$this->getNextElementId().'">'.$description.'</label><br /><textarea id="'.$this->getNextElementId().'" name="'.$name.'" cols="'.$cols.'" rows="'.$rows.'">'.htmlspecialchars($text).'</textarea>');
	$this->descriptions[$name] = $description;

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

	$this->setFocus();
	$this->addElement($name, '<label for="'.$this->getNextElementId().'">'.$description.'</label><br /><input id="'.$this->getNextElementId().'" type="text" name="'.$name.'" size="'.$cols.'" value="'.htmlspecialchars($text).'" />');
	$this->descriptions[$name] = $description;

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

	$this->setFocus();
	$this->addElement($name,  '<label for="'.$this->getNextElementId().'">'.$description.'</label><br /><input id="'.$this->getNextElementId().'" type="password" name="'.$name.'" size="'.$cols.'" value="'.htmlspecialchars($text).'" />');
	$this->descriptions[$name] = $description;

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

protected function showWarning($text)
	{
	$this->warning[] = $text;
	}

private function setFocus()
	{
	if(empty($this->focus))
		{
		$this->focus = $this->getNextElementId();
		}
	}

}


?>