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

abstract class FormElement {

	private static $elementCounter = 0;
	private $elementId = 0;

	public function __construct()
		{
		$this->elementId = self::$elementCounter++;
		}

	protected function getNextElementId()
		{
		return 'id-'.$this->elementId;
		}

	abstract public function __toString();

}

class ButtonElement extends FormElement {

	protected $name = '';
	protected $label = '';

	public function __construct($name, $label)
		{
		parent::__construct();
		$this->name = $name;
		$this->label = htmlspecialchars($label);
		}

	public function __toString()
		{
		return '<span class="submit"><input type="submit" name="'.$this->name.'" value="'.$this->label.'" /></span>';
		}
}

class SubmitButtonElement extends ButtonElement {

	public function __construct($label)
		{
		parent::__construct('submit', $label);
		}
}

class ResetButtonElement extends ButtonElement {

	public function __construct($label)
		{
		parent::__construct('reset', $label);
		}

	public function __toString()
		{
		return '<span class="submit"><input type="reset" name="'.$this->name.'" value="'.$this->label.'" /></span>';
		}
}

class FormElementException extends RuntimeException {

	function __construct($message)
		{
		parent::__construct($message, 0);
		}
}

class PassiveFormElement extends FormElement {

	protected $content = '';

	public function __construct($content)
		{
		$this->content = $content;
		}

	public function __toString()
		{
		return $this->content;
		}
}

class DividerElement extends PassiveFormElement {

	public function __construct()
		{
		parent::__construct('</fieldset><fieldset class="frm-set">');
		}
}

class LabeledElement extends PassiveFormElement {

	private $label = '';

	public function __construct($label, $content)
		{
		$this->label = $label;
		parent::__construct($content);
		}

	public function __toString()
		{
		return
		'<div class="frm-fld">
			<label for="'.$this->getNextElementId().'">
				<span class="fld-label">'.$this->label.'</span>
				<span class="fld-input">'.$this->content.'</span>
			</label>
		</div>';
		}
}

abstract class ActiveFormElement extends FormElement {

	protected $name = '';
	protected $label = '';
	protected $value = '';
	protected $required = true;
	protected $minLength = 1;
	protected $maxLength = 65535;
	protected $Input = null;

	public function __construct($name, $value)
		{
		parent::__construct();
		$this->name = $name;
		$this->label = $name;
		$this->value = htmlspecialchars($value);
		$this->Input = Modul::__get('Input');
		}

	public function setRequired($required)
		{
		$this->required = $required;
		}

	public function setMinLength($minLength)
		{
		$this->minLength = $minLength;
		}

	public function setMaxLength($maxLength)
		{
		$this->maxLength = $maxLength;
		}

	public function validate()
		{
		if ($this->required && $this->Input->Post->isEmptyString($this->name))
			{
			throw new FormElementException('Das Feld "'.$this->label.'" darf nicht leer sein.');
			}

		$currentLength = $this->Input->Post->getLength($this->name);

		if ($currentLength < $this->minLength)
			{
			throw new FormElementException('Im Feld "'.$this->label.'" fehlen noch '.($this->minLength - $currentLength).' Zeichen.');
			}
		elseif ($currentLength > $this->maxLength)
			{
			throw new FormElementException('Im Feld "'.$this->label.'" sind '.($currentLength - $this->maxLength).' Zeichen zuviel.');
			}
		}
}

class HiddenElement extends ActiveFormElement {

	public function __toString()
		{
		return '<input type="hidden" name="'.$this->name.'" value="'.$this->value.'" />';
		}
}

class SecurityTokenElement extends HiddenElement {

	protected $minLength = 40;
	protected $maxLength = 40;
	private $page = '';
	private $User = null;

	public function __construct($page)
		{
		$this->page = $page;
		$this->User = Modul::__get('User');
		parent::__construct('SecurityToken', '');
		}

	public function __toString()
		{
		$this->value = sha1($this->page.$this->User->getNextSecurityToken());
		return parent::__toString();
		}

	public function validate()
		{
		parent::validate();

		try
			{
			$token = $this->Input->Post->getHex('SecurityToken');
			}
		catch (RequestException $e)
			{
			throw new FormElementException($e->getMessage());
			}

		if (sha1($this->page.$this->User->getSecurityToken()) != $token)
			{
			throw new FormElementException('Sicherheitswarnung: Ungültiger Schlüssel!');
			}
		else
			{
			$this->User->getNextSecurityToken();			
			}
		}
}

abstract class InputElement extends ActiveFormElement {

	protected $help = '';

