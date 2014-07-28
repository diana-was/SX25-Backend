<?php
/**	APPLICATION:	SX25
*	FILE:			Model.php
*	DESCRIPTION:	front end - Model class to use as starter of any class
*					all classes should be named as Name_Class and the file as Name.php casesensitive
*	CREATED:		29 October 2010 by Diana De vargas
*	UPDATED:									
*/

class File extends  Model
{
	private static $_Object;	// Created a stacic object if the object can be shared or called in multiple places and can be unic 
	
    /**
     * constructor : set up the static object
     *
     * @return static object
     */
	public function __construct()
	{
		self::$_Object = $this;
		return self::$_Object;
	}

    /**
     * Get the class static object
     *
     * @return self
     */
    public static function getInstance() 
    {
    	$class = __CLASS__;
    	if (!isset(self::$_Object)) {
    		return new $class();
    	}	
    	return self::$_Object;
    }
	
	/**
	 * Upload the files to a sent path
	 *
	 * @param array $path  $variable => path
	 * return string $resp 
	 */
    public function upload_files ($path)
	{
		$resp = '';
		if (empty($path) || !is_array($path))
			return 'No path sent to fuction';

		if (empty($_FILES))
			return 'No files send to upload';
			
		foreach ($path as $var => $folder) {
			if (isset($_FILES[$var])) 
			{
				$files = $this->fixFilesArray($_FILES[$var]);
				foreach ($files as $file) 
				{
					if (isset($file['tmp_name']) && !empty($file['tmp_name']))
					{
						$fileName = $folder.'/'.$file['name'];

						if (!is_dir($folder)) {
							mkdir($folder,0777);
						}
					    if (is_file($fileName))
					    {
					    	unlink($fileName);
					    } 
						copy ($file['tmp_name'],$fileName);
						$resp = 'Loaded';
					}
				}
			}
		}
		return $resp;
	}
    
	/**
	 * Fixes the odd indexing of multiple file uploads from the format:
	 *
	 * $_FILES['field']['key']['index']
	 *
	 * To the more standard and appropriate:
	 *
	 * $_FILES['field']['index']['key']
	 *
	 * @param array $files
	 * @author Corey Ballou
	 * @link http://www.jqueryin.com
	 */
	public function fixFilesArray(&$files)
	{
		$fileList = array();
	    $names = array( 'name' => 1, 'type' => 1, 'tmp_name' => 1, 'error' => 1, 'size' => 1);
	
	    foreach ($files as $key => $part) {
	        // only deal with valid keys and multiple files
	        $key = (string) $key;
	        if (isset($names[$key]) && is_array($part)) {
	            foreach ($part as $position => $value) {
	                $fileList[$position][$key] = $value;
	            }
	            // remove old key reference
	            unset($files[$key]);
	        }
	    }
	    return $fileList;
	}    
	
	public function getDirectoryList ($path,$subdir='',$deep=false)
	{
		$list = array();
		if (is_dir($path) && $handle = opendir($path)) 
		{
			/* This is the correct way to loop over the directory. */
		    while (($file = readdir($handle)) !== false) {
		    	if ($file != "." && $file != "..") {
					if (is_dir($path.'/'.$file) && $deep)						    		
            			$list = array_merge($list,$this->getDirectoryList($path.'/'.$file,$file.'/',$deep));
            		else 
						$list[] = $subdir.$file;
		    	}
		    }
		    closedir($handle);
		}
		return $list;
	} 

	public function deleteDirectory ($path,$deep=false,$trace=false)
	{
		$result = true;
		if (is_dir($path) && $handle = opendir($path)) 
		{
			/* This is the correct way to loop over the directory. */
		    while (($file = readdir($handle)) !== false) 
		    {
		    	if ($file != "." && $file != "..") 
		    	{
		    		if ($trace) 
		    			echo "$path$file<br>";
					if (is_dir($path.'/'.$file) && $deep)
					{				
            			if ($this->deleteDirectory($path.'/'.$file,$deep,$trace))
            				$result = $result && rmdir($path.'/'.$file);
            			else
            				$result = false;
					}
            		else 
						$result = $result && unlink($path.$file);
		    	}
		    }
		    closedir($handle);
		}
		return $result;
	} 
	
}