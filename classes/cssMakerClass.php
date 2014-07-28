<?php 
/**	APPLICATION:	SX2/cssMakerClass.php
*	FILE:			cssMakerClass.php
*	DESCRIPTION:	generate a random css file and name it as domain name, then save in csslibrary
*	CREATED:		24 Feb 2011 by Gordon Ye
*	UPDATED:									
*/

class cssMaker extends  Model
{
	protected   $_domain;
	protected   $_session;
	protected   $_action;
	protected   $_layout_id;
	private 	$_db;
	private   	$_queue;
	private   	$_cssStr;
	private   	$_csspart = array('header_footer_background_color','header_background_color','footer_background_color','header_background_image','header_footer_font_color','header_text_color','footer_text_color','background','menues','menu_text_color','menu_background_color','content','left_sidebar_text_color','right_sidebar_text_color','left_sidebar_background_color','right_sidebar_background_color','sponsor_link','wrapper','logo','text','link');
	private		$cssFolder;
	private		$cssLink;
	private		$cssImageFolder;
	private		$cssImageLink;
	
    public function __construct(db_class $db)
	{
		$config = Config::getInstance();
		$this->cssFolder		= $config->sx25cssFolder;
		$this->cssLink			= $config->sx25cssLink;
		$this->cssImageFolder	= $config->sx25cssImageFolder;
		$this->cssImageLink		= $config->sx25cssImageLink;
		
		$this->_db = $db;
		$this->_queue = new cssQueue($db);
	}
	
	public function backupCss(){
		$filename = $this->cssFolder.$this->_domain.".css";
		$csscontent = file_get_contents($filename);
		/*$backupfile = "csslibrary/backup_".$this->_session.".css";
		$handle = fopen($backupfile, "w+");
		$numbytes = fwrite($handle, $csscontent);
		fclose($handle);
		chmod($backupfile, 0777);*/
		$sql = "INSERT INTO css_backup (session, css, domain, created_date) VALUES ('".$this->_session."', '".mysql_real_escape_string($csscontent)."', '".$this->_domain."/".$this->_action."', '".date('Y-m-d')."' )";
		$this->_db->insert_sql($sql);
	}
	
	public function undoCss(){
		$sql = "SELECT css FROM css_backup WHERE session='".$this->_session."' ORDER BY id DESC LIMIT 1 ";
		$csscontent = $this->_db->select_one($sql);
		if(empty($csscontent))
			return false;
		$filename = $this->cssFolder.$this->_domain.".css";
		file_put_contents($filename, $csscontent);
		$sql = "DELETE FROM css_backup WHERE session='".$this->_session."' ORDER BY id DESC LIMIT 1 ";
		$this->_db->insert_sql($sql);
	}
	
	public function clean_cache(){
		$sql = "DELETE FROM css_backup";
		$this->_db->insert_sql($sql);
	}
	
	public function appendCss($domain, $location, $keyword, $file, $existing=0){
		$this->_cssStr = 'body{ background: url("layout_images/'.$file.'") repeat-x; background-attachment: fixed;}';
		$filesize = filesize($this->cssImageFolder.$file);
		if(!$existing){	
			$sql = "INSERT INTO css (css_part, type, size, description, css, value) VALUES ('".$location."', 'image', ".$filesize.", '".$keyword."', '".$this->_cssStr."', '".$file."' )";
			$this->_db->insert_sql($sql);
		}
		$this->saveCssFile($domain, true);
	}

	public function existingImageBackground($domain, $css_id, $type){
		if($type=='header_background_image'){
			$pattern = '/#header[\s]*{[\s]*background[a-zA-Z0-9\_\-\#\%\"\.\(\)\;\:\=\/\s]*}/';
			$this->removePattern($domain, $pattern);
		}
		else if($type=='background'){
			$pattern = '/body[\s]*{[\s]*background[a-zA-Z0-9\_\-\#\"\.\(\)\;\:\=\/\s]*}/';
			$this->removePattern($domain, $pattern);
		}
		$sql = "SELECT css FROM css WHERE css_id =".$css_id;
		$this->_cssStr =  $this->_db->select_one($sql);
		$this->saveCssFile($domain, true);
	}
	
	public function enableOpacity($domain,  $value='0.7'){
		$this->_cssStr = ' #wrapper{ filter:alpha(opacity='.($value*100).');  opacity:'.$value.';} ';
		$this->disableOpacity($domain);
		$this->saveCssFile($domain, true);
	}
	
	
	public function updateHeaderHeight($domain,  $value='200'){
		$this->_cssStr = ' #header { height:'.$value.'px;} ';
		$this->saveCssFile($domain, true);
	}
		
