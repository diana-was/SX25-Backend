<?php
// This file respond to no existing files in this directory

// Get the required file
$route= (empty($_SERVER["REQUEST_URI"])) ? '' : $_SERVER["REQUEST_URI"];
$parse= @parse_url ( $route );
$file = isset($parse['path'])?pathinfo($parse['path'],PATHINFO_BASENAME):'';
$ext  = isset($parse['path'])?pathinfo($parse['path'],PATHINFO_EXTENSION):'';
$path = isset($parse['path'])?explode('/',pathinfo($parse['path'],PATHINFO_DIRNAME)):array();

switch ($ext)
{
        case 'gif' : case 'png': case 'jpg':
                header("Content-Type: image/$ext");
                $imagepath="../imagelibrary/$file";
                if (file_exists($imagepath))
                	readfile($imagepath);
                else
                	readfile('1x1.png');
                break;
        case 'css' :
				foreach ($path as $key => $val) {
				        if (empty($val))
				                unset($path[$key]);
				}
				if (empty($path))
				        $dir = '';
				else
				    $dir = implode('/',$path).'/';
	        	header("Content-type: text/css");
	            $dir = "../parkedthemes/$dir";
	            if (file_exists($dir.$file))
	                    include($dir.$file);
	            break;
        default :
                break;
}
?>