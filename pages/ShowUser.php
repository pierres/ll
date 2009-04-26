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

class ShowUser extends Page {

private $id = 0;


public function prepare()
	{
	try
		{
		$this->id = $this->Input->Get->getInt('user');
		}
	catch (RequestException $e)
		{
		$this->showWarning('Kein Benutzer angegeben!');
		}

	try
		{
		$stm = $this->DB->prepare
			('
			SELECT
				id,
				name,
				realname,
				posts,
				regdate,
				lastpost,
				avatar,
				email
			FROM
				users
			WHERE
				id = ?'
			);
		$stm->bindInteger($this->id);
		$data = $stm->getRow();
		$stm->close();
		}
	catch (DBNoDataException $e)
		{
		$stm->close();
		$this->Output->setStatus(Output::NOT_FOUND);
		$this->showWarning('Kein Benutzer gefunden!');
		}

	$data['regdate'] = $this->L10n->getDateTime($data['regdate']);
	$data['lastpost'] = $this->L10n->getDateTime($data['lastpost']);

	$avatar = (empty($data['avatar']) ? '' : '<img src="'.$this->Output->createUrl('GetAvatar', array('user' => $data['id'])).'" alt="" />');

	$this->setTitle('Profil von '.$data['name']);

	$body =
		'
<div id="brd-main" class="main">

	<h1><span>'.$this->getTitle().'</span></h1>

	<div class="main-head">
		<h2><span>'.$this->getTitle().'</span></h2>
	</div>

	<div class="main-content frm">
		<div class="profile vcard">
			<h3>User information</h3>
			<div class="user">
				<h4 class="user-ident">'.$avatar.' <strong class="username fn nickname">'.$data['name'].'</strong></h4>
				<ul class="user-info">
						<li><span><strong>Registered:</strong> '.$data['regdate'].'</span></li>
						<li><span><strong>Posts:</strong> '.$data['posts'].'</span></li>
				</ul>
			</div>
			<ul class="user-data">
				<li><span><strong>Real name:</strong> '.$data['realname'].'</span></li>
						<li><span><strong>Last post:</strong> '.$data['lastpost'].'</span></li>
						<li><strong>E-mail:</strong> <span>'.($this->User->isLevel(User::ROOT) ? '<a href="mailto:'.$data['email'].'">'.$data['email'].'</a>' : '(Private)').'</span></li>
			</ul>
			<h3>User actions</h3>
			<ul class="user-actions">
				<li><a href="'.$this->Output->createUrl('UserRecent', array('user' => $this->id)).'">Show all posts</a></li>
				'.($this->User->isOnline() ? '<li><a href="'.$this->Output->createUrl('NewPrivateThread', array('recipients' => $data['name'])).'">Neues privates Thema</a></li>' :'').'
				'.($this->User->isLevel(User::ROOT) ? '<li><a href="'.$this->Output->createUrl('DeleteUser', array('user' => $this->id)).'">Benutzerkonto löschen</a></li>' :'').'
			</ul>
		</div>
	</div>

</div>
		';

	$this->setBody($body);
	}

}


?>