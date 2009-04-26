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

class AdminCreateBoard extends Form {

private $name = '';
private $host = '';

protected function setForm()
	{
	$this->setTitle('Neues Board erstellen');

	if (!$this->User->isLevel(User::ROOT))
		{
		$this->showFailure('nur root darf das!');
		}

	$this->add(new SubmitButtonElement('Erstellen'));

	$nameInput = new TextInputElement('name', '', 'Der Name des Boards');
	$nameInput->setMinLength(3);
	$nameInput->setMaxLength(100);
	$this->add($nameInput);

	$hostInput = new TextInputElement('host', '', 'Host/Domain');
	$hostInput->setMinLength(6);
	$hostInput->setMaxLength(100);
	$this->add($hostInput);
	}

protected function checkForm()
	{
	$this->name = $this->Input->Post->getString('name');
	$this->host = $this->Input->Post->getString('host');

	try
		{
		$stm = $this->DB->prepare
			('
			SELECT
				id
			FROM
				boards
			WHERE
				name = ?
				OR host = ?
			');
		$stm->bindString(htmlspecialchars($this->name));
		$stm->bindString(htmlspecialchars($this->host));
		$stm->getColumn();
		$stm->close();

		$this->showWarning('Name oder Host/Domain bereits vergeben!');
		}
	catch (DBNoDataException $e)
		{
		$stm->close();
		}
	}

protected function sendForm()
	{
	$html = <<<eot
<?xml version="1.0" encoding="UTF-8" ?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.1//EN" 
   "http://www.w3.org/TR/xhtml11/DTD/xhtml11.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="de">
<head>
	<meta http-equiv="content-type" content="application/xhtml+xml; charset=UTF-8" />
	<meta http-equiv="content-language" content="de" />
	<meta http-equiv="expires" content="120" />
	<meta name="keywords" content="<!-- title -->,<!-- name -->" />
	<meta name="title" content="<!-- name --> <!-- title -->" />
	<meta name="robots" content="<!-- meta.robots -->" />
	<meta name="revisit-after" content="3 days" />
	<link rel="shortcut icon" href="favicon.ico" />
	<link rel="stylesheet" media="screen" href="?page=GetCss;id=<!-- id -->" />
	<link rel="alternate" type="application/atom+xml" title="<!-- name -->" href="?page=GetRecent;id=<!-- id -->" />
	<link rel="search" type="application/opensearchdescription+xml" href="?page=GetOpenSearch;id=<!-- id -->" title="<!-- name -->" />
	<title>
		<!-- name --> :: <!-- title -->
	</title>
</head>
<body>
	<div id="logo" onclick="location.href='?page=Forums;id=<!-- id -->'">	</div>
	<div id="title">
		<!-- title -->
	</div>
	<div id="welcome">
		Willkommen <!-- user -->!
	</div>
	<div id="menu">
		<!-- menu -->
	</div>
	<div id="body">
		<!-- body -->
	</div>
</body>
</html>
eot;
	$css = <<<eot
body		{
		font-family:sans-serif;
		font-size:12px;
		text-align:left;
		color:#002468;
		background-color:#ffffff;
		}

a		{
		text-decoration:none;
		color:#182263;
		}

a:hover		{
		color:#FFCC00;
		text-decoration:none;
		}

table     	{
		empty-cells:show;
		text-align:left;
		border-collapse:collapse;
		margin-left:auto;
		margin-right:auto;
		}

.frame		{
		border-style:solid;
		border-width:1px;
		border-color:#182263;
		}

.button		{
		background-color:#6c83af;
		color:#FFFFFF;
		font-size:10px;
		font-family:sans-serif;
		padding-left:5px;
		padding-right:5px;
		padding-top:0px;
		padding-bottom:0px;
		margin:0px;
		border-style:solid;
		border-color:#6c83af;
		border-width:1px;
		background-image:url("images/button.png");
		background-repeat:repeat-x;
		}

.button:hover	{
		background-color:#FFCC00;
		border-color:#182263;
		color:#182263;
		}

.new		{
		background-image:url("images/new.png");
		background-repeat:no-repeat;
		width:30px;
		height:30px;
		display:block;
		}

.old		{
		background-image:url("images/old.png");
		background-repeat:no-repeat;
		width:30px;
		height:30px;
		display:block;
		}

.newex		{
		background-image:url("images/newex.png");
		background-repeat:no-repeat;
		width:30px;
		height:30px;
		display:block;
		}

.oldex		{
		background-image:url("images/oldex.png");
		background-repeat:no-repeat;
		width:30px;
		height:30px;
		display:block;
		}

.poll		{
		background-image:url("images/poll.png");
		background-repeat:no-repeat;
		width:30px;
		height:30px;
		display:block;
		}

.closed		{
		background-image:url("images/locked.png");
		background-repeat:no-repeat;
		width:30px;
		height:30px;
		display:block;
		}

.sticky		{
		background-image:url("images/sticky.png");
		background-repeat:no-repeat;
		width:30px;
		height:30px;
		display:block;
		}

.summary	{
		position:absolute;
		width:300px;
		overflow:hidden;
		margin:5px;
		padding:2px;
		background-image:url("images/bg.png");
		background-repeat:repeat-x;
		background-color:#FFCC00;
		color:#182263;
		border:solid 1px #182263;
		z-index:10;
		}

#logo		{
		position:absolute;
		background-image:url("images/logo.png");
		background-repeat:no-repeat;
		width:150px;
		height:111px;
		z-index:1;
		}

#logo:hover{cursor:pointer;}

#title		{
		padding-left:200px;
		padding-top:10px;
		font-size:16px;
		font-weight:bold;
		}

#welcome	{
		padding-left:200px;
		padding-top:20px;
		font-size:12px;
		font-weight:bold;
		}

