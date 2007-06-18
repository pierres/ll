<?php


class Settings{


private $config = array();


public function __construct()
	{
	$this->config['locale']				= 'de_DE.utf8';
	$this->config['timezone']			= 'Europe/Berlin';

	$this->config['domain']				= 'localhost';
	$this->config['sql_database'] 			= 'll';
	$this->config['sql_user']			= '';
	$this->config['sql_password']			= '';

	$this->config['log_timeout'] 			= 14*24*60*60; //14 days
	$this->config['session_timeout'] 		= 3600;
	$this->config['session_refresh'] 		= 900;
	$this->config['max_age']			= 31536000;
	$this->config['max_threads'] 			= 20;
	$this->config['max_posts'] 			= 20;
	$this->config['max_summary'] 			= 5;
	$this->config['max_users'] 			= 150;
	$this->config['max_post'] 			= 40000;
	$this->config['min_post'] 			= 3;

	$this->config['log_dir'] 			= '';
	$this->config['mail_log_dir'] 			= '';

	$this->config['file_size']			= 2*1024*1024; //2 MByte
	$this->config['quota']				= 10*1024*1024; //10 MByte
	$this->config['files']				= 100;
	$this->config['avatar_size']			= 60;

	$this->config['thumb_size']			= 300;
	$this->config['max_image_file_size']		= 2*1024*1024; //2 MByte
	$this->config['image_refresh']			= 60*60*24*30; //30 days

	$this->config['password_key_lifetime']		= 24*60*60*7; //7 days

	$this->config['antispam_hash']			= '';
	$this->config['antispam_timeout']		= 60*60*2; //2 hour
	$this->config['antispam_wait']			= 2; //2 seconds

	$this->config['cookie_hash']			= '';

	$this->config['debug']				= false;
	
	$this->config['allowed_mime_types']		= array('text/plain', 'text/x-c', 'text/x-c++', 'text/xml',
								'image/gif', 'image/jpeg', 'image/png',
								'application/pdf', 'application/x-bzip2', 'application/x-gzip', 'application/x-zip');

	if (file_exists('LocalSettings.php'))
		{
		include ('LocalSettings.php');
		}

	setlocale(LC_ALL, $this->config['locale']);
	date_default_timezone_set($this->config['timezone']);
	}


public function getValue($key)
	{
	return $this->config[$key];
	}


}

?>