<?php

class Poll extends Page{

private $id 		= 0;
private $question 	= '';
private $options 	= array();


public function __construct($pollid = 0)
	{
	parent::__construct();

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
		$this->question = $this->Sql->fetchValue
			('
			SELECT
				question
			FROM
				polls
			WHERE
				id = '.$this->id
			);

		$this->options = $this->Sql->fetch
			('
			SELECT
				value,
				votes,
				id
			FROM
				poll_values
			WHERE
				pollid = '.$this->id.'
			ORDER BY
				votes DESC
			');
		}
	catch (SqlNoDataException $e)
		{
		$this->showWarning('Keine Umfrage gefunden.');
		}
	}


public function showPoll()
	{
	if ($this->hasVoted())
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
	$total = 0;
	foreach ($this->options as $votes)
		{
		$total += $votes['votes'];
		}

	foreach ($this->options as $data)
		{
		if ($total == 0)
			{
			$percent = 0;
			}
		else
			{
			$percent = round($data['votes'] / $total * 100, 1);
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
					'.$percent.'% ('.$data['votes'].')
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
			<form method="post" action="?page=Poll;id='.$this->Board->getId().';thread='.$this->id.'">
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

	try
		{
		$this->Sql->query
			('
			INSERT INTO
				poll_voters
			SET
				pollid = '.$this->id.',
				userid = '.$this->User->getId()
			);

		$this->Sql->query
			('
			UPDATE
				poll_values
			SET
				votes = votes + 1
			WHERE
				id = '.$valueid.'
				AND pollid = '.$this->id
			);
		}
	catch (SqlException $e)
		{
		$this->reload();
		}
	}

private function reload()
	{
	$this->Io->redirect('Postings', 'thread='.$this->id);
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
		$this->Sql->fetchValue
			('
			SELECT
				userid
			FROM
				poll_voters
			WHERE
				pollid = '.$this->id.'
				AND userid = '.$this->User->getId()
			);
		return true;
		}
	catch (SqlNoDataException $e)
		{
		return false;
		}
	}

}

?>