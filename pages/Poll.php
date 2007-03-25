<?php

/** FIXME: Nicht geschützt via Form */
/** TODO: Sollte in ein Modul und eine Page aufgeteilt werden! */
class Poll extends Page{

private $id 		= 0;
private $question 	= '';
private $options 	= array();

private $target 	= '';


public function __construct($pollid = 0, $target = 'Postings')
	{
	parent::__construct();

	$this->target = ($this->Io->isRequest('target') ? $this->Io->getString('target') : $target);

	if ($pollid == 0)
		{
		try
			{
			$pollid = $this->Io->getInt('thread');
			}
		catch (IoException $e)
			{
			$this->showWarning('Kein Thema angegeben.');
			}
		}

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
			<form method="post" action="?page=Poll;id='.$this->Board->getId().';thread='.$this->id.';target='.$this->target.'">
				<table style="width:100%">
					<tr>
						<td class="title" colspan="2">
							'.$this->question.'
						</td>
					</tr>
						'.$options.'
					<tr>
						<td class="main" colspan="2">
							<input type="submit" name="submit" value="Abstimmen" />
							<input type="submit" name="result" value="Ergebnis" />
						</td>
					</tr>
				</table>
			</form>
			</td>
		</tr>
		';

	return $body;
	}

public function prepare()
	{
	if ($this->hasVoted())
		{
		$this->reload();
		}

	try
		{
		$valueid = $this->Io->getInt('valueid');
		}
	catch (IoException $e)
		{
		$this->reload();
		}

	$stm = $this->DB->prepare
		('
		INSERT INTO
			poll_voters
		SET
			pollid = ?,
			userid = ?'
		);
	$stm->bindInteger($this->id);
	$stm->bindInteger($this->User->getId());
	$stm->execute();
	$stm->close();

	$stm = $this->DB->prepare
		('
		UPDATE
			poll_values
		SET
			votes = votes + 1
		WHERE
			id = ?
			AND pollid = ?'
		);
	$stm->bindInteger($valueid);
	$stm->bindInteger($this->id);
	$stm->execute();
	$stm->close();
	}

protected function reload()
	{
	$this->Io->redirect($this->target, 'thread='.$this->id.($this->Io->isRequest('result') ? ';result' : ''));
	}

public function show()
	{
	$this->reload();
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