	public function setFontFamily($domain,  $value){
		$sql = 'SELECT css from css WHERE css_id="'.$value.'" ';
		$css = $this->_db->select_one($sql);
		if($css){	
			$pattern = '/body[\s]*{[\s]*font-family[a-zA-Z0-9\_\-\!\#\"\.\(\)\,\;\:\=\/\s]*}/';
			$this->removePattern($domain, $pattern);
			$this->_cssStr = $css;
			$this->saveCssFile($domain, true);
		}			
	}
	
	public function disableOpacity($domain){
		$pattern = '/#wrapper{ filter:alpha[a-zA-Z0-9\_\-\#\"\.\(\)\;\:\=\/\s]*}/'; 
		$this->removePattern($domain, $pattern);
	}
		
	public function removeBackground($domain){
		$pattern = '/body{ background:[a-zA-Z0-9\_\-\#\"\.\(\)\;\:\/\s]*}/';
		$this->removePattern($domain, $pattern);
	}

	public function updateCss($domain, $location, $keyword, $value){
		switch($location){
			case "background":
			$this->_cssStr = ' body { background: #'.$value.';} ';
			/* In case of setting background color, to extract background image first, then place image and color in a line */
			$this->_cssStr = $this->combineBackgroundColorImage($domain, $value); 
			$pattern = '/body{ background:[a-zA-Z0-9\_\-\#\"\.\(\)\;\:\/\s]*}/';
			$this->removePattern($domain, $pattern);
			break;
		
			case "header_text_color":
			$this->_cssStr = '  #header{ color: #'.$value.';}   #header a:link, #header a:active, #header a:visited{ color: #'.$value.';} ';
			break;
			
			case "footer_text_color":
			$this->_cssStr = '  #footer{ color: #'.$value.';}   #footer a:link, #footer a:active, #footer a:visited { color: #'.$value.';} ';
			break;
			
			case "header_background_color":
			$this->_cssStr = ' #header{ background: #'.$value.';}';
			$pattern =  '/#header[\s]*{[\s]*background[a-zA-Z0-9\_\-\#\%\"\.\(\)\;\,\!\:\=\/\s]*}/';
			$this->removePattern($domain, $pattern);		
			break;
			
			case "footer_background_color":
			$this->_cssStr = ' #footer{ background: #'.$value.';}';
			$pattern = '/#footer[\s]*{[\s]*background[a-zA-Z0-9\_\-\#\%\"\.\(\)\;\,\!\:\=\/\s]*]}/';   //a-zA-Z0-9\_\-\#\"\.\(\)\;\:\/\s*
			$this->removePattern($domain, $pattern);
			break;
			
			case "right_sidebar_background_color":
			$this->_cssStr = '  #right_sidebar{ background: #'.$value.';} ';
			$pattern = '/#right_sidebar[\s]*{[\s]*background[a-zA-Z0-9\_\-\#\"\.\(\)\;\:\/\s]*}/';
			$this->removePattern($domain, $pattern);
			break;
			
			case "right_sidebar_text_color":
			$this->_cssStr = '  #right_sidebar, #right_sidebar ul li a{ color:#'.$value.';}  ';
			$pattern = '/#right_sidebar, #right_sidebar ul li a[\s]*{[\s]*color[a-zA-Z0-9\_\-\#\"\.\(\)\;\:\/\s]*}/';
			$this->removePattern($domain, $pattern);
			break;
			
			case "setRightTextBold":
			$this->_cssStr = '  #right_sidebar ul li a{ font-weight: bold;}  ';
			break;
			
			case "setLeftTextBold":
			$this->_cssStr = '  #left_sidebar ul li a{ font-weight: bold;}  ';
			break;
			
			case "left_sidebar_background_color":
			$this->_cssStr = '  #left_sidebar{ background: #'.$value.';} ';
			$pattern = '/#left_sidebar[\s]*{[\s]*background[a-zA-Z0-9\_\-\#\"\.\(\)\;\:\/\s]*}/';
			$this->removePattern($domain, $pattern);
			break;
			
			case "left_sidebar_text_color":
			$this->_cssStr = '  #left_sidebar, #left_sidebar ul li a{ color:#'.$value.';}  ';
			$pattern = '/#left_sidebar, #left_sidebar ul li a[\s]*{[\s]*color[a-zA-Z0-9\_\-\#\"\.\(\)\;\:\/\s]*}/';
			$this->removePattern($domain, $pattern);
			break;
			
			case "menu_color":
			$this->_cssStr = '  #header_menu_1 a, #header_menu_1 a:visited { color: #'.$value.';}  #header_menu_2 a, #header_menu_2 a:visited { color: #'.$value.';}  #footer a{ color: #'.$value.';	} ';
			break;
			
			case "menu_font_size":
			$this->_cssStr = '  #header_menu_1, #header_menu_2{ font-size: '.$value.'px;}  ';
			break;
			
			case "setRightSize":
			$this->_cssStr = '  #right_sidebar, #right_sidebar ul li a{ font-size: '.$value.'px;}  ';
			break;
			
			case "setLeftSize":
			$this->_cssStr = '  #left_sidebar, #left_sidebar ul li a{ font-size: '.$value.'px;}  ';
			break;
			
			case "setMenuTextBold":
			$this->_cssStr = '  #header_menu_1, #header_menu_2{ font-weight: '.$value.';}  ';
			break;
	
			case "menu_background_color":
			$this->_cssStr = '  #header_menu_1, #header_menu_2{ background: #'.$value.';}    ';
			break;
			
			case "logo_color":
			$this->_cssStr = '  #header_logo{ color: #'.$value.';}    ';
			break;

			case "logo_background_color":
			$this->_cssStr = '  #header_logo{ background: #'.$value.';}    ';
			break;
			
			case "text_color":
			$this->_cssStr = '  body{ color: #'.$value.';}    ';
			break;
			
			case "font_size":
			$this->_cssStr = '  body{ font-size:'.$value.'px;}  ';
			$pattern = '/body[\s]*{[\s]*font-size[a-zA-Z0-9\_\-\#\"\.\(\)\;\:\=\/\s]*}/';
			$this->removePattern($domain, $pattern);
			break;
			
			case "sponsor_title_size":
			$this->_cssStr = '  a.URLtop {font-size: #'.$value.'px;} ';
			break;
			
			case "sponsor_description_size":
			$this->_cssStr = '  a.URLtext {font-size: #'.$value.'px;} ';
			break;
			
			case "sponsor_url_size":
			$this->_cssStr = '  a.URLbottom {font-size: #'.$value.'px;} ';
			break;
			
			case "sponsor_title_color":
			$this->_cssStr = '  a.URLtop {color: #'.$value.' !important;} ';
			break;
			
			case "sponsor_description_color":
			$this->_cssStr = '  a.URLtext {color: #'.$value.' !important;} ';
			break;
			
			case "sponsor_url_color":
			$this->_cssStr = '  a.URLbottom {color: #'.$value.' !important;} ';
			break;
		}
		$existing = true; //$this->verifySameCss($location, $value);
		if(!$existing){	
			$sql = "INSERT INTO css (css_part, type, description, css, value) VALUES ('".$location."', 'color', '".$keyword."', '".$this->_cssStr."', '".$value."')";
			$this->_db->insert_sql($sql);
		}
		
		$this->saveCssFile($domain, true);
	}
	
	
	private function removePattern($domain, $pattern){
		$filename = $this->cssFolder.$domain.".css";
		$css = file_get_contents($filename);
		$css = preg_replace($pattern, ' ', $css);
		$handle = fopen($filename, "w+");
		$numbytes = fwrite($handle, $css);
		fclose($handle);
	}
	
