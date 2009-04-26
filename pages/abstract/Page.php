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

require ('modules/DB.php');
require ('modules/User.php');
require ('modules/Board.php');
require ('modules/IOutput.php');
require ('modules/ICache.php');

abstract class Page extends Modul implements IOutput {

protected $variables = array();

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
	'GetLLCodes' => 'pages/GetLLCodes.php',
	'GetOpenSearch' => 'pages/GetOpenSearch.php',
	'GetRecent' => 'pages/GetRecent.php',
	'GetSmilies' => 'pages/GetSmilies.php',
	'Impressum' => 'pages/Impressum.php',
	'InviteToPrivateThread' => 'pages/InviteToPrivateThread.php',
	'Login' => 'pages/Login.php',
	'Logout' => 'pages/Logout.php',
	'MarkAllAsRead' => 'pages/MarkAllAsRead.php',
	'MarkAsRead' => 'pages/MarkAsRead.php',
	'MarkupTest' => 'pages/MarkupTest.php',
	'MovePosting' => 'pages/MovePosting.php',
	'MoveThread' => 'pages/MoveThread.php',
	'MyFavorites' => 'pages/MyFavorites.php',
	'MyFiles' => 'pages/MyFiles.php',
	'MyProfile' => 'pages/MyProfile.php',
	'NewPost' => 'pages/NewPost.php',
	'NewPrivatePost' => 'pages/NewPrivatePost.php',
	'NewPrivateThread' => 'pages/NewPrivateThread.php',
	'NewThread' => 'pages/NewThread.php',
	'NotFound' => 'pages/NotFound.php',
	'Poll' => 'pages/Poll.php',
	'Postings' => 'pages/Postings.php',
	'Privacy' => 'pages/Privacy.php',
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
		throw new RuntimeException('Seite '.$name.' wurde nicht gefunden!', 0);
		}
	}

public function __construct()
	{
	$this->DB->connect(
		$this->Settings->getValue('sql_user'),
		$this->Settings->getValue('sql_password'),
		$this->Settings->getValue('sql_database'));

	$this->variables['body']	 = '';
	$this->variables['title']	 = '';
	$this->variables['meta.robots']	 = 'index,follow';
	}

protected function getMenu()
	{
	$menu =	'<div id="brd-navlinks"><ul>';
	
	$menu .= '
		<li id="navindex"><a href="'.$this->Output->createUrl('Forums').'"><span>Index</span></a></li>

		<li id="navsearch"><a href="'.$this->Output->createUrl('Search').'"><span>Search</span></a></li>';


	if ($this->User->isOnline())
		{
		$menu .=
			'<li id="navprofile"><a href="'.$this->Output->createUrl('MyProfile').'"><span>My profile</span></a></li>';

		if ($this->User->isAdmin())
			{
			$menu .=
			'<li id="navadmin"><a href="'.$this->Output->createUrl('AdminSettings').'"><span>Administration</span></a></li>';
			}

		$menu .= '<li id="navlogout"><a href="'.$this->Output->createUrl('Logout').'"><span>Logout</span></a></li>';
		}
	else
		{
		$menu .=
			'<li id="navregister"><a href="'.$this->Output->createUrl('Register').'"><span>Register</span></a></li>

			<li id="navlogin"><a href="'.$this->Output->createUrl('Login').'"><span>Login</span></a></li>';
		}

	return $menu.'</ul></div>';
	}

public function setValue($key, $value)
	{
	$this->variables[$key] = $value;
	}

public function getValue($key)
	{
	return $this->variables[$key];
	}

public function setTitle($value)
	{
	$this->setValue('title', $value);
	}

public function getTitle()
	{
	return $this->getValue('title');
	}

public function setBody($value)
	{
	$this->setValue('body', $value);
	}

protected function showWarning($text)
	{
	$this->setValue('meta.robots', 'noindex,nofollow');
	$this->setTitle('Warnung');
	$this->setBody('<div class="warn">'.$text.'</div>');
	$this->sendOutput();
	}

