<?php


class DelFile extends Page{


public function prepare()
	{
	if (!$this->User->isOnline())
		{
		$this->showFailure('Nur für Mitglieder!');
		}

	try
		{
		$file = $this->Io->getInt('file');
		}
	catch (IoRequestException $e)
		{
		return;
		}

	try
		{
		$file = $this->Sql->fetchValue
			('
			SELECT
				id
			FROM
				files
			WHERE
				id = '.$file.'
				AND userid = '.$this->User->getId()
			);
		}
	catch (SqlNoDataException $e)
		{
		return;
		}

	$this->Sql->query
		('
		DELETE FROM
			files
		WHERE
			id = '.$file
		);

	try
		{
		$posts = $this->Sql->fetchCol
			('
			SELECT
				postid
			FROM
				post_file
			WHERE
				fileid = '.$file
			);

		foreach($posts as $post)
			{
			// Das ist also die letzte Datei für diesen Beitrag ...
			if ($this->Sql->numRows('post_file WHERE postid = '.$post) == 1)
				{
				$this->Sql->query
					('
					UPDATE
						posts
					SET
						file = 0
					WHERE
						id = '.$post
					);
				}
			}
		}
	catch (SqlNoDataException $e)
		{
		}

	$this->Sql->query
		('
		DELETE FROM
			post_file
		WHERE
			fileid = '.$file
		);

	$this->Sql->query
		('
		UPDATE
			users
		SET
			avatar = 0
		WHERE
			id = '.$this->User->getId().'
			AND avatar = '.$file
		);
	}

public function show()
	{
	$this->Io->redirect('MyFiles');
	}


}

?>