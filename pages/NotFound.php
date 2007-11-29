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
/** TODO Dies kann man auch benutzerfreundlicher realisieren */
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
		$this->Io->redirectToUrl('http://www.archlinux.de');
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
	elseif(preg_match('/id=\d+/', $_SERVER["REQUEST_URI"]))
		{
		$id = preg_replace('/.*id=(\d+).*/', '$1',  $_SERVER["REQUEST_URI"]);
		$this->Io->redirect('Forums', 'id='.$id);
		}
	else
		{
		$search = urlencode(trim(preg_replace('/\W/', ' ',  str_replace('.php', '', $_SERVER["REQUEST_URI"]))));
		$this->Io->redirect('Search', 'search='.$search);
		}
	}

}

?>