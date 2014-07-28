<?php
/**	APPLICATION:	SX25
*	FILE:			EventModule_Class
*	DESCRIPTION:	display domain - EventModule_Class read directories from database
*	CREATED:		
*	UPDATED:									
*/

class Event extends  Model
{
	protected	$moduleName = 'Event';
	protected	$sourceList = array('eventful');
	private     $app_key = "kQKGr7VcKNZqX87n";
	private     $user='princetonit';
	private     $password='1234abcd';
	private 	$_curlObj;
	private 	$_db;
	private static $_Object;
	
	private function __construct(db_class $db)
	{
		$this->_curlObj = new SingleCurl();
		$this->_db = $db;
		self::$_Object = $this;
		return self::$_Object;
	}
	
	public static function getInstance(db_class $db)
	{
    	$class = __CLASS__;
    	if (!isset(self::$_Object)) {
    		return new $class($db);
    	}	
    	return self::$_Object;
	}
	
	public function check_event_set($keyword,$amount=1)
	{		
		if ( $event = $this->_db->select_one("SELECT count(*) FROM events WHERE LOWER(event_keyword) like LOWER('".$keyword."') and event_stop_time >= '".date('Y-m-d',strtotime('now'))."'"))
			return ($event >= $amount)?$event:false;
		else
			return false;
	}

	public function get_event_info($event_id) 
	{
		$pQuery = "SELECT * FROM events WHERE event_id = '".$event_id."' LIMIT 1";
		$pResults = $this->_db->select($pQuery);
		if($pRow=$this->_db->get_row($pResults, 'MYSQL_ASSOC'))
		{
			return $pRow;
		}
		else
		{
			return false;
		}
	}

	function get_library_event($fromRecord,$recordPerPage,$sortyQuery) {
		
		$output = array();
		$eventQuery = "SELECT * FROM events ".$sortyQuery." LIMIT ".$fromRecord.",".$recordPerPage;
		$pResults = $this->_db->select($eventQuery);
		while($row = $this->_db->get_row($pResults, 'MYSQL_ASSOC')){
			$output[] = $row;
		}
		
		return $output;
	}

	function get_event($keyword, $limit=1, $startnum=0, $sortyQuery='') {
	
		$event = array();
		$sortyQuery = empty($sortyQuery)?'ORDER BY event_id ':$sortyQuery;
		$limit = (empty($limit) || !is_numeric($limit))?1:$limit;
		
		$eventQuery = "SELECT * FROM events WHERE LOWER(event_keyword) like LOWER('$keyword') $sortyQuery LIMIT $startnum,$limit ";
		$pResults = $this->_db->select($eventQuery);
		if($pResults){
			while($pRow = $this->_db->get_row($pResults,'MYSQL_ASSOC')){
				$event[] = $pRow;		
			}
		}
	
		return $event;
	}
	
	public function count_events($keyword='') 
	{
		$query = empty($keyword)?'':" WHERE event_keyword like '$keyword' ";
		$eventQuery = "SELECT count(*) FROM events $query ";
		$count = $this->_db->select_one($eventQuery);
		return $count;
	}

	function count_total_events() 
	{
		$directory = array();
		
		$directoryQuery = "SELECT event_keyword, count(*) as `events`  FROM events GROUP BY event_keyword ORDER BY event_keyword";
		$pResults = $this->_db->select($directoryQuery);
		if($pResults){
			while($pRow = $this->_db->get_row($pResults,'MYSQL_ASSOC')){
				$directory[] = $pRow;		
			}
		}
	
		return $directory;
	}
	
	public function del_event($id)
	{	
		$a = $this->get_event_info($id);
		$dQuery = "DELETE FROM events WHERE event_id = '".$id."'";
		if($this->_db->delete($dQuery))
			return true;
		else
			return false;
	}
	
