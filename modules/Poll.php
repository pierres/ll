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
class Poll extends Modul {

private $id 		= 0;
private $question 	= '';
private $options 	= array();

private $target 	= '';


public function __construct($pollid = 0, $target = 'Postings')
	{
	$this->target = $target;
	$this->id = $pollid;

	try
		{
		$stm = $this->DB->prepare
			('
			SELECT
				forumid
			FROM
				threads
			WHERE
				id = ?'
			);
		$stm->bindInteger($this->id);
		$forum = $stm->getColumn();
		$stm->close();

		if ($forum == 0)
			{
			$stm = $this->DB->prepare
				('
				SELECT
					userid
				FROM
					thread_user
				WHERE
					userid = ?
					AND threadid = ?'
				);
			$stm->bindInteger($this->User->getId());
			$stm->bindInteger($this->id);
			$stm->getColumn();
			$stm->close();
			}

		$stm = $this->DB->prepare
			('
			SELECT
				question
			FROM
				polls
			WHERE
				id = ?'
			);
		$stm->bindInteger($this->id);
		$this->question = $stm->getColumn();
		$stm->close();

		$stm = $this->DB->prepare
			('
			SELECT
				value,
				votes,
				id,
				(SELECT SUM(votes) FROM poll_values WHERE pollid = p.pollid) AS total
			FROM
				poll_values AS p
			WHERE
				pollid = ?
			ORDER BY
				votes DESC
			');
		$stm->bindInteger($this->id);
		$this->options = $stm->getRowSet();
		}
	catch (DBNoDataException $e)
		{
		$this->Io->setStatus(Io::NOT_FOUND);
		$this->setValue('meta.robots', 'noindex,nofollow');
		$this->showWarning('Keine Umfrage gefunden.');
		}
	}


public function showPoll()
	{
	if ($this->Io->isRequest('result') || $this->hasVoted())
		{
		return $this->showResult();
		}
	else
		{
		return $this->showForm();
		}
	}

private function showResult()
	{
	$options = '';

	foreach ($this->options as $data)
		{
		if ($data['total'] == 0)
			{
			$percent = 0;
			}
		else
			{
			$percent = $data['votes'] / $data['total'] * 100;
			}

		$options .=
			'
			<tr>
				<td class="main" style="width:30%">
					'.$data['value'].'
				</td>
				<td class="main" style="width:40%">
					<div class="pollbar" style="width:'.round($percent).'%">
						&nbsp;
					</div>
				</td>
				<td class="main" style="font-size:9px;width:30%">
					'.round($percent, 2).'% ('.$data['votes'].')
				</td>
			</tr>
			';
		}

	$body =
		'
		<tr>
			<td colspan="3" style="padding:0px;">
				<table style="width:100%;">
					<tr>
						<td class="title" colspan="3">
							'.$this->question.'
						</td>
					</tr>
						'.$options.'
				</table>
			</td>
		</tr>
		';

	return $body;
	}

private function showForm()
	{
	$i = 0;
	$options = '';
	foreach ($this->options as $data)
		{
		$options .=
			'
			<tr>
				<td class="main">
					<input type="radio" name="valueid" value="'.$data['id'].'" class="radio" />
				</td>
				<td class="main" style="width:100%;">
					'.$data['value'].'
				</td>
			</tr>
			';
		$i++;
		}

	$body =
		'
		<tr>
			<td colspan="3" style="padding:0px;">
			<form method="post" action="?page=SubmitPoll;id='.$this->Board->getId().';thread='.$this->id.';target='.$this->target.'">
				<table style="width:100%">
					<tr>
						<td class="title" colspan="2">
							'.$this->question.'
						</td>
					</tr>
						'.$options.'
					<tr>
						<td class="main" colspan="2">
							<input class="button" type="submit" name="submit" value="Abstimmen" />
							<a href="?page='.$this->target.';id='.$this->Board->getId().';thread='.$this->id.';target='.$this->target.';result" class="button">Ergebnis</a>
						</td>
					</tr>
				</table>
			</form>
			</td>
		</tr>
		';

	return $body;
	}

private function hasVoted()
	{
	if (!$this->User->isOnline())
		{
		return true;
		}

	try
		{
		$stm = $this->DB->prepare
			('
			SELECT
				userid
			FROM
				poll_voters
			WHERE
				pollid = ?
				AND userid = ?'
			);
		$stm->bindInteger($this->id);
		$stm->bindInteger($this->User->getId());
		$stm->getColumn();
		$stm->close();
		return true;
		}
	catch (DBNoDataException $e)
		{
		$stm->close();
		return false;
		}
	}

}

?>