	private function combineBackgroundColorImage($domain, $value){
		$file = $this->cssFolder.$domain.'.css';
		chmod($file, 0777);
		$css = file_get_contents($file);
		
		$pos = strpos($css, 'body{ background: url(');
		if($pos===false)
			$pos = strpos($css, 'body{background: url(');
													  
		if($pos!==false){
			$imagestr = explode('body{ background: url(', $css);	
			if(sizeof($imagestr)==0)
				$imagestr = explode('body{background: url(', $css);
			
			$size = sizeof($imagestr);
			$imagestr1 = explode(')',$imagestr[$size-1]);
			$image = $imagestr1[0];
			
			//remove all body background from xxxxxx.com.css, save it back to file
			return ' body{ background: #'.$value.' url('.$image.') repeat-x;  background-attachment: fixed;}';
		}
		return ' body{ background: #'.$value.';}';
	}
	   
    /**
    * generate a random css file and name it as domain name, then save in csslibrary
    */
	public function setRandomCss($domain,$layout_id=0,$type='sx25'){
		if ($this->randomCss($domain,$layout_id,$type))
		{
			$this->saveCssFile($domain, false);
			return true;
		}
		return false;	
	}
	
	public function setRandomTheme($domain,$type='sx25'){
		$themes = $this->_queue->getThemesArray();
		$key = array_rand($themes);
		$this->setColorTheme($domain, $themes[$key], 0, $type);
		return $themes[$key];
	}
	