	public function __construct($name, $value, $label)
		{
		parent::__construct($name, $value);
		$this->label = $label;

		$this->value = $this->Input->Post->getHtml($name, $value);
		}

	protected function formatOutput($input)
		{
		return
		'<div class="frm-fld'.($this->required ? ' required' : '').'">
			<label for="'.$this->getNextElementId().'">
				<span class="fld-label">'.$this->label.'</span>
				<span class="fld-input">'.$input.'</span>
				'.($this->required ? '<em class="req-text">(Required)</em>' : '').'
				'.(!empty($this->help) ? '<span class="fld-help">'.$this->help.'</span>' : '').'
			</label>
		</div>';
		}

	public function setHelp($help)
		{
		$this->help = $help;
		}
}

class TextInputElement extends InputElement {

	protected $size = 80;

	public function setSize($size)
		{
		$this->size = $size;
		}

	public function __toString()
		{
		return $this->formatOutput('<input id="'.$this->getNextElementId().'" type="text" name="'.$this->name.'" size="'.$this->size.'" value="'.$this->value.'" />');
		}
}

class AntiSpamElement extends TextInputElement {

	private $Settings = null;
	private $Output = null;
	protected $minLength = 4;
	protected $maxLength = 4;
	protected $size = 4;

	public function __construct()
		{
		parent::__construct('AntiSpamHash', '', 'CAPTCHA');
		$this->Settings = Modul::__get('Settings');
		$this->Output = Modul::__get('Output');

		$this->Output->setCookie('AntiSpamTime',  $this->Input->getTime());
		$wantedHash = substr(sha1($this->Input->getTime().$this->Settings->getValue('antispam_hash')), 0, 4);

		$this->help = 'Please type in the following code: <strong>'.$wantedHash.'</strong>';
		}

	protected function formatOutput($input)
		{
		// we need this workaround because some browsers
		// don't even load elemnts that have "display:none"
		return '<div style="background-image:url('.$this->Output->createUrl('FunnyDot').');visibility:hidden;position:absolute;z-index:-1">'.parent::formatOutput($input).'</div>';
		}

	public function validate()
		{
		// Cookie should overwrite Post values
		isset($_COOKIE['AntiSpamHash']) && $_POST['AntiSpamHash'] = $_COOKIE['AntiSpamHash'];

		parent::validate();

		try
			{
			$hash = $this->Input->Post->getHex('AntiSpamHash');
			}
		catch (RequestException $e)
			{
			throw new FormElementException($e->getMessage());
			}

		try
			{
			$time = $this->Input->Cookie->getInt('AntiSpamTime');
			}
		catch (RequestException $e)
			{
			throw new FormElementException($e->getMessage());
			}

		if ($hash != substr(sha1($time.$this->Settings->getValue('antispam_hash')), 0, 4))
			{
			throw new FormElementException('Fehlerhafte Formulardaten empfangen. Überprüfe den Sicherheitscode!');
			}

		if ($this->Input->getTime() - $time > $this->Settings->getValue('antispam_timeout'))
			{
			throw new FormElementException('Deine Zeit ist abgelaufen. Schicke das Formular bitte erneut ab, und zwar innherlab der nächsten '.$this->Settings->getValue('antispam_timeout').' Sekunden.');
			}
		elseif ($this->Input->getTime() - $time < $this->Settings->getValue('antispam_wait'))
			{
			throw new FormElementException('Du warst zu schnell. Schicke das Formular bitte erneut ab. Laße Dir diesmal mindestens '.$this->Settings->getValue('antispam_wait').' Sekunden Zeit.');
			}
		}
}

class PasswordInputElement extends TextInputElement {

	public function __construct($name, $label)
		{
		parent::__construct($name, '', $label);
		}

	public function __toString()
		{
		return $this->formatOutput('<input id="'.$this->getNextElementId().'" type="password" name="'.$this->name.'" size="'.$this->size.'" value="" />');
		}
}

class TextareaInputElement extends InputElement {

	private $columns = 80;
	private $rows = 20;

	public function setColumns($columns)
		{
		$this->columns = $columns;
		}

	public function setRows($rows)
		{
		$this->rows = $rows;
		}

	public function __toString()
		{
		return $this->formatOutput('<textarea id="'.$this->getNextElementId().'" name="'.$this->name.'" cols="'.$this->columns.'" rows="'.$this->rows.'">'.$this->value.'</textarea>');
		}
}

class FileInputElement extends InputElement {

	private $size = 20;
	protected $required = false;

	public function setSize($size)
		{
		$this->size = $size;
		}

