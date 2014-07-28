<?php

/*	APPLICATION:	PrincetonIT SX2
	FILE:			reportClass.php
	DESCRIPTION:	Class to extract various report
	CREATED:		20 Jan 2011 by Gordon Ye
	UPDATED:									
*/

class Report extends  Model
{
	private $_db; 
	private static $_Object;
	
	public function __construct(db_class $db)
	{
		$this->_db = $db;
		self::$_Object = $this;
		return self::$_Object;
	}

    /**
     * Get the class static object
     *
     * @return self
     */
    public static function getInstance(db_class $db) 
    {
    	$class = __CLASS__;
    	if (!isset(self::$_Object)) {
    		return new $class($db);
    	}	
    	return self::$_Object;
    }
	
	public function getReport($name, $extra=array()){
		switch($name){
			case 'release_domains':
				$output = $this->getRemoveReport(2);
				break;
				
			case 'delete_domains':
				$output = $this->getRemoveReport(3);
				break;

			case 'remove_parked_domains':
				$output = $this->getRemoveParkDomainReport();
				break;
								
			case 'active_domains':
				$output = $this->getSX25Report('active');
				break;
				
			case 'sx25_mapping_keywords':				
				$output = $this->getSX25Report('sx25_mapping_keywords', $extra);
				break;
				
			case 'sx25_articles':
				$output = $this->getSX25Report('articles');
				break;
				
			case 'sx25_images':
				$output = $this->getSX25Report('images');
				break;
				
			case 'sx25_both':
				$output = $this->getSX25Report('both');
				break;
				
			case 'sx25_menues':
				$output = $this->getSX25Report('menues');
				break;
				
			case 'sx25_keyword_tracking':
				$output = $this->getSX25Report('keyword_tracking');
				break;	
				
			case 'parked_domains':
				$output = $this->getParkedReport('domains');
				break;
				
			case 'parked_articles':
				$output = $this->getParkedReport('articles');
				break;
				
			case 'parked_genz':
				$output = $this->getParkedReport('genz');
				break;
				
			case 'directories':
				$output = $this->getOthersReport('directories');
				break;

			case 'questions':
				$output = $this->getOthersReport('questions');
				break;
				
			case 'images':
				$output = $this->getOthersReport('images');
				break;
				
			case 'answers':
				$output = $this->getOthersReport('answers');
				break;
			
			case 'related_keywords' :
				$output = $this->getOthersReport ( 'related_keywords' );
				break;
			
			case 'missing_content' :
				$output = $this->getMissingContentReport ( $extra );
				break;
			
			case 'banned_sx25_domains' :
				$output = $this->getBannedDomainReport($extra,'SX25');
				break;			
				
			case 'banned_parked_domains' :					
				$output = $this->getBannedDomainReport($extra,'Parked');
				break;			
				
			default :
			   $output = '';
			   break;
		}
		return $output;
	}
	
		
	public function getRemoveReport($status){
		$count = 1;
		$output = '';
		$domain = Site::getInstance($this->_db);
		$pResults = $domain->get_domain_data_list('status',$status);
		foreach ($pResults as $row){
			if ($count == 1)
			{
				$keys = array_keys($row);
				$output .= 'line,'.implode(',',$keys)."\n";
			}
			$output .= $count.",".implode(',',$row);
			$output .= "\n";
			$count++;
		}
		return $output;
	}

	public function getRemoveParkDomainReport(){
		$count = 1;
		$output = '';
		$parkDomain = ParkedDomain::getInstance($this->_db);
		$pResults = $parkDomain->get_domain_data_list('status',2);
		foreach ($pResults as $row){
			if ($count == 1)
			{
				$keys = array_keys($row);
				$output .= 'line,'.implode(',',$keys)."\n";
			}
			$output .= $count.",".implode(',',$row);
			$output .= "\n";
			$count++;
		}
		return $output;
	}
	
	public function getSX25Report($action, $extra = array())
	{
		$count = 1;
		$output = '';
		$pResults = array();
		
		switch($action){
			case 'active':	
				$domain = Site::getInstance($this->_db);
				$pResults = $domain->get_domain_data_list('status',1);
				break;
			case 'sx25_mapping_keywords':	
				$mk = MappingKeyword::getInstance($this->_db);
				$pResults = $mk->get_domain_mapping_keywords($extra['profile_id']);
				break;
			case 'menues':	
				$domain = Menu::getInstance($this->_db);
				$pResults = $domain->get_menus_list();
				break;
			case 'articles':
				$Article = Article::getInstance($this->_db);
				$pResults = $Article->count_sx25_articles();
				break;
			case 'images':
				$Image =Image::getInstance($this->_db);
				$pResults = $Image->count_images();
				break;
			case 'keyword_tracking':
				$domain = Site::getInstance($this->_db);
				$pResults = $domain->get_keyword_tracking_list();
				break;
				
			default :
				$Image = Image::getInstance($this->_db);
				$dResults = $Image->count_images();
				$Article = Article::getInstance($this->_db);
				$aResults = $Article->count_sx25_articles();
				$aArray = array (
						'articles' => 0 
				);
				$dArray = array (
						'domain_id' => '',
						'domain_url' => '',
						'images' => 0,
						'articles' => 0 
				);
				$pResults = array();
				if ($dResults)
				{
					foreach ($dResults as $data) 
					{
						$pResults[$data['domain_id']] = array_merge($data,$aArray);
					}
				}
				if ($aResults)
				{
					foreach ($aResults as $data) 
					{
						if (array_key_exists ($data['domain_id'], $pResults ))
						{
							$pResults[$data['domain_id']]['articles'] = $data['articles'];
						}
						else
						{
							$pResults[$data['domain_id']] = array_merge($dArray,$data);
						}
					}
				}
				break;
		}								

		foreach ($pResults as $row){
			if ($count == 1)
			{
				$keys = array_keys($row);
				$output .= 'line,'.implode(',',$keys)."\n";
			}
			$output .= $count.",".implode(',',$row);
			$output .= "\n";
			$count++;
		}
		return $output;
	}
	
