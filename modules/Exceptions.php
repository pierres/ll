<?php

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
			<h2 style="font-size:14px;">'.$e->getMessage().'</h2>
			<h3 style="font-size:12px;">Code</h3>
			<p>'.$e->getCode().'</p>
			<h3 style="font-size:12px;">File</h3>
			<p>'.$e->getFile().'</p>
			<h3 style="font-size:12px;">Line</h3>
			<p>'.$e->getLine().'</p>
			<h3 style="font-size:12px;">Trace</h3>
			<pre>'.$e->getTraceAsString().'</pre>
		</body>
	</html>';

	if (Settings::LOG_DIR != '' && is_writable(Settings::LOG_DIR))
		{
		file_put_contents(Settings::LOG_DIR.time().'.html', $screen);
		}

	header('Content-Type: text/html; charset=UTF-8');
	header('HTTP/1.1 500 Exception');
	echo $screen;
	exit();
	}

/**
* Hiermit sorgen wir dafür, daß auch PHP-Fehler eine Exception werfen.
*/
function ErrorHandler($code, $string, $file, $line, $context)
	{
	throw new RuntimeException ($string, $code);
	}


//error_reporting(E_STRICT | E_ALL);
set_exception_handler('ExceptionHandler');
set_error_handler('ErrorHandler');


?>