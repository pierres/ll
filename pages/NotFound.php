<?php


class NotFound extends Page{


public function prepare()
	{
	if(preg_match('/board\.php/', $_SERVER["REQUEST_URI"]))
		{
		$id = preg_replace('/.*id=(\d+).*/', '$1',  $_SERVER["REQUEST_URI"]);
		$this->Io->redirect('Forums', 'id='.$id);
		}
	elseif(preg_match('/forum\.php/', $_SERVER["REQUEST_URI"]))
		{
		$id = preg_replace('/.*id=(\d+).*/', '$1',  $_SERVER["REQUEST_URI"]);
		$forum = preg_replace('/.*forum=(\d+).*/', '$1',  $_SERVER["REQUEST_URI"]);
		$this->Io->redirect('Threads', 'id='.$id.';forum='.$forum);
		}
	elseif(preg_match('/thread\.php/', $_SERVER["REQUEST_URI"]))
		{
		$id = preg_replace('/.*id=(\d+).*/', '$1',  $_SERVER["REQUEST_URI"]);
		$thread = preg_replace('/.*thread=(\d+).*/', '$1',  $_SERVER["REQUEST_URI"]);
		$this->Io->redirect('Postings', 'id='.$id.';thread='.$thread);
		}
	elseif(preg_match('/archlinux/', $_SERVER["REQUEST_URI"]))
		{
		$this->Io->redirect('Forums', 'id=20');
		}
	elseif(preg_match('/dev/', $_SERVER["REQUEST_URI"]))
		{
		$this->Io->redirect('Forums', 'id=19');
		}
	elseif(preg_match('/user\.php/', $_SERVER["REQUEST_URI"]))
		{
		$id = preg_replace('/.*id=(\d+).*/', '$1',  $_SERVER["REQUEST_URI"]);
		$this->Io->redirect('ShowUser', 'id='.$id);
		}
	elseif(preg_match('/contact|kontakt/', $_SERVER["REQUEST_URI"]))
		{
		$id = preg_replace('/.*id=(\d+).*/', '$1',  $_SERVER["REQUEST_URI"]);
		$this->Io->redirect('Contact', 'id='.$id);
		}
	elseif(preg_match('/impressum|about|uns|über/', $_SERVER["REQUEST_URI"]))
		{
		$id = preg_replace('/.*id=(\d+).*/', '$1',  $_SERVER["REQUEST_URI"]);
		$this->Io->redirect('Impressum', 'id='.$id);
		}
	elseif(preg_match('/jabber/', $_SERVER["REQUEST_URI"]))
		{
		$id = preg_replace('/.*id=(\d+).*/', '$1',  $_SERVER["REQUEST_URI"]);
		$this->Io->redirect('Jabber', 'id='.$id);
		}
	elseif(preg_match('/id=\d+/', $_SERVER["REQUEST_URI"]))
		{
		$id = preg_replace('/.*id=(\d+).*/', '$1',  $_SERVER["REQUEST_URI"]);
		$this->Io->redirect('Forums', 'id='.$id);
		}
	else
		{
		$search = urlencode(trim(preg_replace('/\W/', ' ',  str_replace('.php', '', $_SERVER["REQUEST_URI"]))));
		$this->Io->redirect('Search', 'id=1;submit=;search='.$search);
		}
	}

}

?>