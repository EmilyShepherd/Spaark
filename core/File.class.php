<?php

class File
{
	private $realPath;
	private $path;
	
	public function __construct($path)
	{
		$this->path = $path;
		
		$test_path = Config::APP_ROOT() . $path;
		if (file_exists($test_path))
		{
			$this->realPath = $test_path;
			return;
		}
		
		$test_path = SPAARK_PATH . 'default/' . $path;
		if (file_exists($test_path))
		{
			$this->realPath = $test_path;
			return;
		}
	}
	
	public function exists()
	{
		return !!$this->realPath;
	}
	
	public function requireExists()
	{
		if (!$this->exists())
		{
			throw new NotFoundException($this->path);
		}
	}
	
	public function getContents()
	{
		return file_get_contents($this->realPath);
	}
	
	public function includeCode($path)
	{
		include_once $this->realPath;
	}
}

?>