	private function setUpCss($domain, $layout_id=0, $type='sx25'){
		// --- get base css content ----//
		if (empty($layout_id) ) {
			if ($type=='sx25')
				$site = Site::getInstance($this->_db);
			else
				$site = ParkedDomain::getInstance($this->_db);
			$domainInfo = $site->get_domain_info_name($domain);
			if ($domainInfo)
				$layout_id = $domainInfo['domain_layout_id'];
		} 
		if (!empty($layout_id)) {
			if ($type=='sx25')
				$Layout = new Layout();
			else
				$Layout = new LayoutParked();
			$layoutFolders = $Layout->get_base_folders($layout_id);
			if (!empty($layoutFolders))
			{
				$cssStr = file_get_contents($layoutFolders['base']);
				$folderDir = ($type=='sx25')?'sx25themes/'.$layoutFolders['folder']:$layoutFolders['folder'];
				$cssStr = preg_replace ( '/\((\s*)images\//i' , '(/'.$folderDir.'/images/', $cssStr);
				$cssStr = preg_replace ( '/\'(\s*)images\//i' , "'/".$folderDir.'/images/', $cssStr);
				$this->_cssStr = preg_replace ( '/"(\s*)images\//i' , '"/'.$folderDir.'/images/', $cssStr);
				return true;
			}
		}
		return false;
	}
	
	public function setDefaultCss($domain, $layout_id=0, $type='sx25'){
		if ($this->setUpCss($domain,$layout_id,$type))
		{
			$this->saveCssFile($domain, false);	
			return true;
		}
		return false;
	}
	
	public function setColorTheme($domain, $theme, $layout_id=0, $type='sx25'){
		if ($this->setDefaultCss($domain,$layout_id,$type))
		{
			$pattern = '/#footer[\s]*{[a-zA-Z0-9\_\-\#\%\"\.\(\)\;\,\!\:\=\/\s]*background[a-zA-Z0-9\_\-\#\%\"\.\(\)\;\,\!\:\=\/\s]*}/';
			$this->removePattern($domain, $pattern);
			
			$pattern = '/#header[\s]*{[a-zA-Z0-9\_\-\#\%\"\.\(\)\;\,\!\:\=\/\s]*background:[\s]*url[a-zA-Z0-9\_\-\#\%\"\.\(\)\;\,\!\:\=\/\s]*}/';
			$this->removePattern($domain, $pattern);/**/
			
			$this->getColorTheme($theme);
			$this->setDomainTheme($domain, '', $theme, $type);
			$this->saveCssFile($domain, true);
			return true;
		}	
		return false;
	}
	
    public function randomCss($domain, $layout_id=0, $type='sx25') {

    	if ($this->setUpCss($domain,$layout_id,$type))
		{
			// --- get random css content ----//
			foreach($this->_csspart as $k=>$v){
				$this->_cssStr .= $this->_db->select_one("SELECT css FROM css WHERE css_part='".$v."' ORDER BY RAND()  LIMIT 1");	
			}
			return true;
		}	
		return false;	
    }
	
	public function saveCssFile($domain, $append=false, $filename=''){
		 $domain = strtolower($domain);
		 $filename = strtolower($filename);
		 if($filename=='')
		 	$filename = $this->cssFolder.$domain.".css";
			
		 if (file_exists($filename)) 
		 	chmod($filename, 0777);
		
		 if(!$append)
		 	$handle = fopen($filename, "w+");
		 else
			$handle = fopen($filename, "a+");
		 $numbytes = fwrite($handle, $this->_cssStr);
		 fclose($handle);
		 chmod($filename, 0777);
	}  
	
	public function setDomainTheme($domain, $domain_id, $theme, $type='sx25'){
		if ($type=='sx25')
			$site = Site::getInstance($this->_db);
		else
			$site = ParkedDomain::getInstance($this->_db);
			
		if(empty($domain))
			$site->save_domain(array('domain_theme_id' => $theme), $domain_id);
		else
			$site->update_domain(array('domain_theme_id' => $theme), strtolower($domain));
	}
		
	
	private function verifySameCss($css_part, $value){
		$sql = 'SELECT value from css WHERE css_part="'.$css_part.'" AND value="'.$value.'" ';
		$value = $this->_db->select_one($sql);
		if($value)
			return $value;
		else
			return 0;
	}
		