	public function scrape_event($keyword, $numEvents=30, $dayStart='', $dayEnd='', $location='United States')
	{
		$numEvents	= empty($numEvents)?30:$numEvents;
		$keyword 	= strtolower(trim($keyword)); 
		$location 	= strtolower(trim($location));
		$start 		= empty($dayStart)?date('Ymd00',strtotime('Now')):date('Ymd00',strtotime($dayStart));
		$end 		= empty($dayEnd)?date('Ymd00',strtotime('+ 6 months')):date('Ymd00',strtotime($dayEnd));
		$period 	= $start.'-'.$end;
		$exist 		= $this->count_events($keyword);
		$page_number= ($exist >= $numEvents)?floor($exist / $numEvents)+1:1;
		
		
		$qanum = 0;
		
		while ($qanum < $numEvents)
		{
			$request = "http://api.eventful.com/rest/events/search?app_key=".$this->app_key."&user=".$this->user."&password=".$this->password."&keywords=".urlencode($keyword)."&location=".urlencode($location)."&date=".$period."&page_size=".($numEvents + 5).(!empty($page_number)?"&page_number=$page_number":'');
			//echo $request.'<br>';
			$result = file_get_contents($request);
			if (!$result) break;
			 
			$xml = simplexml_load_string($result);
			if (!isset($xml->events->event) || empty($xml->events->event)) break;
			
			foreach ($xml->events->event as $key => $value )
			{
				if (($numEvents > 0) && ($qanum >= $numEvents))
					break;
	
				 $event_id 		= $value['id'];
				 $title 		= mysql_real_escape_string($value->title);
				 $event_url 	= $value->url;
				 $description 	= mysql_real_escape_string($value->description);
				 $start_time 	= strtotime($value->start_time);
				 $start_time 	= !empty($start_time)? $start_time:strtotime(date('Y-m-d H:i:s')); 
				 $stop_time 	= !empty($value->stop_time)?strtotime($value->stop_time):'';
				 $venue_url 	= $value->venue_url;
				 $venue_name 	= mysql_real_escape_string($value->venue_name);
				 $venue_address = $value->venue_address;
				 $city_name 	= $value->city_name;
				 $region_name 	= $value->region_name;
				 $postal_code 	= $value->postal_code;
				 $country_name 	= $value->country_name;
				 $latitude 		= $value->latitude;
				 $longitude 	= $value->longitude;
				 $image_url 	= $value->image->medium->url;
				 $image_width 	= $value->image->medium->width;
				 $image_height 	= $value->image->medium->height;			
			
				 $earray = array( 'event_eventful_id' => $event_id
				 				, 'event_title' => $title
				 				, 'event_description' => $description
				 				, 'event_keyword' => $keyword
				 				, 'event_url' => $event_url
				 				, 'event_start_time' =>$start_time
				 				, 'event_venue_url' =>$venue_url
				 				, 'event_venue_name' =>$venue_name
				 				, 'event_venue_address' =>$venue_address
				 				, 'event_city_name' =>$city_name
				 				, 'event_region_name' =>$region_name
				 				, 'event_postal_code' =>$postal_code
				 				, 'event_country_name' =>$country_name
				 				, 'event_latitude' =>$latitude
				 				, 'event_longitude' =>$longitude
				 				, 'event_image_url' =>$image_url
				 				, 'event_image_width' =>$image_width
				 				, 'event_image_height' =>$image_height
				 				); 
				 if (!empty($stop_time))
				 	$earray['event_stop_time'] =  $stop_time;
	
				 $result = $this->check_and_save($earray);
				 
				 if($result)
				      $qanum++;
				    
				 $page_number++;
			}
		}
		return $qanum;	
	}
	
	
	public function save_event($array, $id=0)
	{	
		if($id == 0)
		{
			$id = $this->check_and_save($array);
		}
		else
		{
			$this->_db->update_array('events', $array, "event_id='".$id."'");
		}
		return $id;
	}

	private function check_and_save($array)
	{	
		$result = $this->_db->select_one("SELECT * FROM events WHERE event_keyword like '".$array['event_keyword']."' and event_eventful_id = '".$array['event_eventful_id']."' ");
		if(!$result){	
			return $id = $this->_db->insert_array('events', $array);
		}
		else
			return false;
	}
	
