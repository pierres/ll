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
require_once 'PHPUnit/Framework/TestCase.php';
require_once ('../modules/Settings.php');
require_once ('../modules/Functions.php');

function __autoload($class)
	{
	LLTestCase::loadModul($class);
	}

abstract class Modul extends PHPUnit_Framework_TestCase{

private static $loadedModules = array();

private static $availableModules = array
	(
	'AdminCats' => 'pages/AdminCats.php',
	'AdminCatsDel' => 'pages/AdminCatsDel.php',
	'AdminCss' => 'pages/AdminCss.php',
	'AdminDesign' => 'pages/AdminDesign.php',
	'AdminForums' => 'pages/AdminForums.php',
	'AdminForumsDel' => 'pages/AdminForumsDel.php',
	'AdminForumsDelEx' => 'pages/AdminForumsDelEx.php',
	'AdminForumsEx' => 'pages/AdminForumsEx.php',
	'AdminForumsMods' => 'pages/AdminForumsMods.php',
	'AdminForumsMove' => 'pages/AdminForumsMove.php',
	'AdminGlobalSettings' => 'pages/AdminGlobalSettings.php',
	'AdminHtml' => 'pages/AdminHtml.php',
	'AdminIndex' => 'pages/AdminIndex.php',
	'AdminSettings' => 'pages/AdminSettings.php',
	'ChangeEmail' => 'pages/ChangeEmail.php',
	'ChangePassword' => 'pages/ChangePassword.php',
	'ChangePasswordKey' => 'pages/ChangePasswordKey.php',
	'CloseThread' => 'pages/CloseThread.php',
	'Contact' => 'pages/Contact.php',
	'DelFile' => 'pages/DelFile.php',
	'DelPost' => 'pages/DelPost.php',
	'DelThread' => 'pages/DelThread.php',
	'DeleteUser' => 'pages/DeleteUser.php',
	'DeletedPosts' => 'pages/DeletedPosts.php',
	'DeletedThreads' => 'pages/DeletedThreads.php',
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
	'GetImage' => 'pages/GetImage.php',
	'Impressum' => 'pages/Impressum.php',
	'InviteToPrivateThread' => 'pages/InviteToPrivateThread.php',
	'Jabber' => 'pages/Jabber.php',
	'Login' => 'pages/Login.php',
	'Logout' => 'pages/Logout.php',
	'MarkAllAsRead' => 'pages/MarkAllAsRead.php',
	'MarkAsRead' => 'pages/MarkAsRead.php',
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
	'Portal' => 'pages/Portal.php',
	'Postings' => 'pages/Postings.php',
	'PrivatePostings' => 'pages/PrivatePostings.php',
	'PrivateThreads' => 'pages/PrivateThreads.php',
	'QuotePost' => 'pages/QuotePost.php',
	'QuotePrivatePost' => 'pages/QuotePrivatePost.php',
	'Recent' => 'pages/Recent.php',
	'Register' => 'pages/Register.php',
	'RegisterBoard' => 'pages/RegisterBoard.php',
	'Search' => 'pages/Search.php',
	'ShowUser' => 'pages/ShowUser.php',
	'SiteMap' => 'pages/SiteMap.php',
	'SplitThread' => 'pages/SplitThread.php',
	'StickThread' => 'pages/StickThread.php',
	'Threads' => 'pages/Threads.php',
	'UserList' => 'pages/UserList.php',
	'UserRecent' => 'pages/UserRecent.php',
	'AdminForm' => 'pages/abstract/AdminForm.php',
	'AdminPage' => 'pages/abstract/AdminPage.php',
	'Form' => 'pages/abstract/Form.php',
	'GetFile' => 'pages/abstract/GetFile.php',
	'Page' => 'pages/abstract/Page.php',
	'AdminFunctions' => 'modules/AdminFunctions.php',
	'Board' => 'modules/Board.php',
	'DB' => 'modules/DB.php',
	'Exceptions' => 'modules/Exceptions.php',
	'Functions' => 'modules/Functions.php',
	'IOutput' => 'modules/IOutput.php',
	'Io' => 'modules/Io.php',
	'Log' => 'modules/Log.php',
	'Mail' => 'modules/Mail.php',
	'Markup' => 'modules/Markup.php',
	'Modul' => 'modules/Modul.php',
	'Settings' => 'modules/Settings.php',
	'Stack' => 'modules/Stack.php',
	'ThreadList' => 'modules/ThreadList.php',
	'UnMarkup' => 'modules/UnMarkup.php',
	'User' => 'modules/User.php'
	);

public static function loadModul($name)
	{
	if (isset(self::$availableModules[$name]))
		{
		include_once('../'.self::$availableModules[$name]);
		}
	else
		{
		throw new RuntimeException('Modul '.$name.' wurde nicht gefunden!', 0);
		}
	}

public static function __get($name)
	{
	if (!isset(self::$loadedModules[$name]))
		{
		self::loadModul($name);
		$new = new $name();
		self::$loadedModules[$name] = &$new;
		return $new;
		}
	else
		{
		return self::$loadedModules[$name];
		}
	}

public static function __set($name, &$object)
	{
	if (!isset(self::$loadedModules[$name]))
		{
		self::$loadedModules[$name] = $object;
		return $object;
		}
	else
		{
		return self::$loadedModules[$name];
		}
	}

public function getName()
	{
	return get_class($this);
	}

}

abstract class LLTestCase extends Modul{


public function setUp()
	{
	}

public function tearDown()
	{
	}

}

?>
