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

class AdminCreateBoard extends Form {

private $name = '';
private $host = '';

protected function setForm()
	{
	$this->setTitle('Neues Board erstellen');

	if (!$this->User->isLevel(User::ROOT))
		{
		$this->showFailure('Nur root darf das');
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

		$this->showWarning('Name oder Host/Domain bereits vergeben');
		}
	catch (DBNoDataException $e)
		{
		$stm->close();
		}
	}

protected function sendForm()
	{
	$html = <<<eot
<!DOCTYPE HTML>
<html>
<head>
	<!-- head -->
	<link rel="shortcut icon" href="images/favicon.ico" />
</head>
<body>
	<div id="ll">
		<div id="title">
			<h1><!-- name --></h1>
			<h2><!-- title --></h2>
			<img src="images/logo.png" alt ="" />
		</div>
		<div id="main-menu">
			<!-- main-menu -->
		</div>
		<div id="sub-menu">
			<!-- user-welcome -->
			<!-- user-menu -->
		</div>
		<div id="body">
			<!-- body -->
		</div>
		<div id="foot-menu">
			<!-- user-menu -->
		</div>
	</div>
</body>
</html>
eot;
	$css = <<<eot
/* LL  */
#ll {
	font-family: sans-serif;
	font-size:14px;
	text-align:left;
	color:#002468;
	background-color:#ffffff;
	max-width:1000px;
	margin-left:auto;
	margin-right:auto;
}
#ll a {
	text-decoration:none;
	color:#182263;
}
#ll a:hover {
	color:#6c83af;
	text-decoration:underline;
}
#ll #title {
	color:#FFFFFF;
	background-color:#6c83af;
	position:relative;
	padding:0px;
	margin-top:0px;
	margin-bottom:0px;
}
#ll #title h1 {
	font-size:18px;
	padding:5px;
	padding-left:10px;
	margin:0px;
}
#ll #title h2 {
	font-size:12px;
	padding:5px;
	padding-left:10px;
	margin:0px;
}
#ll #title img {
	position:absolute;
	z-index:1;
	right:0px;
	top:-2px;
	padding:0px;
	margin:10px;
}
#ll #main-menu {
	background-color:#dddddd;
	margin-top:0px;
	margin-bottom:0px;
	padding:5px;
	padding-left:0px;
}
#ll #main-menu ul {
	list-style-type: none;
	margin:0px;
	padding:0px;
}
#ll #main-menu ul li {
	display: inline;
	margin:0px;
	padding:10px;
}
#ll #sub-menu {
	font-size:12px;
	background-color:#eeeeee;
	margin-top:0px;
	margin-bottom:0px;
	padding:5px;
	padding-left:10px;
	height:17px;
	position:relative;
}
#ll #foot-menu {
	font-size:12px;
	background-color:#eeeeee;
	margin-top:30px;
	margin-bottom:30px;
	padding:5px;
	padding-left:10px;
	height:17px;
	position:relative;
}
#ll #sub-menu ul,
#ll #foot-menu ul {
	list-style-type: none;
	margin:0px;
	padding:0px;
	right:0px;
	position:absolute;
	top:-5px;
}
#ll #sub-menu ul li,
#ll #foot-menu ul li {
	float: left;
	margin:0px;
	padding:10px;
}
#ll #sub-menu ul li ul,
#ll #foot-menu ul li ul {
	visibility: hidden;
	background-color:#eeeeee;
	position:relative;
}
#ll #sub-menu ul li ul li,
#ll #foot-menu ul li ul li {
	margin-top:10px;
	padding:4px;
	float:none;
}
#ll #sub-menu ul li:hover ul,
#ll #foot-menu ul li:hover ul {
	visibility: visible;
}
#ll #body {
	margin-top:30px;
}


/* Page */
#ll .warning {
	color:#FF8000;
}
#ll .failure {
	color:#C00000;
}
#ll .failure,
#ll .warning {
	list-style-type:square;
	font-weight:bold;
	margin:0px;
	padding:0px;
	padding-left:15px;
}
#ll #body .box {
	padding:10px;
	background-color:#eeeeee;
}
#ll #body .box th {
	margin:0px;
	min-width:200px;
	vertical-align:top;
	text-align:left;
}
#ll #body .box td {
	margin:0px;
	vertical-align:top;
	text-align:left;
	padding-left:10px;
}
#ll #body .box td * {
	margin:0px;
	vertical-align:top;
}
#ll #body .current-page {
	color:#6c83af;
	text-decoration:underline;
}


