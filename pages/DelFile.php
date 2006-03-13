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
		$stm = $this->DB->prepare
			('
			SELECT
				id
			FROM
				files
			WHERE
				id = ?
				AND userid = ?'
			);
		$stm->bindInteger($file);
		$stm->bindInteger($this->User->getId());
		$file =$stm->getColumn();
		}
	catch (DBNoDataException $e)
		{
		return;
		}

	$stm = $this->DB->prepare
		('
		DELETE FROM
			files
		WHERE
			id = ?'
		);
	$stm->bindInteger($file);
	$stm->execute();

	try
		{
		$stm = $this->DB->prepare
			('
			SELECT
				postid
			FROM
				post_file
			WHERE
				fileid = ?'
			);
		$stm->bindInteger($file);

		foreach($stm->getColumnSet() as $post)
			{
			// Das ist also die letzte Datei für diesen Beitrag ...
			$stm = $this->DB->prepare
				('
				SELECT
					COUNT(*)
				FROM
					post_file
				WHERE
					postid = ?'
				);
			$stm->bindInteger($post);

			if ($stm->getColumn() == 1)
				{
				$stm = $this->DB->prepare
					('
					UPDATE
						posts
					SET
						file = 0
					WHERE
						id = ?'
					);
				$stm->bindInteger($post);
				$stm->execute();
				}
			}
		}
	catch (DBNoDataException $e)
		{
		}

	$stm = $this->DB->prepare
		('
		DELETE FROM
			post_file
		WHERE
			fileid = ?'
		);
	$stm->bindInteger($file);
	$stm->execute();

	$stm = $this->DB->prepare
		('
		UPDATE
			users
		SET
			avatar = 0
		WHERE
			id = ?
			AND avatar = ?'
		);
	$stm->bindInteger($this->User->getId());
	$stm->bindInteger($file);
	$stm->execute();
	}

public function show()
	{
	$this->Io->redirect('MyFiles');
	}


}

?>