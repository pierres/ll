<?php


class Settings{


private $config = array();


public function __construct()
	{
	$this->config['domain']			= 'localhost';
	$this->config['sql_database'] 		= 'll';
	$this->config['sql_user']		= '';
	$this->config['sql_password']		= '';

	$this->config['log_timeout'] 		= 14;
	$this->config['session_timeout'] 	= 3600;
	$this->config['session_refresh'] 	= 900;
	$this->config['max_age']		= 31536000;
	$this->config['max_threads'] 		= 20;
	$this->config['max_posts'] 		= 20;
	$this->config['max_summary'] 		= 5;
	$this->config['max_members'] 		= 50;
	$this->config['max_post'] 		= 20000;
	$this->config['min_post'] 		= 3;

	$this->config['log_dir'] 		= '';

	$this->config['file_size']		= 2097152;
	$this->config['quota']			= 10485760;
	$this->config['files']			= 100;
	$this->config['avatar_size']		= 102400;

	if (file_exists(PATH.'LocalSettings.php'))
		{
		include (PATH.'LocalSettings.php');
		}
	}


public function getValue($key)
	{
	return $this->config[$key];
	}


}

?>