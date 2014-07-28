<?php
/**
 * Layout class
 * Author: Archie Huang on 08/05/2009
 * 
 */

class LayoutParked extends Model
{
	private 	$_db;
	protected	$layoutFolder;
	
	public function __construct() {
		global $db;
		
		$this->_db = $db;
		$config = Config::getInstance();
		$this->layoutFolder = $config->layoutFolder;
	} 
	
	public function check_layout_id($layout_name) {
		$layout_id = false;
		$layoutQ = "SELECT layout_id FROM layouts_parked WHERE layout_name LIKE '".$layout_name."' LIMIT 1";
	
		$layout_id = $this->_db->select_one($layoutQ);
		return $layout_id;
	}
	
	public function check_layout_folder($layout_folder) {
		$layout_id = false;
		$layoutQ = "SELECT layout_id FROM layouts_parked WHERE layout_folder LIKE '".$layout_folder."' LIMIT 1";
	
		$layout_id = $this->_db->select_one($layoutQ);
		return $layout_id;
	}
	
	public function get_layout_info($layout_id) {
		$pQuery = "SELECT * FROM layouts_parked WHERE layout_id = '".$layout_id."' LIMIT 1";
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
	
	public function get_base_folders($layout_id) {
		$layout_folder = array();
	
		$layoutInfo = $this->get_layout_info($layout_id);
		if ($layoutInfo)
		{
			$layout_folder['base'] = $this->layoutFolder.$layoutInfo['layout_folder'].'/parked.css';
			$layout_folder['folderPath'] = $this->layoutFolder.$layoutInfo['layout_folder'];
			$layout_folder['folder'] = $layoutInfo['layout_folder'];
		}	
		return $layout_folder;
	}
	
	public function save_layout($array, $id=0, &$err='')
	{
		if($id == '0')
		{
			$oldFolderName = $array['layout_folder'];
			$id = $this->_db->insert_array('layouts_parked', $array);
		}
		else
		{
			$layoutQ = "SELECT layout_folder FROM layouts_parked WHERE layout_id = '".$id."' LIMIT 1";
			$oldFolderName = $this->_db->select_one($layoutQ);
			$this->_db->update_array('layouts_parked', $array, "layout_id='".$id."'");
		}
		$err = $this->save_layout_files($id,$oldFolderName);
		
		return $id;
	}


	public function save_layout_files($id,$oldFolderName='')
	{
		$pf = new Profile();
		
		
		$results = '';
		
		$data = $this->get_layout_info($id);
		if ($data === false){
			$results = 'Layout not found';
			break;
		}

		if (!empty($oldFolderName) && is_dir($this->layoutFolder.$oldFolderName) && ($oldFolderName != $data['layout_folder']))
		{
			$err = rename ( $this->layoutFolder.$oldFolderName, $this->layoutFolder.$data['layout_folder'] );
			if ($err == false) echo 'error!!!!';
		}
		
		// create 'layout_folder' folder if doesn't exist
		if (!is_dir($this->layoutFolder.$data['layout_folder'])) {
			mkdir($this->layoutFolder.$data['layout_folder'],0777);
		}

		if (!is_dir($this->layoutFolder.$data['layout_folder'].'/images')) {
			mkdir($this->layoutFolder.$data['layout_folder'].'/images',0777);
		}
		
		// Publish css file
		$style = $this->layoutFolder.$data['layout_folder'].'/parked.css';
		$fp = fopen($style, "wb") or die("can't open file $style");
		fputs($fp, $this->replace_tag($data['layout_css']), strlen($data['layout_css']));
		fclose($fp);

		// Publish js file
		$js = $this->layoutFolder.$data['layout_folder'].'/parked.js';
		$fp = fopen($js, "wb") or die("can't open file $js");
		fputs($fp, $this->replace_tag($data['layout_js']), strlen($data['layout_js']));
		fclose($fp);
				
		return $results;
	}
	
	
	public function get_layouts() {
		$returnArray = array();
		
		$pQuery = "SELECT layout_id, layout_name FROM layouts_parked ORDER BY layout_name";
		$pResults = $this->_db->select($pQuery);
		while($pRow=$this->_db->get_row($pResults, 'MYSQL_ASSOC'))
		{
			$returnArray[] = $pRow;
		}
		return $returnArray;
	}

	public function publish_layout_forParked($layoutIds,$profileIds,$accountIds,&$windowError = null)
	{
		global $cokieFile;
		
		$curl = new SingleCurl('',20);
		$curl->setCookiFileLocation($cokieFile,true);
		
		$pf = new Profile();
		$account = new ParkedAccount();
		
		$resp = array();
		
		foreach ($layoutIds as $id) {
			if (!isset($resp[$id])) {
				
				if (!isset($profileIds[$id]) || empty($profileIds[$id])){
					$resp[$id] = 'Profile not selected';
					break;
				}
				
				if (!isset($accountIds[$id]) || empty($accountIds[$id])){
					$resp[$id] = 'Account not selected';
					break;
				}
				
				$this->_db->connect();	// reconect after timeout
				$data = $this->get_layout_info($id);
				$profileData = $pf->getProfileInfo($profileIds[$id]);
				if ($profileData === false || !is_array($profileData)) {
					$resp[$id] = 'Profile not found';
					break;
				}
				
				// Replace Profile link 
				$name 			= trim($data['layout_name']);
				$profileLink 	= 'http://'.trim($profileData['profile_parked_domain']).'/'.trim($data['layout_folder']).'/';
				$cssLibrary 	= 'http://'.trim($profileData['profile_parked_domain']).'/csslibrary/';
				$cssLink 		= empty($data['layout_css'])?'':'<link href="'.$profileLink.'parked.css" rel="stylesheet" type="text/css">';
				$jsLink 		= empty($data['layout_js'])?'':'<script src="'.$profileLink.'parked.js" language="JavaScript" type="text/JavaScript"></script>';
				// Landing Page
				$landing 		= $data['layout_landing'];
				$exist 			= preg_match ( '/\{CSS_LIBRARY\}\s*{DOMAIN}\s*\.css/',$landing);
				if (!$exist || $exist == 0)
					$landing 	= str_ireplace('&lt;/head&gt;', $cssLink.'&lt;/head&gt;', $landing);
				$landing 		= str_ireplace('&lt;/head&gt;', $jsLink.'&lt;/head&gt;', $landing);
				$landing 		= str_replace(array('{PROFILE_LINK}','{CSS_LIBRARY}'), array($profileLink,$cssLibrary), $landing);
				// Result Page
				$result 		= $data['layout_result'];
				$exist = preg_match ( '/\{CSS_LIBRARY\}\s*{DOMAIN}\s*\.css/',$result);
				if (!$exist || $exist == 0)
					$result 	= str_ireplace('&lt;/head&gt;', $cssLink.'&lt;/head&gt;', $result);
				$result 		= str_ireplace('&lt;/head&gt;', $jsLink.'&lt;/head&gt;', $result);
				$result 		= str_replace(array('{PROFILE_LINK}','{CSS_LIBRARY}'), array($profileLink,$cssLibrary), $result);
				// Other data
				$sponsoredNum 	= $data['layout_sponsored_num']; 
				$sponsored 		= $data['layout_sponsored']; 
				$fileopenLanding= str_replace(array('{PROFILE_LINK}','{CSS_LIBRARY}'), array($profileLink,$cssLibrary), $data['layout_fileopen_1']);
				$newsLanding 	= str_replace(array('{PROFILE_LINK}','{CSS_LIBRARY}'), array($profileLink,$cssLibrary), $data['layout_news_read_1']);
				$fileopenResult	= str_replace(array('{PROFILE_LINK}','{CSS_LIBRARY}'), array($profileLink,$cssLibrary), $data['layout_fileopen_2']);
				$newsResult 	= str_replace(array('{PROFILE_LINK}','{CSS_LIBRARY}'), array($profileLink,$cssLibrary), $data['layout_news_read_2']);
				
				foreach ($accountIds[$id] as $accountId) {
					// conect to parked 
					$accountData = $account->get_parked_account_info($accountId);
					if ($accountData === false || !is_array($accountData)) {
						$resp[$id] = 'Account not found';
						break;
					}
					// login
					$curl->setReferer("https://www.domainapps.com/sites");
					/* NOT REQUIRE TO LOGIN INTO DOMAINAPPS go directly to Parked 
					$curl->createCurl('post','https://www.domainapps.com/login.cfm', array( 'Email' => $accountData['parked_account_user'],
																							'Password' => $accountData['parked_account_password']
																							));
					*/
					//$curl->displayResponce();
					// Parked layouts login
					$curl->createCurl('get','https://secure.parked.com/account/myaccount/layout/?user='.$accountData['parked_account_user'].'&pass='.$accountData['parked_account_password'].'&login=yes', array());
					$page = $curl->__toString();
					//$curl->displayResponce();
					
					$layotLink = str_replace(' ', '+', $name);
					if (stripos($page, "/account/myaccount/layout/index.php?layout_name=$layotLink&mod=1") === false) {
						$db_lname = '';
					} else {
						$db_lname = $name;
					}
					
					// save it
					$curl->createCurl('post','https://secure.parked.com/account/myaccount/layout/index.php', array( 
																						'action' => 'cust_layout',
																						'db_lname' => $db_lname,
																						'layout_name' => $name,
																						'layout_index' => $this->replace_tag($landing),
																						'file_open_index' => $fileopenLanding,
																						'news_read_index' => $newsLanding,
																						'preview_index' => ' Preview ',																						
																						'layout_results' => $this->replace_tag($result),																						
																						'listings_num' => $sponsoredNum,																						
																						'listings1' => $this->replace_tag($sponsored),																						
																						'file_open_results' => $fileopenResult,																						
																						'news_read_results' => $newsResult,																						
																						'preview_results' => ' Preview ',																						
																						'apply_to' => 'using',
																						'apply' => ' Perform Selected Operation '
																						));
					//$curl->displayResponce();
					$rp = $curl->__toString();
					preg_match('/Please fix the error below:(.+)font>/U',$rp, $errors);
					if (!isset($resp[$id])) {
						$resp[$id] = '';
					}
					$resp[$id] .= $accountData['parked_account_name'].': ';
					
					if (!empty($errors) && is_array($errors)) {
						$resp[$id] .= $errors[0].'<br>';
					} else {
						preg_match('/name="layout_name"(.+)value="'.$name.'"/U',$rp, $errors);
						$resp[$id] .= (!empty($errors) && is_array($errors))? 'OK<br>':'Unknown error conecting to parked.<br>';
						if (empty($errors)) {
							$windowError = $rp;
						}
					}
					$curl->createCurl('get','https://www.domainapps.com/logout.cfm', array()); 
					$curl->resetReferer();
				}
			}
		}
		return $resp;
	}
	
	private function __copyDir($sRootPath, $ftp, $ftpPath, $root = true) { 
		
      $oDir = dir($sRootPath);
      $result = true;

	  $ftp->chdir($ftpPath);
	  if ($root) {
	      if(FALSE !== $ftp->mput($sRootPath, $ftpPath))
		  {
		  		$ftp->chmod($ftpPath,0777);
		  }  else  {
				return "Copy of $sRootPath files failed (".implode(',', $ftp->_error_array).") ";
		  }
	  }
      // Change mod to 0777
      while(($sDir = $oDir->read()) !== false) { 
      	if($sDir != '.' && $sDir != '..') {
      		if( is_dir($sRootPath.$sDir) ) { 
				$ftp->chmod($sRootPath.$sDir,0777);
				$result = $this->__copyDir($sRootPath.$sDir, $ftp, $ftpPath.$sDir,false);
				if ($result !== true)
					break;
      		} else { 
				$ftp->chmod($ftpPath.$sDir,0777);
      		} 
        } 
      } 
      $oDir->close(); 
      return $result;
	}

	private function replace_tag($string)
	{
		$string = str_replace('&lt;', '<', $string);
		$string = str_replace('&gt;', '>', $string);
		return $string;
	}
	
}
?>