#menu		{
		text-align:center;
		margin:10px;
		}

#body		{
		margin-top:30px;
		text-align:center;
		position:relative;
		}

.cat		{
		color:#FFFFFF;
		font-weight:bold;
		font-size:12px;
		background-color:#6c83af;
		padding:1px;
		padding-left:100px;
		border-color:#182263;
		border-width:1px;
		border-top-style:solid;
		border-bottom-style:solid;
		}

.catname	{
		color:#FFFFFF;
		}

.catname:hover	{
		color:#FFFFFF;
		}

.path		{
		color:#FFFFFF;
		font-weight:bold;
		font-size:10px;
		background-color:#6c83af;
		padding:1px;
		}

.pathlink	{
		color:#FFFFFF;
		}

.pathlink:hover	{
		color:#FFCC00;
		}

.iconcol	{
		background-color:#dddddd;
		padding:3px;
		margin:0px;
		text-align:center;
		vertical-align:middle;
		width:30px;
		}

.threadiconcol	{
		background-color:#dddddd;
		text-align:center;
		vertical-align:middle;
		min-width:20px;
		max-width:40px;
		width:20px;
		}

.forumcol	{
		color:#002468;
		background-color:#eeeeee;
		padding:5px;
		vertical-align:top;
		}

.forumtitle	{
		font-size:12px;
		font-weight:bold;
		}

.forumdescr	{
		font-size:10px;
		}

.countcol	{
		color:#002468;
		font-size:10px;
		background-color:#eeeeee;
		padding:0px;
		text-align:center;
		}

.lastpost	{
		color:#002468;
		font-size:10px;
		background-color:#eeeeee;
		padding:3px;
		vertical-align:top;
		width:150px;
		}

.forums	{
		color:#002468;
		font-size:10px;
		background-color:#dddddd;
		padding:3px;
		padding-left:10px;
		padding-right:10px;
		vertical-align:top;
		width:150px;
		}

.title		{
		color:#ffffff;
		font-size:12px;
		background-color:#182263;
		text-align:center;
		padding-top:2px;
		padding-bottom:2px;
		padding-left:8px;
		padding-right:8px;
		background-image:url("images/bg.png");
		background-repeat:repeat-x;
		}

.pollbar	{
		font-size:9px;
		background-color:#6c83af;
		}

.pages		{
		color:#002468;
		font-size:10px;
		background-color:#dbdbdb;
		padding:1px;
		}

.thread		{
		font-weight:bold;
		font-size:12px;
		margin-left:5px;
		}

.threadpages	{
		font-size:9px;
		margin-left:15px;
		}