protected function showFailure($text)
	{
	$this->setValue('meta.robots', 'noindex,nofollow');
	$this->setTitle('Fehler');
	$this->setBody('<div class="warn">'.$text.'</div>');
	$this->sendOutput();
	}

public function prepare()
	{
	$this->setTitle('Warnung');
	$this->setBody('kein Text');
	}

private function getHead()
	{
	return '<meta name="robots" content="'.$this->getValue('meta.robots').'" />
		<title>'.$this->getTitle().'</title>
		<!-- <link rel="stylesheet" media="screen" href="'.$this->Output->createUrl('GetCss').'" /> -->
		<link rel="stylesheet" media="screen" href="oxygen.css" />
		<link rel="alternate" type="application/atom+xml" title="Aktuelle Themen im Forum" href="'.$this->Output->createUrl('GetRecent').'" />
		<link rel="search" type="application/opensearchdescription+xml" href="'.$this->Output->createUrl('GetOpenSearch').'" title="'.$this->Board->getName().'" />';
	}

private function getVisit()
	{
	$menu = '<div id="brd-visit">
			<ul>
				<li id="vs-searchnew"><a href="'.$this->Output->createUrl('Recent').'" title="Lists topics that have new posts since your last visit">New posts</a></li>';
	
	if ($this->User->isOnline())
		{
		$menu .= '
			<li id="vs-markread"><a href="'.$this->Output->createUrl('MarkAllAsRead').'">Mark all topics as read</a></li>
			</ul><p>
				<span id="vs-logged">Logged in as <strong>'.$this->User->getName().'</strong>.</span>
				<span id="vs-message">Last visit: <strong>'.$this->L10n->getDateTime($this->User->getLastUpdate()).'</strong></span>
			</p>';
		}
	else
		{
		$menu .= '</ul><p></p>';
		}
	
	return $menu.'</div>';
	}

private function sendOutput()
	{
// 	$file = $this->Board->getHtml();
	$file = file_get_contents('oxygen.html');

	$this->variables['content-type'] = $this->Output->getContentType();
	$this->variables['id'] = $this->Board->getId();
	$this->variables['name'] = $this->Board->getName();
	$this->variables['description'] = $this->Board->getDescription();
	$this->variables['menu'] = $this->getMenu();
	$this->variables['head'] = $this->getHead();
	$this->variables['page'] = $this->getName();
	$this->variables['visit'] = $this->getVisit();

// 	if ($this->User->isOnline())
// 		{
// 		$this->variables['user'] = $this->User->getName();
// 		}

// 	$this->setBody($this->getValue('body').
// 		'
// 		<div style="text-align:right;font-size:10px;margin-top:5px;">
// 			<a href="'.$this->Output->createUrl('Privacy').'">Datenschutz</a> ::
// 			<a href="'.$this->Output->createUrl('Impressum').'">Impressum</a>
// 		</div>
// 		<div style="text-align:right;font-size:10px;margin-top:30px;">
// 			Powered by <a href="http://www.laber-land.de">LL 4.0</a><br />
// 			&copy; Copyright 2002&ndash;2009 Pierre Schmitz
// 		</div>
// 		');

	$this->setValue('about',
	'<div id="brd-about">
		<p id="copyright">Powered by <strong><a href="http://www.laber-land.de/">LL 4.0</a></strong></p>
	</div>');


// 	if ($this->Settings->getValue('debug') && function_exists('xdebug_time_index'))
// 		{
// 		$this->setValue('debug',
// 			'<div style="text-align:left;font-size:10px;font-family:monospace;margin-top:3px;">
// 			Ausführungszeit:&nbsp;&nbsp;&nbsp;'.xdebug_time_index().' s<br />
// 			Speicherverbrauch:&nbsp;'.(xdebug_peak_memory_usage()/1024).' KByte
// 			</div>'
// 			);
// 		}

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