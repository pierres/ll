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
				attachments
			WHERE
				id = ?
				AND userid = ?'
			);
		$stm->bindInteger($file);
		$stm->bindInteger($this->User->getId());
		$file =$stm->getColumn();
		$stm->close();
		}
	catch (DBNoDataException $e)
		{
		$stm->close();
		return;
		}

	$stm = $this->DB->prepare
		('
		DELETE FROM
			attachments
		WHERE
			id = ?'
		);
	$stm->bindInteger($file);
	$stm->execute();
	$stm->close();

	try
		{
		$stm = $this->DB->prepare
			('
			DELETE FROM
				attachment_thumbnails
			WHERE
				id = ?'
			);
		$stm->bindInteger($file);
		$stm->execute();
		$stm->close();
		}
	catch (DBNoDataException $e)
		{
		}

	/** TODO Stement-Schatelung aufräumen */
	try
		{
		$stm = $this->DB->prepare
			('
			SELECT
				postid
			FROM
				post_attachments
			WHERE
				attachment_id = ?'
			);
		$stm->bindInteger($file);

		foreach($stm->getColumnSet() as $post)
			{
			// Das ist also die letzte Datei für diesen Beitrag ...
			$stm2 = $this->DB->prepare
				('
				SELECT
					COUNT(*)
				FROM
					post_attachments
				WHERE
					postid = ?'
				);
			$stm2->bindInteger($post);

			if ($stm2->getColumn() == 1)
				{
				$stm3 = $this->DB->prepare
					('
					UPDATE
						posts
					SET
						file = 0
					WHERE
						id = ?'
					);
				$stm3->bindInteger($post);
				$stm3->execute();
				$stm3->close();
				}
			$stm2->close();
			}
		$stm->close();
		}
	catch (DBNoDataException $e)
		{
		}
	try
		{
		$stm = $this->DB->prepare
			('
			DELETE FROM
				post_attachments
			WHERE
				attachment_id = ?'
			);
		$stm->bindInteger($file);
		$stm->execute();
		$stm->close();
		}
	catch (DBNoDataException $e)
		{
		$stm->close();
		}

// 	$stm = $this->DB->prepare
// 		('
// 		UPDATE
// 			users
// 		SET
// 			avatar = 0
// 		WHERE
// 			id = ?
// 			AND avatar = ?'
// 		);
// 	$stm->bindInteger($this->User->getId());
// 	$stm->bindInteger($file);
// 	$stm->execute();
//	 $stm->close();
	}

public function show()
	{
	$this->Io->redirect('MyFiles');
	}


}

?>