.newthread{
		background-color:#FFCC00;
		color:#182263;
		font-size:10px;
		font-family:sans-serif;
		padding-left:4px;
		padding-right:4px;
		padding-top:0px;
		padding-bottom:0px;
		margin-right:10px;
		border-style:solid;
		border-color:#6c83af;
		border-width:1px;
		background-image:url("images/button.png");
		background-repeat:repeat-x;
		}

.deletedthread	{
		text-decoration:line-through;
		color:gray;
		}

.movedthread	{
		color:gray;
		}

.deletedpost	{
		color:black;
		font-size:10px;
		background-color:gray;
		padding:5px;
		}

.main		{
		color:#002468;
		font-size:12px;
		background-color:#eeeeee;
		padding:5px;
		}

.post0		{
		color:#002468;
		font-size:12px;
		background-color:#dbdbdb;
		padding:5px;
		}

.post1		{
		color:#002468;
		font-size:12px;
		background-color:#eeeeee;
		padding:5px;
		}

.postdate	{
		font-size:9px;
		}

.postname	{
		font-weight:bold;
		}

.postbuttons	{
		text-align:right;
		}

.postedit	{
		font-size:9px;
		}

.warning	{
		font-size:10px;
		border-style:solid;
		border-width:1px;
		border-color:red;
		padding:5px;
		text-align:left;
		margin-left:30%;
		margin-right:30%;
		margin-top:10px;
		margin-bottom:10px;
		}

.preview	{
		color:black;
		font-size:10px;
		background-color:white;
		border-style:solid;
		border-width:1px;
		border-color:black;
		padding:5px;
		margin:20px;
		top:10px;
		text-align:left;
		width:600px;
		}

pre		{
		font-family:monospace;
		color:#000000;
		font-size:12px;
		background-color:#ffffff;
		border-style:dashed;
		border-width:1px;
		border-color:black;
		padding:5px;
		overflow:auto;
		width:600px;
		max-height:600px;
		text-align:left;
		}

blockquote	{
		border-style:solid;
		border-width:2px;
		border-color:#eeeeee;
		padding:5px;
		}


input		{
		font-family:sans-serif;
		color:#002468;
		font-size:12px;
		background-color:#dbdbdb;
		border-style:solid;
		border-width:1px;
		border-color:#182263;
		}

input[type=radio]{
		border-style:none;
		}

input[type=checkbox]{
		border-style:none;
		}

input[type=file]{
		border-style:none;
		}

textarea	{
		font-family:sans-serif;
		color:#002468;
		font-size:12px;
		background-color:#dbdbdb;
		border-style:solid;
		border-width:1px;
		border-color:#182263;
		}

select		{
		font-family:sans-serif;
		color:#002468;
		font-size:12px;
		background-color:#dbdbdb;
		border-style:solid;
		border-width:1px;
		border-color:#182263;
		}

.avatar 	{
		margin:20px;
		max-width:60px;
		max-height:60px;
		}

.link		{
		text-decoration:underline;
		color:#6c83af;
		}

.extlink	{
		text-decoration:underline;
		}

.image		{
		max-width:300px;
		max-height:300px;
		border-style:solid;
		border-width:1px;
		border-color:#182263;
		}

.image:hover	{
		cursor:pointer;
		border-color:#6c83af;
		}

h1{font-size:20px;}
h2{font-size:18px;}
h3{font-size:16px;}
h4{font-size:14px;}
h5{font-size:12px;}
h6{font-size:10px;}
eot;

	$stm = $this->DB->prepare
		('
		INSERT INTO
			boards
		SET
			admin = ?,
			name =  ?,
			regdate = ?,
			html = ?,
			css = ?,
			host = ?,
			admin_name = \'\',
			admin_address = \'\',
			admin_email = \'\',
			admin_tel = \'\',
			description = \'\'
		');
	$stm->bindInteger($this->User->getId());
	$stm->bindString(htmlspecialchars($this->name));
	$stm->bindInteger(time());
	$stm->bindString($html);
	$stm->bindString($css);
	$stm->bindString(htmlspecialchars($this->host));
	$stm->execute();
	$stm->close();

	$this->Output->redirect('AdminIndex', array('id' => $this->DB->getInsertId()));
	}

}

?>