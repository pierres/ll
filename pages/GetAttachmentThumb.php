<?php

class GetAttachmentThumb extends GetAttachment{

public function show()
	{
	try
		{
		/** @TODO: Optimieren! */
		$stm = $this->DB->prepare
			('
			(
			SELECT
				attachments.name,
				attachments.type,
				attachment_thumbnails.content,
				attachment_thumbnails.size
			FROM
				attachments,
				attachment_thumbnails
			WHERE
				attachments.id = ?
				AND attachments.id = attachment_thumbnails.id
			)
			UNION
			(
			SELECT
				name,
				type,
				content,
				size
			FROM
				attachments
			WHERE
				id = ?
				AND id NOT IN (SELECT id FROM attachment_thumbnails)
			)'
			);
		$stm->bindInteger($this->file);
		$stm->bindInteger($this->file);
		$data = $stm->getRow();
		$stm->close();
		}
	catch (DBNoDataException $e)
		{
		$stm->close();
		$this->showWarning('Datei nicht gefunden');
		}

	$this->sendFile($data['type'], $data['name'], $data['size'], $data['content']);
	}

}

?>