	 public function getColorTheme($theme) {
		/*$themecss= $this->_db->select("SELECT * FROM css WHERE description='".$theme."' ");	
		while ($tRow=$this->_db->get_row($themecss, 'MYSQL_ASSOC')){
			$this->_cssStr .= $tRow['css'];
		}*/
		$combination = $this->_db->select("SELECT * FROM css_pending WHERE id=".$theme);	
		$row = $this->_db->get_row($combination, 'MYSQL_ASSOC');
		$range = $row['background'].','.$row['header'].','.$row['color'];
		
		$this->_cssStr = '';
		$csssql = $this->_db->select("SELECT * FROM css WHERE css_structure_id IN (".$range.") ");	
		while ($tRow=$this->_db->get_row($csssql, 'MYSQL_ASSOC')){
			$this->_cssStr .= '  '.$tRow['css'];
		}
    }
	
	 public function getFontFamily() {
		$fonts = $this->_db->select("SELECT * FROM css WHERE css_part='text' AND system='1' ORDER BY description ASC ");	
		return $fonts;
    }
	
	public function getNextCombination($domain,$layout_id=0,$type='sx25'){
		$this->setDefaultCss($domain,$layout_id,$type);
		$id = $this->getUntestCombination();
		$this->saveCssFile($domain, true);
		return $id;
	}
	
	public function getUntestCombination(){	
		$combination = $this->_db->select("SELECT * FROM css_pending WHERE status='0' ORDER BY RAND() LIMIT 1 ");	
		$row = $this->_db->get_row($combination, 'MYSQL_ASSOC');
		$range = $row['background'].','.$row['header'].','.$row['color'];
		
		$this->_cssStr = '';
		$csssql = $this->_db->select("SELECT * FROM css WHERE css_structure_id IN (".$range.") ");	
		while ($tRow=$this->_db->get_row($csssql, 'MYSQL_ASSOC')){
			$this->_cssStr .= '  '.$tRow['css'];
		}
		return $row['id'];
	}
	
	public function rejectCombination($cid){
		$this->_db->update_sql("UPDATE css_pending SET status=1 WHERE id=".$cid);		
	}
	
	public function saveCSS($css_part, $description, $css, $active){
		$css_structure_id = $this->saveCSSTable($css_part, $description, $css);
		if($css_structure_id=='0')
			return '0';
		$type = $this->saveStructureTable($css_structure_id, $css_part, $description, $active);
		return $css_structure_id;
	}
		
	private function saveCSSTable($css_part, $description, $css){	
		$css_structure_id = $this->_db->select_one("SELECT css_structure_id FROM css WHERE css_part='".mysql_real_escape_string($css_part)."' AND description='".mysql_real_escape_string($description)."' AND css='".mysql_real_escape_string($css)."' LIMIT 1 ");
		if($css_structure_id)
			return '0';
		
		// color is allowed to have multiple records.
		if($css_part!='background' && $css_part!='header_background_image'){
			$css_structure_id = $this->_db->select_one("SELECT css_structure_id FROM css WHERE description='".$description."' AND type='color'  LIMIT 1 ");
			$type = 'color';
		}else{
			$css_structure_id = '';
			$type = 'image';
		}
		
		if(empty($css_structure_id)){
			$css_structure_id = $this->_db->select_one("SELECT max(css_structure_id) FROM css ");  // get max id in case of new record
			$css_structure_id++;
		}
		$sql = "INSERT INTO css (css_part, type, description, css, css_structure_id) VALUES ('".mysql_real_escape_string($css_part)."', '".$type."', '".mysql_real_escape_string($description)."', '".mysql_real_escape_string($css)."', '".$css_structure_id."')";
		$this->_db->insert_sql($sql);
		
		return $css_structure_id;
	}
	
	private function saveStructureTable($css_structure_id, $css_part, $description, $active){
		if($css_part!='background' && $css_part!='header_background_image'){
			$type = 'color';
		}else if($css_part=='header_background_image'){
			$type = 'header';
		}else{
			$type = 'background';
		}
		$sql = "INSERT INTO css_structure (css_structure_id, css_structure_type, css_structure_description, css_structure_active) VALUES ('".$css_structure_id."', '".$type."', '".mysql_real_escape_string($description)."', '".$active."')";
		$this->_db->insert_sql($sql);
		
		return $type;
	} 
    
    /**
    * Safen up file name. All other methods use this, ensures consistent access
    * @return string
    */
    private function filename($file) {
        $file = strtolower($file);
        $file = preg_replace('~[^a-z0-9/\\_\-]~', '', $file);
        return $file;    
    }
}

?>