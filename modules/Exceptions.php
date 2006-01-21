<?php

ini_set('docref_root', 'http://www.php.net/');
set_exception_handler('ExceptionHandler');
set_error_handler('ErrorHandler');

function ExceptionHandler(Exception $e)
	{
	$screen = '<?xml version="1.0" encoding="UTF-8" ?>
		<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.1//EN" "xhtml11.dtd">
		<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="de">
		<meta http-equiv="content-type" content="text/html; charset=UTF-8" />
		<head>
		<title>'.get_class($e).'</title>
		</head>
		<body>
			<h1 style="font-size:16px;">'.get_class($e).'</h1>
			<pre>'.$e->getMessage().'</pre>
			<pre>
<strong>Code</strong>: '.$e->getCode().'
<strong>File</strong>: '.$e->getFile().'
<strong>Line</strong>: '.$e->getLine().'</pre>
			<h2 style="font-size:14px;">Trace:</h2>
			<pre>'.$e->getTraceAsString().'</pre>
		</body>
	</html>';

	if (Modul::__get('Settings')->getValue('log_dir') != '' && is_writable(Modul::__get('Settings')->getValue('log_dir')))
		{
		file_put_contents(Modul::__get('Settings')->getValue('log_dir').time().'.html', $screen);
		}

	header('Content-Type: text/html; charset=UTF-8');
	header('HTTP/1.1 500 Exception');
	echo $screen;
	exit();
	}

/**
* Hiermit sorgen wir dafür, daß auch PHP-Fehler eine Exception werfen.
*/
function ErrorHandler($code, $string, $file, $line)
	{
	throw new InternalRuntimeException ($string, $code, $file, $line);
	}

class InternalRuntimeException extends RuntimeException{

public function __construct($string, $code, $file, $line)
	{
	parent::__construct($string, $code);
	$this->file = $file;
	$this->line = $line;
	}

}

?>