	/*
	 *  this fuction is a copy of the one in sx25standard QuestionAnswerModule_Class
	 *   
	 */
	public function getOneEventByKeyword($keyword, $altKeyword='', $start, $alterkw='')
	{
		$data = $this->getEventsByKeyword($keyword, $altKeyword, $start, 1, $alterkw);

		if (is_array($data) && count($data) > 0) {
			$pRow = $data[0];
		} else {
			$pRow = false;
		}
		
		return $pRow;
	}

	public function getEventsByKeyword($keyword, $altKeyword='', $city='', $numEvents=12, $dayStart='', $dayEnd='')
	{
		$keyword= strtolower(trim($keyword));
		$altKeyword = strtolower(trim($altKeyword));
		$cy 	= (!empty($city))? " AND (event_city_name ='$city' or event_country_name ='$city' or event_region_name ='$city') ":"";
		$start 	= !empty($dayStart)? " AND event_start_time >= '".date('Y-m-d',strtotime($dayStart))."'":'';
		$end 	= !empty($dayEnd)? " AND event_start_time <= '".date('Y-m-d',strtotime($dayEnd))."'":'';
		$limit 	= ($numEvents > 0)?" LIMIT $numEvents ":'';
		$aQuery = "SELECT * FROM events WHERE event_keyword = '$keyword' $cy $start $end order by event_start_time ASC $limit ";
		$aResults = $this->_db->select($aQuery);
		$result = array();

		while ($aRow = $this->_db->get_row($aResults, 'MYSQL_ASSOC')) {
			$result[] = $aRow;
		}
	
		if(sizeof($result)==0) 
		{
			$qanum = $this->scrape_event($keyword, $numEvents, $dayStart, $dayEnd, $city);
			if ($qanum > 0)
				return $this->getEventsByKeyword($keyword, $altKeyword, $city, $numEvents, $dayStart, $dayEnd);
			elseif ($altKeyword != $keyword && !empty($altKeyword))
				return $this->getEventsByKeyword($altKeyword, '', $city, $numEvents, $dayStart, $dayEnd);
		}

		return $result;
	}
	
	public function getEventById($event_id)
	{
		$aQuery = "SELECT * FROM events WHERE event_eventful_id='".$event_id."' ";
		$aResults = $this->_db->select($aQuery);
		$pRow=$this->_db->get_row($aResults, 'MYSQL_ASSOC');
		
		return $pRow;
	}
	
	/**
	 * get Events
	 * 
	 * @param string  $keyword
	 * @param array   $sources list of sources to search from 
	 * @param integer $numEvants Events to download 
	 * 
	 * @return array
	 */
	public function getData($keyword,$sources,$numEvents,$extraParams = array())
	{
		$data = array();
		if (!is_array($sources)) { 
			$sources = array($sources);
		}
		
		$event_id 	= (isset($extraParams['event_id'])&&!empty($extraParams['event_id']))?$extraParams['event_id']:0;
		$city 		= isset($extraParams['city'])?trim($extraParams['city']):'';
		$dayStart 	= isset($extraParams['dayStart'])?trim($extraParams['dayStart']):date('Y-m-d');
		$dayEnd 	= isset($extraParams['dayEnd'])?trim($extraParams['dayEnd']):date('Y-m-d', strtotime('+ 1 month'));
		$alterkw	= (isset($extraParams['orign_keyword'])&&!empty($extraParams['orign_keyword']))?$extraParams['orign_keyword']:'';
						
		foreach($sources as $n => $source) 
		{
			$source = strtolower($source);
				switch ($source) {
					case 'eventful': 
								if (!empty($event_id)) {
									$data[] = $this->getEventById($event_id);
							   	} else {
									//echo "<br />$keyword, $city, $numEvents, $dayStart, $dayEnd";
									$data = $this->getEventsByKeyword($keyword, $alterkw, $city, $numEvents, $dayStart, $dayEnd); 
							   	}
							   	break;
				}
		}
		
		return $data;
	}
	
	
}