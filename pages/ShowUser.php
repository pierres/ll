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

	$avatar = (empty($data['avatar']) ? '' : '<img src="'.$this->Output->createUrl('GetAvatar', array('user' => $data['id'])).'" alt="" />');

	$this->setTitle(sprintf($this->L10n->getText('Profile of %s'), $data['name']));

	$body =
		'
		<div class="box">
			<table>
				<tr>
					<th>'.$this->L10n->getText('Real name').'</th>
					<td> '.$data['realname'].'</td>
				</tr>
				<tr>
					<th>'.$this->L10n->getText('Avatar').'</th>
					<td>'.$avatar.'</td>
				</tr>
				'.($this->User->isLevel(User::ROOT) ? '
				<tr>
					<th>'.$this->L10n->getText('E-mail').'</th>
					<td>'.$data['email'].'</td>
				</tr>
				' : '').'
				<tr>
					<th>'.$this->L10n->getText('Registered').'</th>
					<td>'.$this->L10n->getDateTime($data['regdate']).'</td>
				</tr>
				<tr>
					<th>'.$this->L10n->getText('Posts').'</th>
					<td>'.$data['posts'].'</td>
				</tr>
				'.(!empty($data['lastpost']) ? '.
				<tr>
					<th>'.$this->L10n->getText('Last post').'</th>
					<td>'.$this->L10n->getDateTime($data['lastpost']).'</td>
				</tr>' 
				: '').'
				<tr>
					<th>'.$this->L10n->getText('User actions').'</th>
					<td>
						<ul>
							<li><a href="'.$this->Output->createUrl('UserRecent', array('user' => $this->id)).'">'.$this->L10n->getText('Show all posts').'</a></li>
							'.($this->User->isOnline() ? '<li><a href="'.$this->Output->createUrl('NewPrivateThread', array('recipients' => $data['name'])).'">'.$this->L10n->getText('Post new topic').'</a></li>' :'').'
							'.($this->User->isLevel(User::ROOT) ? '<li><a href="'.$this->Output->createUrl('DeleteUser', array('user' => $this->id)).'">'.$this->L10n->getText('Delete account').'</a></li>' :'').'
						</ul>
					</td>
				</tr>
			</table>
		</div>
		';

	$this->setBody($body);
	}

}


?>