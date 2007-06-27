<?php

require ('modules/DB.php');
require ('modules/User.php');
require ('modules/Board.php');
require ('modules/IOutput.php');

abstract class Page extends Modul implements IOutput{

protected $variables = array();

private static $availablePages = array
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
	'AllRecent' => 'pages/AllRecent.php',
	'ChangeEmail' => 'pages/ChangeEmail.php',
	'ChangePassword' => 'pages/ChangePassword.php',
	'ChangePasswordKey' => 'pages/ChangePasswordKey.php',
	'CloseThread' => 'pages/CloseThread.php',
	'Contact' => 'pages/Contact.php',
	'DelFile' => 'pages/DelFile.php',
	'DelPost' => 'pages/DelPost.php',
	'DelPrivateThread' => 'pages/DelPrivateThread.php',
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
	'GetCss' => 'pages/GetCss.php',
	'GetId' => 'pages/GetId.php',
	'GetImage' => 'pages/GetImage.php',
	'GetLLCodes' => 'pages/GetLLCodes.php',
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
	'Portal' => 'pages/Portal.php',
	'Postings' => 'pages/Postings.php',
	'Privacy' => 'pages/Privacy.php',
	'PrivatePostings' => 'pages/PrivatePostings.php',
	'PrivateThreads' => 'pages/PrivateThreads.php',
	'QuotePost' => 'pages/QuotePost.php',
	'QuotePrivatePost' => 'pages/QuotePrivatePost.php',
	'Recent' => 'pages/Recent.php',
	'Register' => 'pages/Register.php',
	'RegisterBoard' => 'pages/RegisterBoard.php',
	'RenameUser' => 'pages/RenameUser.php',
	'Search' => 'pages/Search.php',
	'ShowUser' => 'pages/ShowUser.php',
	'SiteMap' => 'pages/SiteMap.php',
	'SplitThread' => 'pages/SplitThread.php',
	'StickThread' => 'pages/StickThread.php',
	'SubmitPoll' => 'pages/SubmitPoll.php',
	'Threads' => 'pages/Threads.php',
	'UserList' => 'pages/UserList.php',
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
	self::__set('DB', new DB(
		$this->Settings->getValue('sql_user'),
		$this->Settings->getValue('sql_password'),
		$this->Settings->getValue('sql_database')
		));

	self::__set('Board', new Board());
	self::__set('User', new User());

	$this->variables['body']	 = '';
	$this->variables['title']	 = '';
	$this->variables['meta.robots']	 = 'index,follow';
	}

protected function makeMenu()
	{
	$menu =	'<a href="?page=Forums;id='.$this->Board->getId().'"><span class="button" id="start">Übersicht</span></a> <a href="?page=Search;id='.$this->Board->getId().'"><span class="button" id="search">Suche</span></a> <a href="?page=Recent;id='.$this->Board->getId().'"><span class="button" id="recent">Aktuelles</span></a> <a href="?page=UserList;id='.$this->Board->getId().'"><span class="button" id="userlist">Benutzerliste</span></a>';

	if ($this->User->isOnline())
		{
		$menu .=
			' <a href="?page=MyProfile;id='.$this->Board->getId().'"><span class="button" id="myprofile">Mein Profil</span></a> <a href="?page=Logout;id='.$this->Board->getId().'"><span class="button" id="logout">Abmelden</span></a>';

		if ($this->User->isAdmin())
			{
			$menu .=
			' <a href="?page=AdminIndex;id='.$this->Board->getId().'"><span class="button" id="admin">Administration</span></a>';
			}

		}
	else
		{
		$menu .=
			' <a href="?page=Register;id='.$this->Board->getId().'"><span class="button" id="register">Registrieren</span></a> <a href="?page=Login;id='.$this->Board->getId().'"><span class="button" id="login">Anmelden</span></a>';
		}

	return $menu;
	}

public function setValue($key, $value)
	{
	$this->variables[$key] = $value;
	}

public function getValue($key)
	{
	return $this->variables[$key];
	}

protected function showWarning($text)
	{
	$this->setValue('meta.robots', 'noindex,nofollow');
	$this->setValue('title', 'Warnung');
	$this->setValue('body', '<div class="warning">'.$text.'</div>');
	$this->sendOutput();
	}

protected function showFailure($text)
	{
	$this->setValue('meta.robots', 'noindex,nofollow');
	$this->setValue('title', 'Fehler');
	$this->setValue('body', '<div class="warning">'.$text.'</div>');
	$this->sendOutput();
	}

private function getWebring()
	{
	try
		{
		$boards = $this->DB->getRowSet
			('
			SELECT
				id,
				name
			FROM
				boards
			ORDER BY
				id ASC
			');
		}
	catch (DBNoDataException $e)
		{
		$boards = array();
		}

	$menu = '<form action=""><div><select name="link" onchange="location.href=\'?page=Forums;id=\'+this.form.link.options[this.form.link.selectedIndex].value">';

	foreach ($boards as $board)
		{
		$selected = ($this->Board->getId() == $board['id'] ? ' selected="selected"': '');
		$menu .= '<option value="'.$board['id'].'"'.$selected.'>'.$board['name'].'</option>';
		}

	return $menu.'</select></div></form>';
	}

public function prepare()
	{
	$this->setValue('title', 'Warnung');
	$this->setValue('body', 'kein Text');
	}

private function sendOutput()
	{
// 	$file = file_get_contents('html/'.$this->Board->getId().'.html');
	$file = $this->Board->getHtml();

	$this->variables['id'] = $this->Board->getId();
	$this->variables['name'] = $this->Board->getName();
	$this->variables['menu'] = $this->makeMenu();

	if ($this->User->isOnline())
		{
		$this->variables['user'] = $this->User->getName();
		}

	$this->setValue('webring', $this->getWebring());

	$this->setValue('body', $this->getValue('body').'<div style="text-align:right;font-size:8px;margin-top:3px;"><a href="?page=Privacy;id='.$this->Board->getId().'">Datenschutz</a> :: <a href="?page=Impressum;id='.$this->Board->getId().'">Impressum</a></div>');

	foreach ($this->variables as $key => $value)
		{
		$file = str_replace('<!-- '.$key.' -->', $value, $file);
		}

	$this->Io->out($file);
	}

public function show()
	{
	$this->sendOutput();
	}
}

?>