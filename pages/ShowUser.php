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
		$this->showFailure($this->L10n->getText('No user specified.'));
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
		$this->showFailure($this->L10n->getText('User not found.'));
		}

	$data['regdate'] = $this->L10n->getDateTime($data['regdate']);
	$data['lastpost'] = $this->L10n->getDateTime($data['lastpost']);

	$avatar = (empty($data['avatar']) ? '' : '<img src="'.$this->Output->createUrl('GetAvatar', array('user' => $data['id'])).'" alt="" />');

	$this->setTitle(sprintf($this->L10n->getText('Profile of %s'), $data['name']));

	$body =
		'
<div id="brd-main" class="main">

	<h1><span>'.$this->getTitle().'</span></h1>

	<div class="main-head">
		<h2><span>'.$this->getTitle().'</span></h2>
	</div>

	<div class="main-content frm">
		<div class="profile vcard">
			<h3>'.$this->L10n->getText('User information').'</h3>
			<div class="user">
				<h4 class="user-ident">'.$avatar.' <strong class="username fn nickname">'.$data['name'].'</strong></h4>
				<ul class="user-info">
						<li><span><strong>'.$this->L10n->getText('Registered').':</strong> '.$data['regdate'].'</span></li>
						<li><span><strong>'.$this->L10n->getText('Posts').':</strong> '.$data['posts'].'</span></li>
				</ul>
			</div>
			<ul class="user-data">
				<li><span><strong>'.$this->L10n->getText('Real name').':</strong> '.$data['realname'].'</span></li>
						<li><span><strong>'.$this->L10n->getText('Last post').':</strong> '.$data['lastpost'].'</span></li>
						'.($this->User->isLevel(User::ROOT) ? '<li><strong>'.$this->L10n->getText('E-mail').':</strong> <span><a href="mailto:'.$data['email'].'">'.$data['email'].'</a></span></li>' : '').'
			</ul>
			<h3>User actions</h3>
			<ul class="user-actions">
				<li><a href="'.$this->Output->createUrl('UserRecent', array('user' => $this->id)).'">'.$this->L10n->getText('Show all posts').'</a></li>
				'.($this->User->isOnline() ? '<li><a href="'.$this->Output->createUrl('NewPrivateThread', array('recipients' => $data['name'])).'">'.$this->L10n->getText('Post new topic').'</a></li>' :'').'
				'.($this->User->isLevel(User::ROOT) ? '<li><a href="'.$this->Output->createUrl('DeleteUser', array('user' => $this->id)).'">'.$this->L10n->getText('Delete account').'</a></li>' :'').'
			</ul>
		</div>
	</div>

</div>
		';

	$this->setBody($body);
	}

}


?>