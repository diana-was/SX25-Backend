<?php
/**	APPLICATION:	SX25
*	FILE:			modelClass.php
*	DESCRIPTION:	backend - Model class to use as starter of any class
*					all classes should be named as NameSome and the file as nameSomeClass.php casesensitive
*	CREATED:		26 October 2011 by Diana De vargas
*	UPDATED:									
*/

abstract class Model
{
	private static $microtime_start = null;
    /**
     * Magic Get
     *
     * @param string $property Property name
     *
     * @return mixed
     */
    final public function __get($property)
    {
        return $this->__getProperty($property);
    }

    /**
     * Magic Set
     *
     * @param string $property Property name
     * @param mixed $value New value
     *
     * @return self
     */
    final public function __set($property, $value)
    {
        return $this->__setProperty($property, $value);
    }

    /**
     * Magic Isset
     *
     * @param string $property Property name
     *
     * @return boolean
     */
    public function __isset($property)
    {
       if (isset($this->$property)) {
           return true;
       }
    }

    /**
     * Get Property
     *
     * @param string $property Property name
     *
     * @return mixed
     */
    protected function __getProperty($property)
    {
        $value = null;

        $methodName = '__getVal' . ucwords($property);
        if(method_exists($this, $methodName)) {
            $value = call_user_func(array($this, $methodName));
        } else {
        	if (isset($this->$property)) {
        		$value = $this->$property;
        	}
        }

        return $value;
    }

    /**
     * Set Property
     *
     * @param string $property Property name
     * @param mixed $value Property value
     *
     * @return self
     */
    protected function __setProperty($property, $value)
    {
        $methodName = '__setVal' . ucwords($property);
        if(method_exists($this, $methodName)) {
            call_user_func(array($this, $methodName), $value);
        } else {
        	if (isset($this->$property)) {
        		$this->$property = $value;
        	}
        }
            
        return $this;
    }

	public function printTime($text='',$logit=false)
	{
	    if(self::$microtime_start === null)
	    {
	    	self::$microtime_start = microtime(true);
	    	if ($logit)
	    		syslog (LOG_INFO, "$text (0.0)");
	    	else 
	    		echo "$text (0.0)<br>".PHP_EOL;
	    }
	    else
	    {   
	    	$microtime_end = microtime(true);
	    	if ($logit)
	    		syslog (LOG_INFO, "$text (".number_format($microtime_end - self::$microtime_start,4).')');
	    	else 
	    		echo  "$text (".number_format($microtime_end - self::$microtime_start,4).')<br>';
	    	self::$microtime_start = $microtime_end;
	    }
	}
	
	public static function resetCssCounter($i=1)
	{
	    self::$css_count = is_numeric($i)?$i:1;
	}
	
	public static function printCssClass()
	{
		$css_class = '';
	    if(self::$css_count == 1)
	    {
	    	$css_class = 'first odd';
	    }
	    else
	    {   
	    	if (self::$css_count & 1)
	    		$css_class = 'odd';
	    	else 
	    		$css_class = 'even';
	    }
	   	self::$css_count++;
	   	return $css_class;
	}
	
	public function resetTime()
	{
	    self::$microtime_start = microtime(true);
	}
	
    /**
     * Display the object 
     *
     * @return void
     */
    public function printMe() {
		echo '<br />';
		echo '<pre>';
		print_r ($this);
		echo '</pre>';
	}
}