/* Forums */
#ll #body #forum {
	border-collapse:collapse;
	width:100%;
}
#ll #body #forum thead th {
	background-color:#dddddd;
	text-align:center;
	padding-top:4px;
	padding-bottom:4px;
}
#ll #body #forum tbody {
	background-color:#eeeeee;
}
#ll #body #forum tbody .category {
	color:#FFFFFF;
	background-color:#6c83af;
	padding:2px;
	padding-left:41px;
}
#ll #body #forum tbody .forum-status {
	background-color:#dddddd;
	padding:3px;
	margin:0px;
	text-align:center;
	vertical-align:middle;
	width:30px;
	height:30px;
}
#ll #body #forum tbody .forum-status .status-old,
#ll #body #forum tbody .forum-status .status-new {
	width:10px;
	height:10px;
	display:block;
	margin-left:auto;
	margin-right:auto;
}
#ll #body #forum tbody .forum-status .status-old,
#ll #body #threads tbody .thread-status .status-old {
	background-color:#eeeeee;
}
#ll #body #forum tbody .forum-status .status-new,
#ll #body #threads tbody .thread-status .status-new {
	background-color:#6c83af;
}
#ll #body #forum tbody .forum-main {
	padding:5px;
}
#ll #body #forum tbody .forum-main .forum-title {
	font-weight:bold;
	vertical-align:top;
}
#ll #body #forum tbody .forum-main .forum-description {
	font-size:12px;
}
#ll #body #forum tbody .forum-lastpost {
	font-size:12px;
	vertical-align:top;
	padding:5px;
	padding-left:30px;
	width:200px;
}


/* Form */
#ll #body form table {
	border-collapse:collapse;
	background-color:#eeeeee;
	width:100%;
	margin-left:auto;
	margin-right:auto;
}
#ll #body form table tr th,
#ll #body form table tr td {
	text-align:left;
	vertical-align:top;
	padding-left:10px;
	padding-top:10px;
	padding-bottom:10px;
}
#ll #body form table tr th {
	width:200px;
}
#ll #body form input[type=text],
#ll #body form input[type=password],
#ll #body form input[type=file],
#ll #body form textarea {
	font-family:monospace;
	color:#808080;
}
#ll #body form input[type=text]:focus,
#ll #body form input[type=password]:focus,
#ll #body form input[type=file]:focus,
#ll #body form textarea:focus {
	color:#000000;
}
#ll #body form .form-help {
	font-size:12px;
}
#ll #body form ul {
	list-style-type:square;
	margin:0px;
	padding:0px;
	padding-left:15px;
}

/* ThreadList */
#ll #body #threads {
	border-collapse:collapse;
	width:100%;
}
#ll #body #threads thead th {
	background-color:#dddddd;
	text-align:center;
	padding-top:4px;
	padding-bottom:4px;
	height:20px;
}
#ll #body #threads thead td,
#ll #body #threads tfoot td {
	background-color:#dddddd;
	padding-top:4px;
	padding-bottom:4px;
	padding-left:38px;
	font-size:12px;
	height:20px;
}
#ll #body #threads thead .thread-count,
#ll #body #threads tfoot .thread-count {
	text-align:right;
	padding-right:50px;
}
#ll #body #threads tbody {
	background-color:#eeeeee;
}
#ll #body #threads tbody .thread-status {
	background-color:#dddddd;
	padding:3px;
	margin:0px;
	text-align:center;
	vertical-align:middle;
	width:30px;
	height:30px;
}
#ll #body #threads tbody .thread-status .status-old,
#ll #body #threads tbody .thread-status .status-new {
	width:10px;
	height:10px;
	display:block;
	margin-left:auto;
	margin-right:auto;
}
#ll #body #threads tbody .thread-main {
	padding:5px;
	vertical-align:top;
	font-size:12px;
}
#ll #body #threads tbody .thread-main a {
	font-weight:bold;
	font-size:14px;
}
#ll #body #threads tbody .thread-main .thread-summary {
	color:#FFFFFF;
	background-color:#6c83af;
	visibility:hidden;
	position:absolute;
	left:40%;
	width:400px;
	overflow:hidden;
	padding:5px;
	z-index:1;
}
#ll #body #threads tbody tr .thread-main .thread-title:hover + .thread-summary {
	visibility:visible;
}
#ll #body #threads tbody .thread-posts {
	font-weight:bold;
	padding:5px;
	vertical-align:top;
	text-align:center;
}
#ll #body #threads tbody .thread-lastpost {
	font-size:12px;
	vertical-align:top;
	padding:5px;
	padding-left:30px;
	width:200px;
}


