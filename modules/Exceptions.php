<?php

function myExceptionHandler(Exception $e)
	{
	$e->showDebugScreen();
	exit();
	}

/**
* Hiermit sorgen wir dafür, daß auch PHP-Fehler eine Exception werfen.
*/
function myErrorHandler ($level, $string, $file, $line, $context)
	{
	throw new RuntimeException ($level, $string, $file, $line, $context);
	}

error_reporting(E_STRICT | E_ALL);
set_exception_handler('myExceptionHandler');
set_error_handler('myErrorHandler');

class WebException extends Exception{


protected $webMessage = '';

function __construct($message, $webMessage = 'Ausnahmefehler', $code = 0)
	{
	$this->webMessage = $webMessage;
	parent::__construct($message, $code);
	}

public function getWebMessage()
	{
	return $this->webMessage;
	}

private function getDebugScreen()
	{
	$traces = $this->getTrace();
	$traceString = '';

	foreach($traces as $trace)
		{
		foreach($trace as $key => $value)
			{
			if (is_Array($value))
				{
				$traceString .= '<strong>'.$key.'</strong> => (';
				foreach($trace as $key => $value)
					{
					$traceString .= '<em>'.$key.'</em> =>'.$value.'; ';
					}
				$traceString .= ')<br />';
				}
				else
				{
				$traceString .= '<strong>'.$key.'</strong> =>'.$value.'<br />';
				}
			}
		$traceString .= '<br />';
		}

	header('Content-Type: text/html; charset=UTF-8');
	header('HTTP/1.1 500 Kernel-Panic');

	return '<?xml version="1.0" encoding="UTF-8" ?>
		<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.1//EN" "xhtml11.dtd">
		<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="de">
		<meta http-equiv="content-type" content="text/html; charset=UTF-8" />
		<head>
		<title>'.$this->getWebMessage().'</title>
		</head>
		<body>
			<h1>'.get_class($this).'</h1>
			<h2>'.$this->getWebMessage().'</h2>
			<h2>'.$this->getMessage().'</h2>
			<h3>Code</h3>
			<p>'.$this->getCode().'</p>
			<h3>Datei</h3>
			<p>'.$this->getFile().'</p>
			<h3>Zeile</h3>
			<p>'.$this->getLine().'</p>
			<h3>Verlauf</h3>
			<p><small>'.$traceString.'</small></p>
		</body>
	</html>';
	}

public function showDebugScreen()
	{
	$screen = $this->getDebugScreen();
	echo $screen;
	file_put_contents(Settings::LOG_DIR.time().'.html', $screen);
	exit();
	}
}

class RuntimeException extends WebException{


protected $_context = array();


function __construct($level, $string, $file, $line, $context)
	{
	parent::__construct($string, 'Laufzeit-Fehler', $level);
	$this->file = $file;
	$this->line = $line;
	$this->_level = $level;
	$this->_context = $context;
	}
}


?>