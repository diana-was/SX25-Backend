<?php

/**	APPLICATION:	SX25
*	FILE:			Module.php
*	DESCRIPTION:	admin centre - Class_Controller read the controls from the URL
*	CREATED:		20 September 2010 by Diana De vargas
*	UPDATED:									
*/

class Controller extends  Model
{
	protected $server;
	protected $module			= array();
	protected $path;
	protected $query;
	protected $system;
	protected $address;
	protected $request;
	protected $server_name;
	protected $rootPath;
	protected $baseURL;
	protected $appPath;
	protected $appURL;
	protected $self;
	protected $ori_kwd		= '';
	protected $orign_keyword	= '';
	protected $keyword		= '';	
	private static $_Controller; 
	
    /**
     * constructor : reads the $_SERVER variables and set up the server http
     *
     * @return void
     */
	public function __construct($appPath)
	{
		$this->server = 'http';
		if (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == "on") {
				$this->server .= "s";
		}
		$this->server .= "://";
		if ($_SERVER["SERVER_PORT"] != "80") {
			$this->server_name = $_SERVER["SERVER_NAME"].":".$_SERVER["SERVER_PORT"];
		} else {
			$this->server_name = $_SERVER["SERVER_NAME"];
		}
		$this->server .= $this->server_name;
		$this->system = (stripos(strtolower(php_uname ("s")), 'windows') !== false)?'WINDOWS':'LINUX';
		$this->address = isset($_SERVER['SERVER_ADDR'])?$_SERVER['SERVER_ADDR']:'';
		$this->request = array();
		foreach($_REQUEST as $key => $val) {
			$key = strtolower($key);
			$this->request[$key] = is_string($val)?urldecode($val):$val;
		}
		
		$this->getController();
		
		$this->ori_kwd 	= isset($this->request['w'])?str_replace("+"," ",$this->request['w']):'';
		if(isset($this->request['keywords']))
		{
			$this->orign_keyword= isset($this->request['k']) ? str_replace("+", " ", str_replace("-", " ", $this->request['k'])) : str_replace("+", " ", str_replace("-", " ", $this->request['keywords']));
			$this->keyword 		= str_replace("+", " ", str_replace("-", " ",$this->request['keywords']));
		}
		else
		{
			$this->keyword = isset($this->path[0])?urldecode(str_replace("+", " ", str_replace("-", " ", $this->path[0]))):'';
			$this->orign_keyword = isset($this->path[1])?urldecode(str_replace("+", " ", str_replace("-", " ", $this->path[1]))):'';
		}
		$this->self = $_SERVER['PHP_SELF'];
		$this->appPath = $appPath;
		$this->appURL  = '/';
		$this->baseURL = self::getBaseURL();
		$this->rootPath = self::getRootPath();
		self::$_Controller = $this;
		return self::$_Controller;
	}

	public static function getRootPath ()
	{
		return  self::cleanPath ($_SERVER['DOCUMENT_ROOT'].pathinfo($_SERVER['SCRIPT_NAME'],PATHINFO_DIRNAME)).'/';
	}
	
	public static function getBaseURL ()
	{
		$baseURL = self::cleanPath (pathinfo($_SERVER['PHP_SELF'],PATHINFO_DIRNAME));
		$baseURL .= ($baseURL != '/')?'/':'';
		return $baseURL;
	}
	
	public static function cleanPath ($str) 
	{
		$str = str_replace('\\','/',$str);
		$parts = explode('/',$str);
		$clean = array();
		foreach ($parts as $val) {
			$val = trim($val);
			if (!empty($val)) {
				$clean[] = 	$val;
			}
		}
		if (count($clean) > 0 && $clean[count($clean)-1] == '/') {
			array_pop($clean);
		}
		$str = implode('/',$clean);
		$str = !preg_match('|^[a-z,A-Z]+:|', $str)?'/'.$str:$str;
		return $str;
	}

    /**
     * Get the controller static object
     *
     * @return self
     */
    public static function getInstance($appPath='') 
    {
    	$class = __CLASS__;
    	if (!isset(self::$_Controller)) {
    		return new $class($appPath);
    	}
    	return self::$_Controller;
    }

	/**
     * getController : get the route from the url to extract the module name and the url parts
     *
     * @return void
     */
    public function getController() {
	        /*** get the route from the url ***/
	        $route = (empty($_SERVER["REQUEST_URI"])) ? '' : $_SERVER["REQUEST_URI"];
	        $this->module = '';
			$this->query =array();
			
	        if (empty($route))
	        {
	                $route = 'index';
	        }
	        else
	        {
	                /*** get the parts of the route ***/
	                $parse = @parse_url ( $route );
	                $parts = isset($parse['path'])?explode('/', $parse['path']):array();
	                $query = isset($parse['query'])?$parse['query']:'';
	                parse_str($query,$this->query);
	                
	                foreach ($parts as $key => $val) {
	                	if (empty($val)) {
	                		unset($parts[$key]);
	                	}
	                }
	                
	                if (count($parts) == 0) {
	                	$route = 'index';
	                } else {
	                	$this->module = strtoupper(array_shift($parts));
	                	$this->module = explode('&',$this->module);
	                }
	                if(count($parts) > 0)
	                {
	                    $this->path = $parts;
	                }
	        }
	
	        if (empty($this->module))
	        {
	                $this->module = 'index';
	        }
	
	}

}