/* Postings */
#ll #body #posts {
	border-collapse:collapse;
	width:100%;
}
#ll #body #posts thead th,
#ll #body #posts thead td,
#ll #body #posts tfoot th {
	background-color:#dddddd;
	padding:4px;
	padding-left:10px;
	font-size:12px;
	height:20px;
	text-align:left;
}
#ll #body #posts tbody {
	background-color:#eeeeee;
}
#ll #body #posts tbody:nth-child(odd) {
	background-color:#eeeeee;
}
#ll #body #posts tbody:nth-child(even) {
	background-color:#dbdbdb;
}
#ll #body #posts tbody td {
	padding:10px;
}
#ll #body #posts tbody .posts-user,
#ll #body #posts tbody.poll th {
	padding:10px;
	vertical-align:top;
	font-weight:bold;
	width: 150px;
}
#ll #body #posts tbody .posts-date {
	text-align:right;
}
#ll #body #posts tbody .poll-question {
	font-weight:bold;
}
#ll #body #posts tbody.status-new .posts-date {
	font-weight:bold;
	color:#6c83af;
}
#ll #body #posts tbody .posts-avatar {
	vertical-align:top;
	text-align:center;
}
#ll #body #posts tbody .posts-avatar img {
	max-width:80px;
	max-height:80px;
}
#ll #body #posts tbody .posts-text {
}
#ll #body #posts tbody .posts-text .posts-lastedit {
	font-style:italic;
	padding:10px;
	font-size:12px;
}
#ll #body #posts tbody.poll .poll-options * {
	background-color:transparent;
	padding:0px;
	margin:0px;
}
#ll #body #posts tbody.poll .poll-options .poll-bar {
	background-color:#6c83af;
}
#ll #body #posts tbody.poll .poll-options .poll-percent,
#ll #body #posts tbody.poll .poll-options .poll-votes {
	width:60px;
	text-align:right;
}
#ll #body #posts tbody.poll .poll-options th {
	width:150px;
}
#ll #body #posts tbody.poll .poll-vote {
	text-align:right;
	font-size:12px;
}
#ll #body #posts tbody .posts-menu {
/* TODO move menu to the right */
	font-size:12px;
}
#ll #body #posts tbody .posts-menu ul {
	list-style-type: none;
	margin:0px;
	padding:0px;
	z-index:1;
}
#ll #body #posts tbody .posts-menu ul li {
	float: left;
	margin:0px;
	padding:10px;
}
#ll #body #posts tbody .posts-menu  ul li ul {
	display: none;
	position:absolute;
	background-color:#eeeeee;
}
#ll #body #posts tbody:nth-child(odd) .posts-menu  ul li ul {
	background-color:#eeeeee;
}
#ll #body #posts tbody:nth-child(even) .posts-menu  ul li ul {
	background-color:#dbdbdb;
}
#ll #body #posts tbody .posts-menu ul li ul li {
	margin-top:10px;
	padding:4px;
	float:none;
}
#ll #body #posts tbody .posts-menu ul li:hover ul {
	display: block;
}

#ll #body #posts tbody .posts-text pre {
	white-space: pre-wrap;
	word-wrap: break-word;
	width:450px;
	min-width:95%;
	max-height:700px;
	overflow:auto;
	font-family:monospace;
	color:#000000;
	background-color:#ffffff;
	padding:10px;
	margin:11px;
	border:solid 1px #002468;
}
#ll #body #posts tbody .posts-text p code {
	font-family:monospace;
	color:#000000;
	background-color:#ffffff;
	border:solid 1px #002468;
	padding:1px;
	padding-left:4px;
	padding-right:4px;
	margin-left:4px;
	margin-right:4px;
}
#ll #body #posts tbody .posts-text a {
	text-decoration:underline;
}
#ll #body #posts tbody .posts-text a[rev=auto] {
}
#ll #body #posts tbody .posts-text .image {
	line-height:128px;
	max-width:128px;
	max-height:128px;
	vertical-align:middle;
	border: solid 1px transparent;
	margin:10px;
}
#ll #body #posts tbody .posts-text .image:hover {
	background-color:#6c83af;
	border:solid 1px #6c83af;
}
#ll #body #posts tbody .posts-text audio,
#ll #body #posts tbody .posts-text video {
	display:block;
	margin:10px;
	max-width:80%;
	max-height:700px;
}
#ll #body #posts tbody .posts-text .smiley {
	border: none;
	vertical-align:top;
	text-align:center;
}
#ll #body #posts tbody .posts-text strong {
	font-weight:bolder;
}
#ll #body #posts tbody .posts-text em {
	font-style:italic;
}
#ll #body #posts tbody .posts-text q {
	font-style:italic;
}
#ll #body #posts tbody .posts-text cite {
	font-style:italic;
	margin-left:12px;
}
#ll #body #posts tbody .posts-text blockquote {
	border:solid 1px #002468;
	padding:5px;
	padding-left:15px;
	padding-right:15px;
	margin:0px;
	margin-left:10px;
	margin-right:10px;
	background-color:#dbdbdb;
}
#ll #body #posts tbody:nth-child(odd) .posts-text blockquote {
	background-color:#dbdbdb;
}
#ll #body #posts tbody:nth-child(even) .posts-text blockquote {
	background-color:#eeeeee;
}
#ll #body #posts tbody .posts-text ul {
	list-style-type:square;
}
#ll #body #posts tbody .posts-text .files {
	padding:0px;
	margin:0px;
}


/* MyFiles */
#ll #body form table .files tbody tr {
	background-color:#dbdbdb;
}
#ll #body form table .files tbody tr:nth-child(odd) {
	background-color:#dbdbdb;
}
#ll #body form table .files tbody tr:nth-child(even) {
	background-color:#eeeeee;
}

/* MarkupHelp */
#ll #body .box #markup-help {
}
#ll #body .box #markup-help th {
	width:250px;
}
#ll #body .box #markup-help .markup-help-title {
	text-align:center;
	padding:5px;
	padding-top:20px;
}
#ll #body .box ul {
	list-style-type:square;
	margin:0px;
	padding:0px;
	padding-left:15px;
}
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
			host = ?
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