	public function getParkedReport($action)
	{
		$count = 1;
		$output = '';
		$pResults = array();

		switch($action){
			case 'articles':
				$Article = Article::getInstance($this->_db);
				$pResults = $Article->count_parked_articles();
				break;
				
			case 'genz':
				$domain = ParkedDomain::getInstance($this->_db);
				$pResults = $domain->get_domain_data_list();
				break;
				
			default :
				$domain = ParkedDomain::getInstance($this->_db);
				$Article = Article::getInstance($this->_db);
				$aResults = $Article->count_parked_articles();
				$dResults = $domain->get_domain_data_list();
				$aArray = array('articles' => 0 , 'original_domain' => '');
				$dArray = array('domain_url' => '', 'genz_css_createdate' => '', 'layout_name' => '', 'theme_name' => '', 'theme_code' => '', 
				'keyword' => '','keyword_related1' => '','keyword_related2' => '','keyword_related3' => '','keyword_related4' => '','keyword_related5' => '','keyword_related6' => '',
				'articles' => 0, 'original_domain' => '');
				$pResults = array();
				if ($dResults)
				{
					foreach ($dResults as $data) 
					{
						$pResults[$data['domain_url']] = array_merge($data,$aArray);
					}
				}
				if ($aResults)
				{
					foreach ($aResults as $data) 
					{
						if (array_key_exists ($data['domain_url'], $pResults ))
						{
							$pResults[$data['domain_url']]['articles'] = $data['articles'];
						}
						else
						{
							$pResults[$data['domain_url']] = array_merge($dArray,$data);
						}
					}
				}
				break;
		}

		
		foreach ($pResults as $row){
			if ($count == 1)
			{
				$keys = array_keys($row);
				$output .= 'line,'.implode(',',$keys)."\n";
			}
			$output .= $count.",".implode(',',$row);
						$output .= "\n";
			$count++;
		}
		return $output;
	}

	public function getOthersReport($action)
	{
		$count = 1;
		$output = '';
		$pResults = array();
		
		switch($action){
			case 'directories':	
				$Dir = Dty::getInstance($this->_db);
				$pResults = $Dir->count_keywords_directories();
				break;
			case 'questions':	
				$QA = QuestionAnswer::getInstance($this->_db);
				$pResults = $QA->count_keywords_qa();
				break;
			case 'images':	
				$Lib = ImageLibrary::getInstance($this->_db);
				$pResults = $Lib->count_keywords_images();
				break;
			case 'answers':	
				$A = Answer::getInstance($this->_db);
				$pResults = $A->count_keywords_answers();
				break;
			case 'related_keywords':
				$A = RelatedKeyword::getInstance($this->_db);
				$pResults = $A->get_relatedKeyword_list();
				break;
		}								

		foreach ($pResults as $row){
			if ($count == 1)
			{
				$keys = array_keys($row);
				$output .= 'line,'.implode(',',$keys)."\n";
			}
			$output .= $count.",".implode(',',$row);
						$output .= "\n";
			$count++;
		}
		return $output;
	}
	
	public function getMissingContentReport($extra = array()){
		$count 		= 1;
		$output 	= '';
		$domain 	= Site::getInstance ( $this->_db );

		$pResults = $domain->get_domain_data_list('account',$extra['account_id']);
		
		foreach ( $pResults as $row ) {
			if ($count == 1) {
				$keys = array_keys ( $row );
				$output .= 'line,' . implode ( ',', $keys ) . ',missing_content' . "\n";
			}
			
			$missed = $this->get_missed_module_element($row['layout_id'], $row);
			$output .= $count . "," . implode ( ',', $row ) . ',' . $missed;
						
			$output .= "\n";
			$count ++;
		}
		
		return $output;
	}
	
	public function getBannedDomainReport($extra = array(),$source)
	{
		$count 		= 1;
		$output 	= '';
		$bannedDomain = BannedDomain::getInstance ( $this->_db );
		$pResults = $bannedDomain->get_banned_domain_list('date',$extra,$source);
		foreach ($pResults as $row){
			if ($count == 1)
			{
				$keys = array_keys($row);
				$output .= 'line,'.implode(',',$keys)."\n";
			}
			$output .= $count.",".implode(',',$row);
						$output .= "\n";
			$count++;
		}
		return $output;
	}
	
	function get_missed_module_element($layout_id, $domainInfo=array())
	{
		$Layout = new Layout();
		static $layoutMod = array();
	
		// Get the list of modules and amount of content for each module
		if (!array_key_exists ($layout_id, $layoutMod))
		{
			$layoutMod[$layout_id] = $Layout->getLayoutModules($layout_id);
			ksort($layoutMod[$layout_id]);
		}
		$modules 		= $layoutMod[$layout_id];
		$domain_id 		= trim($domainInfo['domain_id']);
		$domain_url 	= trim($domainInfo['domain_url']);
		$domain_keyword = trim($domainInfo['domain_keyword']);
	
		// Get the list of missing content for this site
		$Site = Site::getInstance($this->_db);
		$missingModules = $Site->get_missed_module_element($modules, $domain_id, $domain_url, $domain_keyword);
		
		$moduleList = '';
		foreach($missingModules as $k=>$m){
			$moduleList .= $m . '-' . $k .' | ';
		}
		
		return substr($moduleList, 0, strlen($moduleList) - 2);
	}
}

?>
