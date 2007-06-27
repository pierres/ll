<?php

class RegisterBoard extends Form{

private $name = '';
private $html = <<<eot
<?xml version="1.0" encoding="UTF-8" ?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.1//EN" "xhtml11.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="de">
<head>
	<meta http-equiv="content-type" content="application/xhtml+xml; charset=UTF-8" />
	<meta http-equiv="content-language" content="de" />
	<meta http-equiv="expires" content="120" />
	<meta name="description" content="Laber-Land - Forengemeinschaft" />
	<meta name="keywords" content="<!-- title -->,<!-- name --> ,laber-land,pierre,schmitz,forum,foren,board,bulletin,kostenlos,webmaster,werbefrei,ll3" />
	<meta name="title" content="<!-- name --> <!-- title -->" />
	<meta name="author" content="Pierre Schmitz" />
	<meta name="robots" content="<!-- meta.robots -->" />
	<meta name="revisit-after" content="3 days" />
	<link rel="shortcut icon" href="favicon.ico" />
	<link rel="stylesheet" media="screen" href="?page=GetCss;id=<!-- id -->" />
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
	<div id="webring">
		<!-- webring -->
	</div>
</body>
</html>
eot;
private $css = <<<eot
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

#webring	{
		text-align:center;
		padding-top:10px;
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
		width:80%;
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


protected function setForm()
	{
	if(!$this->User->isOnline())
		{
		$this->Io->redirect('Login');
		}

	$this->setValue('title', 'Eigenes Forum einrichten');

	if (!$this->User->isLevel(User::ROOT))
		{
		$this->showFailure('nur root darf das!');
		}

	$this->addSubmit('Registrieren');

	$this->addText('name', 'Der Name des Forums', '', 25);
	$this->requires('name');
	$this->setLength('name', 3, 25);
	}

protected function checkForm()
	{
	$this->name = $this->Io->getString('name');

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
			');
		$stm->bindString(htmlspecialchars($this->name));
		$stm->getColumn();
		$stm->close();

		$this->showWarning('Name bereits vergeben!');
		}
	catch (DBNoDataException $e)
		{
		$stm->close();
		}
	}

protected function sendForm()
	{
	$html = str_replace('<!-- id -->', $board, $this->html);

	$stm = $this->DB->prepare
		('
		INSERT INTO
			boards
		SET
			admin = ?,
			name =  ?,
			regdate = ?,
			html = ?,
			css = ?'
		);
	$stm->bindInteger($this->User->getId());
	$stm->bindString(htmlspecialchars($this->name));
	$stm->bindInteger(time());
	$stm->bindString($this->html);
	$stm->bindString($this->css);
	$stm->execute();
	$stm->close();

	$id = $this->DB->getInsertId();

	/** @TODO: remove hardcoded domain name */
	$stm = $this->DB->prepare
		('
		UPDATE
			boards
		SET
			host = ?
		WHERE
			id = ?'
		);
	$stm->bindString($id'.forum.laber-land.de');
	$stm->bindInteger($id);
	$stm->execute();
	$stm->close();

	$stm = $this->DB->prepare
		('
		INSERT INTO
			cats
		SET
			name = \'Allgemeines\',
			boardid = ?'
		);
	$stm->bindInteger($id);
	$stm->execute();
	$stm->close();

	$cat = $this->DB->getInsertId();

	$stm = $this->DB->prepare
		('
		INSERT INTO
			forum_cat
		SET
			catid = ?,
			forumid = 5,
			position = 1
		');
	$stm->bindInteger($cat);
	$stm->execute();
	$stm->close();

	$stm = $this->DB->prepare
		('
		INSERT INTO
			forum_cat
		SET
			catid = ?,
			forumid = 8,
			position = 2
		');
	$stm->bindInteger($cat);
	$stm->execute();
	$stm->close();

	$stm = $this->DB->prepare
		('
		INSERT INTO
			forum_cat
		SET
			catid = ?,
			forumid = 9,
			position = 3
		');
	$stm->bindInteger($cat);
	$stm->execute();
	$stm->close();

	$stm = $this->DB->prepare
		('
		INSERT INTO
			forum_cat
		SET
			catid = ?,
			forumid = 202,
			position = 4
		');
	$stm->bindInteger($cat);
	$stm->execute();
	$stm->close();

	$stm = $this->DB->prepare
		('
		INSERT INTO
			forum_cat
		SET
			catid = ?,
			forumid = 7,
			position = 5
		');
	$stm->bindInteger($cat);
	$stm->execute();
	$stm->close();

	$body =
		'
		<table class="frame">
			<tr>
				<td class="title">
					Registrierung erfolgreich
				</td>
			</tr>
			<tr>
				<td class="main">
					Dein Forum wurde eingerichtet und ist unter folgender Adresse erreichbar:
					<ul>
					<li><strong>Forum:</strong> <a href="?page=Forums;id='.$id.'">http://forum.laber-land.de/?page=Forums;id='.$id.'</a></li>
					<li><strong>Administration:</strong> <a href="?page=AdminIndex;id='.$id.'">http://forum.laber-land.de/?page=AdminIndex;id='.$id.'</a></li>
					</ul>
				</td>
			</tr>
		</table>
		';

	$this->setValue('title', 'Forum erfolgreich eingerichtet');
	$this->setValue('body', $body);
	}


}


?>