	public function __toString()
		{
		return $this->formatOutput('<input id="'.$this->getNextElementId().'" type="file" name="'.$this->name.'" size="'.$this->size.'" value="'.$this->value.'" />');
		}

	/** @TODO */
	public function validate()
		{
		}
}

class CheckboxInputElement extends InputElement {

	private $checked = false;
	protected $minLength = 0;
	protected $maxLength = 2;

	public function __construct($name, $label)
		{
		parent::__construct($name, '1', $label);

		if ($this->Input->Post->isString($name))
			{
			$this->setChecked();
			}
		}

	public function setChecked($checked = true)
		{
		$this->checked = $checked;
		}

	public function __toString()
		{
		return $this->formatOutput('<input type="checkbox" id="'.$this->getNextElementId().'" name="'.$this->name.'" value="'.$this->value.'" '.($this->checked ? ' checked="checked"' : '').' />');
		}
}

class RadioInputElement extends InputElement {

	private $options = array();
	private $checked = '';

	public function __construct($name, $label)
		{
		parent::__construct($name, '', $label);
		$this->setChecked($this->Input->Post->getString($name, ''));
		}

	public function addOption($label, $value)
		{
		$this->options[htmlspecialchars($value)] = $label;
		}

	public function addCheckedOption($label, $value)
		{
		$this->addOption($label, $value);
		$this->setChecked($value);
		}

	public function setChecked($value)
		{
		$this->checked = htmlspecialchars($value);
		}

	public function __toString()
		{
		$output = '';
		$optionCount = 0;

		foreach ($this->options as $value => $label)
			{
			$output .=
				'<div class="radbox">
					<label for="'.$this->getNextElementId().'-'.$optionCount.'">
						<input type="radio" name="'.$this->name.'"'.($value == $this->checked ? ' checked="checked"' : '').' value="'.$value.'" id="'.$this->getNextElementId().'-'.$optionCount.'" />
						'.$label.'
					</label>
				</div>';

			$optionCount++;
			}

		return $this->formatOutput($output);
		}
}

class SelectInputElement extends InputElement {

	private $options = array();
	private $selected = array();
	private $size = 5;
	private $multiple = false;

	public function __construct($name, $label)
		{
		// The constructor of InputElement fails on Arrays
		ActiveFormElement::__construct($name, '', $label);
		$this->label = $label;
		}

	public function addOption($label, $value)
		{
		$this->options[htmlspecialchars($value)] = $label;
		}

	public function addSelectedOption($label, $value)
		{
		$this->addOption($label, $value);
		$this->setSelected($value);
		}

	public function setSelected($value)
		{
		$this->selected[htmlspecialchars($value)] = true;
		}

	public function setSize($size)
		{
		$this->size = $size;
		}

	public function setMultiple($multiple = true)
		{
		$this->multiple = $multiple;
		}

	public function __toString()
		{
		if ($this->multiple)
			{
			$output = '<select name="'.$this->name.'[]" size="'.$this->size.'" multiple="multiple">';

			try
				{
				$inputArray = $this->Input->Post->getArray($this->name);
				$this->selected = array();
				foreach ($inputArray as $inputValue)
					{
					$this->setSelected($inputValue);
					}
				}
			catch (RequestException $e)
				{
				}
			}
		else
			{
			$output = '<select name="'.$this->name.'" size="'.$this->size.'">';

			try
				{
				$selectedValue = $this->Input->Post->getString($this->name);
				$this->setSelected($selectedValue);
				}
			catch (RequestException $e)
				{
				}
			}

		foreach ($this->options as $value => $label)
			{
			$output .=
				'<option'.(isset($this->selected[htmlspecialchars($value)]) ? ' selected="selected"' : '').' value="'.$value.'">
					'.$label.'
				</option>';
			}

		$output .= '</select>';

		return $this->formatOutput($output);
		}

	public function validate()
		{
		if ($this->multiple)
			{
			try
				{
				foreach ($this->Input->Post->getArray($this->name) as $inputValue)
					{
					$currentLength = strlen($inputValue);

					if ($currentLength < $this->minLength)
						{
						throw new FormElementException('Im Feld "'.$this->label.'" fehlen noch '.($this->minLength - $currentLength).' Zeichen.');
						}
					elseif ($currentLength > $this->maxLength)
						{
						throw new FormElementException('Im Feld "'.$this->label.'" sind '.($currentLength - $this->maxLength).' Zeichen zuviel.');
						}
					}
				}
			catch (RequestException $e)
				{
				if ($this->required)
					{
					throw new FormElementException('Das Feld "'.$this->label.'" darf nicht leer sein.');
					}
				}
			}
		else
			{
			parent::validate();
			}
		}
}

?>