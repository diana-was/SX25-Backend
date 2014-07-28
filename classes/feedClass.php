<?php


class Feed extends  Model
{
	private $server_name;
	private $server_url;
	private $requested_page;
	private $remote_addr;
	private $user_agent;
	private $referer;
	private $domain;
	private $urlhost;
	
	public function __construct()
	{
		$this->user_agent 		= @$_SERVER["HTTP_USER_AGENT"];
		$this->referer 			= @$_SERVER['HTTP_REFERER'];
		$this->urlhost			= @$_SERVER['HTTP_HOST'];
		if (APPLICATION_ENVIRONMENT == 'TESTLOCAL') 
		{
			$this->remote_addr 		= ($_SERVER['REMOTE_ADDR'] == '127.0.0.1')?'202.129.79.209':$_SERVER['REMOTE_ADDR'];
		} 
		else 
		{
			$this->remote_addr 		= @$_SERVER['REMOTE_ADDR'];
		}
	}

	private function setDomain ($domain,$keyword='')
	{
		$this->domain			= $domain;
		$this->server_name 		= $this->domain;
		$this->server_url  		= "http://$domain/result.php";
		$this->requested_page	= empty($keyword)?$this->server_url:$this->server_url."Keywords=$keyword";
	}
	
	
	public function loadRelates($type, $feedid='', $keyword, $domain)
	{
		$this->setDomain ($domain,$keyword='');
	
		switch($type)
		{	
	
			case 'VC':			
				$ip = $this->remote_addr;
			
				$xtype 	= '4';
			
				$agent 	= urlencode($this->user_agent);
			
				$via 	= @urlencode($_SERVER['HTTP_VIA']);
			
				$xfwd 	= @urlencode($_SERVER['HTTP_X_FORWARDED_FOR']);
			
				
			
				$s_keyword = str_replace("+", " ", $keyword);
				
				$serveurl = @urlencode( $this->server_url );
			
				$feedURL = "http://feed.validclick.com/?affid=$feedid&maxcount=10&search=".urlencode($keyword)."&xfwd=$xfwd&xflag=show-extras&xtype=4&xformat=xml&ip=$ip&via=$via&agent=$agent&serveurl=$serveurl";
	
				break;
			
			case 'TZ':
			case 'TZ-2':
				$ip = @urlencode($this->remote_addr);
				$domain = @urlencode($this->domain);
				$kw = @urlencode(str_replace(" ", "+", $keyword));
				$rf = @urlencode($this->referer);
				$ua = @urlencode($this->user_agent);
				
				$feedURL = "http://partners.trafficz.com/query.php?domain=$domain&kw=$kw&rf=$rf&ua=$ua&nss=10&nsr=10&ip=$ip";	
				break;
				
			case 'TS':
			case 'TS-2':
				$ip = @urlencode($this->remote_addr);
				$domain = @urlencode($this->domain);
				$kw = @urlencode(str_replace(" ", "+", $keyword));
				$rf = @urlencode($this->referer);
				$ua = @urlencode($this->user_agent);
			    $actual_link = @urlencode($this->requested_page);				
				$feedURL = "http://feed.domainapps.com/getAds?affiliate=fetchprices&RelatedTerms=10&type=sub-123&Keywords=".$kw."&maxCount=0&serveURL=".$actual_link."&ip=".$ip."&ua=".$ua;	
				break;
			
			case 'IS':
				$ip = @urlencode($this->remote_addr );
				$kw = @urlencode(str_replace(" ", "+", $keyword));
				$rf = @urlencode($this->referer);
				$ua = @urlencode($this->user_agent);
					
				$feedURL = "http://searchapi.infospace.com/[partnerID]/wsapi/results?query=".$kw."&category=web&enduserip=".$ip."&qi=1&x-insp-user-headers=".$ua;
				break;
			
			default:
				$feedid = "34574";
				
				$ip = $this->remote_addr;
			
				$xtype = '4';
			
				$agent = urlencode($this->user_agent);
			
				$via = @urlencode($_SERVER['HTTP_VIA']);
			
				$xfwd = @urlencode($_SERVER['HTTP_X_FORWARDED_FOR']);
			
				$serveurl = @urlencode( $this->domain );
			
				$s_keyword = str_replace("+", " ", $keyword);
			
				$feedURL = "http://feed.validclick.com/?affid=$feedid&maxcount=10&search=".urlencode($keyword)."&xfwd=$xfwd&xflag=show-extras&xtype=4&xformat=xml&ip=$ip&via=$via&agent=$agent&serveurl=$serveurl";
				break;
		}
	
		
		$ch=curl_init();
	
		curl_setopt($ch, CURLOPT_URL, $feedURL);
	
		curl_setopt($ch, CURLOPT_TIMEOUT, 5);
	
		curl_setopt($ch, CURLOPT_HEADER, 0);
	
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
	
		$data=curl_exec($ch);
	
		$rss 		= new xml2Array();
	
		$results 	= $rss -> parse($data);
	
		return $results;
	}
	
	function displayRelates($feed, $type)
	{
		$display_string = '';
		
		$i=0;
		
		
		switch($type)
		{	
	
			case 'TZ':
			case 'TZ-2':				
				$_feed = @$feed[0]['children'][2]['children'];
	
				if($_feed)
				{
					foreach($_feed as $row)
					{
						$this_keyword  = '';
						if($row['children'][0]['tagData'] != '' && $i != '0')
							$display_string .= ',';
						$display_string .= @trim(strip_tags($row['children'][0]['tagData']));
						$i++;
					}
				}
				break;
				
			case 'TS':
			case 'TS-2':
			    $offset = !empty($feed[0]['attrs']['NUMRESULTS'])?$feed[0]['attrs']['NUMRESULTS']:''; 
			
				$_feed =  !empty($feed[0]['children'][$offset]['children'])?$feed[0]['children'][$offset]['children']:false;

				if($_feed)
				{
					foreach($_feed as $row)
					{
						$this_keyword  = '';
						if($row['tagData'] != '' && $i != '0')
						    $display_string .= ',';
						$display_string .= @trim(strip_tags($row['tagData']));
						$i++;
					}
				}
				break;
				
			
			default:
				$_feed = @$feed[0]['children'];
				if($_feed)
				{
					foreach($_feed as $row)
					{
						$this_keyword  = '';
						if (!empty($row['children'])) 
						{
							if($row['children'][1]['tagData'] != '' && $i != '0')
								$display_string .= ',';
							$display_string .= @trim(strip_tags($row['children'][1]['tagData']));
						}
						$i++;
					}
				}
				break;
	
		}
		
		return $display_string;
	}
	
	

}
?>