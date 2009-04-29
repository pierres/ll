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

require ('pages/abstract/FormElements.php');

abstract class Form extends Page {

protected $hiddenElements	= array();
protected $inputElements	= array();
protected $buttonElements 	= array();

protected $warning		= array();

protected $focus		= '';

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
	<div id="brd-main" class="main">

		<h1><span>'.$this->getTitle().'</span></h1>

		<div class="main-head">
			<h2><span>'.$this->getTitle().'</span></h2>
		</div>

		<div class="main-content frm">
		'.$this->getWarning().'
		<form '.$this->encoding.' method="'.$this->method.'" action="'.$this->Output->createUrl($this->getName(), $this->params).'" class="frm-form">
			<div class="hidden">
				'.implode(' ', $this->hiddenElements).'
			</div>
			<fieldset class="frm-set set1">
				'.implode(' ', $this->inputElements).'
			</fieldset>
			<div class="frm-buttons">
				'.implode(' ', $this->buttonElements).'
			</div>
		</form>
		<script type="text/javascript">
			/* <![CDATA[ */
			document.getElementById("'.$this->focus.'").focus();
			/* ]]> */
		</script>
		</div>
	</div>';

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

// protected function isSubmit()
// 	{
// 	foreach ($this->buttonElements as $name => $value)
// 		{
// 		if ($this->Input->Post->isString($name))
// 			{
// 			return true;
// 			}
// 		}
// 
// 	return false;
// 	}

protected function getWarning()
	{
	if (!empty($this->warning))
		{
		$warning =
			'
			<div id="req-msg" class="frm-error">
				<h3 class="warn">'.$this->L10n->getText('Warning').'</h3>
				<ul>
					<li>'.implode('</li><li>', $this->warning).'</li>
				</ul>
			</div>
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

// private function setFocus()
// 	{
// 	if(empty($this->focus))
// 		{
// 		$this->focus = FormElement::getNextElementId();
// 		}
// 	}

}


?>