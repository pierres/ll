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

require ('modules/DB.php');
require ('modules/User.php');
require ('modules/Board.php');
require ('modules/IOutput.php');

abstract class Page extends Modul implements IOutput {

private $variables = array();
private $userMenu = array();

private static $availablePages = array
	(
	'AdminCats' => 'pages/AdminCats.php',
	'AdminCatsDel' => 'pages/AdminCatsDel.php',
	'AdminCreateBoard' => 'pages/AdminCreateBoard.php',
	'AdminCss' => 'pages/AdminCss.php',
	'AdminDelBoard' => 'pages/AdminDelBoard.php',
	'AdminDeletedPosts' => 'pages/AdminDeletedPosts.php',
	'AdminDeletedThreads' => 'pages/AdminDeletedThreads.php',
	'AdminDesign' => 'pages/AdminDesign.php',
	'AdminForums' => 'pages/AdminForums.php',
	'AdminForumsDel' => 'pages/AdminForumsDel.php',
	'AdminForumsDelEx' => 'pages/AdminForumsDelEx.php',
	'AdminForumsEx' => 'pages/AdminForumsEx.php',
	'AdminForumsMerge' => 'pages/AdminForumsMerge.php',
	'AdminForumsMods' => 'pages/AdminForumsMods.php',
	'AdminForumsMove' => 'pages/AdminForumsMove.php',
	'AdminGlobalSettings' => 'pages/AdminGlobalSettings.php',
	'AdminGlobalForumsMove' => 'pages/AdminGlobalForumsMove.php',
	'AdminHtml' => 'pages/AdminHtml.php',
	'AdminRenameUser' => 'pages/AdminRenameUser.php',
	'AdminSettings' => 'pages/AdminSettings.php',
	'ChangeEmail' => 'pages/ChangeEmail.php',
	'ChangePassword' => 'pages/ChangePassword.php',
	'ChangePasswordKey' => 'pages/ChangePasswordKey.php',
	'CloseThread' => 'pages/CloseThread.php',
	'DelFile' => 'pages/DelFile.php',
	'DelPost' => 'pages/DelPost.php',
	'DelPrivateThread' => 'pages/DelPrivateThread.php',
	'DelThread' => 'pages/DelThread.php',
	'DeleteUser' => 'pages/DeleteUser.php',
	'EditPost' => 'pages/EditPost.php',
	'EditPrivatePost' => 'pages/EditPrivatePost.php',
	'EditPrivateThread' => 'pages/EditPrivateThread.php',
	'EditThread' => 'pages/EditThread.php',
	'ForgotPassword' => 'pages/ForgotPassword.php',
	'Forums' => 'pages/Forums.php',
	'FunnyDot' => 'pages/FunnyDot.php',
	'GetAttachment' => 'pages/GetAttachment.php',
	'GetAttachmentThumb' => 'pages/GetAttachmentThumb.php',
	'GetAvatar' => 'pages/GetAvatar.php',
	'GetCss' => 'pages/GetCss.php',
	'GetImage' => 'pages/GetImage.php',
	'GetOpenSearch' => 'pages/GetOpenSearch.php',
	'GetRecent' => 'pages/GetRecent.php',
	'InviteToPrivateThread' => 'pages/InviteToPrivateThread.php',
	'Login' => 'pages/Login.php',
	'Logout' => 'pages/Logout.php',
	'MarkAllAsRead' => 'pages/MarkAllAsRead.php',
	'MarkAsRead' => 'pages/MarkAsRead.php',
	'MarkupHelp' => 'pages/MarkupHelp.php',
	'MarkupTest' => 'pages/MarkupTest.php',
	'MovePosting' => 'pages/MovePosting.php',
	'MoveThread' => 'pages/MoveThread.php',
	'MyFiles' => 'pages/MyFiles.php',
	'MyProfile' => 'pages/MyProfile.php',
	'NewPost' => 'pages/NewPost.php',
	'NewPrivatePost' => 'pages/NewPrivatePost.php',
	'NewPrivateThread' => 'pages/NewPrivateThread.php',
	'NewThread' => 'pages/NewThread.php',
	'NotFound' => 'pages/NotFound.php',
	'Poll' => 'pages/Poll.php',
	'Postings' => 'pages/Postings.php',
	'PrivatePostings' => 'pages/PrivatePostings.php',
	'PrivateThreads' => 'pages/PrivateThreads.php',
	'QuotePost' => 'pages/QuotePost.php',
	'QuotePrivatePost' => 'pages/QuotePrivatePost.php',
	'Recent' => 'pages/Recent.php',
	'Register' => 'pages/Register.php',
	'Search' => 'pages/Search.php',
	'SearchResults' => 'pages/SearchResults.php',
	'ShowUser' => 'pages/ShowUser.php',
	'SplitThread' => 'pages/SplitThread.php',
	'StickThread' => 'pages/StickThread.php',
	'Threads' => 'pages/Threads.php',
	'UserRecent' => 'pages/UserRecent.php'
	);

public static function loadPage($name)
	{
	if (isset(self::$availablePages[$name]))
		{
		include_once(self::$availablePages[$name]);
		}
	else
		{
		throw new RuntimeException(sprintf('Page "%s" not found', $name), 0);
		}
	}

public function __construct()
	{
	$this->DB->connect(
		$this->Settings->getValue('sql_host'),
		$this->Settings->getValue('sql_user'),
		$this->Settings->getValue('sql_password'),
		$this->Settings->getValue('sql_database'));

	$this->variables['body']	 = '';
	$this->variables['title']	 = '';
	$this->variables['meta.robots']	 = 'index,follow';
	}

protected function setValue($key, $value)
	{
	$this->variables[$key] = $value;
	}

protected function getValue($key)
	{
	return $this->variables[$key];
	}

protected function setTitle($value)
	{
	$this->setValue('title', $value);
	}

protected function getTitle()
	{
	return $this->getValue('title');
	}

protected function setBody($value)
	{
	$this->setValue('body', $value);
	}

protected function showWarning($text)
	{
	$this->setValue('meta.robots', 'noindex,nofollow');
	$this->setTitle($this->L10n->getText('Warning'));
	$this->setBody('<div class="box"><div class="warning">'.$text.'</div></div>');
	$this->sendOutput();
	}

protected function showFailure($text)
	{
	$this->setValue('meta.robots', 'noindex,nofollow');
	$this->setTitle($this->L10n->getText('Failure'));
	$this->setBody('<div class="box"><div class="failure">'.$text.'</div></div>');
	$this->sendOutput();
	}

public function prepare()
	{
	$this->setTitle($this->L10n->getText('Warning'));
	$this->setBody($this->L10n->getText('no text'));
	}

private function getHead()
	{
	return '
		<meta name="robots" content="'.$this->getValue('meta.robots').'" />
		<title>'.$this->Board->getName().' - '.$this->getTitle().'</title>
		<link rel="stylesheet" media="screen" href="'.$this->Output->createUrl('GetCss').'" />
		<link rel="alternate" type="application/atom+xml" title="'.$this->L10n->getText('Recent topics').'" href="'.$this->Output->createUrl('GetRecent').'" />
		<link rel="search" type="application/opensearchdescription+xml" href="'.$this->Output->createUrl('GetOpenSearch').'" title="'.$this->Board->getName().'" />
		';
	}

private function getMainMenu()
	{
	$menu = '
		<ul>
			<li><a href="'.$this->Output->createUrl('Forums').'">'.$this->L10n->getText('Index').'</a></li>';


	if ($this->User->isOnline())
		{
		if ($this->User->isAdmin())
			{
			$menu .=
			'<li><a href="'.$this->Output->createUrl('AdminSettings').'">'.$this->L10n->getText('Administration').'</a></li>';
			}

		$menu .= '<li><a href="'.$this->Output->createUrl('Logout').'">'.$this->L10n->getText('Logout').'</a></li>';
		}
	else
		{
		$menu .=
			'<li><a href="'.$this->Output->createUrl('Register').'">'.$this->L10n->getText('Register').'</a></li>
			 <li><a href="'.$this->Output->createUrl('Login').'">'.$this->L10n->getText('Login').'</a></li>';
		}

	return $menu.'</ul>';
	}

private function getUserWelcome()
	{
	if ($this->User->isOnline())
		{
		return '<div>'.sprintf($this->L10n->getText('Logged in as %s.'), '<a href="'.$this->Output->createUrl('MyProfile').'"><strong>'.$this->User->getName().'</strong></a>').'</div>';
		}
	else
		{
		return '';
		}
	}

private function getUserMenu()
	{
	return '
		<ul>
			<li>'.implode('</li><li>', $this->userMenu).'</li>
			<li><a href="'.$this->Output->createUrl('Search').'">'.$this->L10n->getText('Search').'</a></li>
			<li><a href="'.$this->Output->createUrl('Recent').'">'.$this->L10n->getText('New posts').'</a></li>
		</ul>';
	}

protected function addUserMenuEntry($entry)
	{
	$this->userMenu[] = $entry;
	}

private function sendOutput()
	{
	$file = $this->Board->getHtml();
// 	$file = file_get_contents('ll.html');

	$this->variables['name'] = $this->Board->getName();
	$this->variables['main-menu'] = $this->getMainMenu();
	$this->variables['head'] = $this->getHead();
// 	$this->variables['page'] = $this->getName();
	$this->variables['user-welcome'] = $this->getUserWelcome();
	$this->variables['user-menu'] = $this->getUserMenu();

	/** FIXME Explizit aufrufen und body zuletzt ersetzen; sonst dorht rekursive Ersetzung */
	foreach ($this->variables as $key => $value)
		{
		$file = str_replace('<!-- '.$key.' -->', $value, $file);
		}

	$this->Output->writeOutput($file);
	}

public function show()
	{
	$this->sendOutput();
	}
}

?>
