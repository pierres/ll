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

require ('pages/abstract/FormElements.php');

abstract class Form extends Page {

protected $hiddenElements	= array();
protected $inputElements	= array();
protected $buttonElements 	= array();

protected $warning		= array();

private $encoding 		= '';
private $method			= 'post';
private $params			= array();

private $isCheckSecurityToken 	= true;
private $isCheckAntiSpamHash 	= true;


public function prepare()
	{
	$this->setValue('meta.robots', 'noindex,nofollow');
	$this->setForm();

	if ($this->isCheckAntiSpamHash && !$this->User->isOnline())
		{
		$this->add(new AntiSpamElement());
		}

	if ($this->isCheckSecurityToken && $this->User->isOnline())
		{
		$this->add(new SecurityTokenElement($this->getName()));
		}

	if ($this->Input->Post->isString('submit') && count($this->warning) == 0)
		{
		$this->validateForm();
		if(count($this->warning) == 0)
			{
			$this->checkForm();
			if(count($this->warning) == 0)
				{
				$this->sendForm();
				}
			}
		}

	$this->showForm();
	}

abstract protected function setForm();

private function validateForm()
	{
	$valid = true;

	foreach (array_merge($this->hiddenElements, $this->inputElements) as $element)
		{
		if ($element instanceof ActiveFormElement)
			{
			try
				{
				$element->validate();
				}
			catch (FormElementException $e)
				{
				$this->showWarning($e->getMessage());
				$valid = false;
				}
			}
		}

	return $valid;
	}

public function setMethod($method)
	{
	$this->method = $method;
	}

public function setEncoding($encoding)
	{
	$this->encoding = $encoding;
	}

protected function isCheckSecurityToken($bool = true)
	{
	$this->isCheckSecurityToken = $bool;
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
	$body = '
		<form '.$this->encoding.' method="'.$this->method.'" action="'.$this->Output->createUrl($this->getName(), $this->params).'">
			<table>
				'.$this->getWarning().'
				'.implode("\n", $this->inputElements).'
				<tr>
					<th></th>
					<td>
						'.implode("\n", $this->hiddenElements).'
						'.implode("\n", $this->buttonElements).'
					</td>
				</tr>
			</table>
		</form>
		'.(!is_null(InputElement::getFocusElement()) ?
		'<script type="text/javascript">
			/* <![CDATA[ */
			document.getElementById("'.InputElement::getFocusElement()->getId().'").focus();
			/* ]]> */
		</script>' :'').'
		';

	$this->setBody($body);
	}

protected function add(FormElement $element)
	{
	if ($element instanceof HiddenElement)
		{
		$this->hiddenElements[] = $element;
		}
	elseif ($element instanceof ButtonElement)
		{
		$this->buttonElements[] = $element;
		}
	else
		{
		$this->inputElements[] = $element;
		}
	}

protected function setParam($key, $value)
	{
	$this->params[$key] = $value;
	}

abstract protected function sendForm();

protected function getWarning()
	{
	if (!empty($this->warning))
		{
		$warning =
			'
			<tr>
				<th>'.$this->L10n->getText('Warning').'</th>
				<td>
					<ul class="warning">
						<li>'.implode('</li><li>', $this->warning).'</li>
					</ul>
				</td>
			</tr>
			';
		}
	else
		{
		$warning = '';
		}

	return $warning;
	}

protected function showWarning($text)
	{
	$this->warning[] = $text;
	}

}


?>