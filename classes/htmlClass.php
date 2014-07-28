<?php
/**
 * Html template class
 * Author: Archie Huang on 29/01/2009
 * 
 */
// Rss parse class
class Html extends  Model
{

	protected $Count=0;
    protected $Template;
	
    /**
     * constructor : 
     * @return object
     */
	public function __construct()
	{
	}
		
	public function parseComment($htmlCode,$feedArray,$extrahtml = '')
	{
		$returnCode = '';
		$adNum = count($feedArray);
		if(is_array($feedArray) && $htmlCode != '')
		{
			for($m=0;$m<$adNum;$m++)
			{
				$blockCode = $htmlCode;
				foreach($feedArray[$m] as $rkey => $rval)
				{
					$blockCode = str_replace("{".$rkey."}",$rval,$blockCode);
				}
				$returnCode .= $blockCode;
			}
			if($extrahtml != '')
				$returnCode .= $extrahtml;
		} else {
			$returnCode = $htmlCode;
		}
		$returnCode = $this->replace_tag($returnCode);
		return $returnCode;
	}
	
	public function replace_tag($resultBase)
	{
		$resultBase = str_replace('&lt;', '<', $resultBase);
		$resultBase = str_replace('&gt;', '>', $resultBase);
		return $resultBase;
	}

	public function replaceTestingURL($htmlCode,$server,$path)
	{
		if (APPLICATION_ENVIRONMENT == 'TESTING') {
			$htmlCode = str_replace(array('//'.TESTING_DOMAIN,'//www.'.TESTING_DOMAIN,'//WWW.'.TESTING_DOMAIN),"//$server",$htmlCode);
		}
		
		return $htmlCode;
	}

	public function parseArticleModule($htmlCode,$article)
	{
		$htmlCode = !empty($htmlCode)?$htmlCode:'';
		if(is_array($article) && !empty($htmlCode))
		{
			$htmlCode = str_replace('{ARTICLE_TITLE}',@$article['article_title'],$htmlCode);
			$htmlCode = str_replace('{ARTICLE_SUMMARY}',@$article['article_summary'],$htmlCode);
			$htmlCode = str_replace('{ARTICLE_CONTENT}',@$article['article_content'],$htmlCode);
		}
		return $htmlCode;
	}
	
	public function parseImageModule($htmlCode,$images,$sources,$channels)
	{
		$htmlCode = !empty($htmlCode)?$htmlCode:'';
		$allImageHtml = '';
		if(is_array($images) && !empty($htmlCode))
		{
			if(!empty($sources)) 
			{
				$src = 1;
				foreach($sources as $channel) {
					$channel = strtolower($channel);
					$imageHtml = '';
					foreach( $images as  $content) 
					{
						if ($channel == strtolower($content['content_source']))
						{
							$imageHtml .= '<li>';
							$imageHtml .= '<img src="'.$content['content_photo_src'].'" alt="'.stripslashes(strip_tags($content['content_title'])).'" />';
							$imageHtml .= $this->anchor($content['content_link'], '', array('target'=> '_blank',  'class' => "link"));
							$imageHtml .= '<span  class="description">'.stripslashes($content['content_title']).'</span>';
							$imageHtml .= '</li>';
						}	
					}
					$allImageHtml .= $imageHtml;
					$htmlCode = str_ireplace("{IMAGE_LIST_$src}", $imageHtml, $htmlCode);
					$url = '#';
					$chanelName = '';
					if (!empty($imageHtml)) {
							$url = isset($channels[$channel])?$channels[$channel]['channel_url']:'';
							$chanelName = isset($channels[$channel])?$channels[$channel]['channel_name']:"Images from $channel";
					} 
					$htmlCode = str_ireplace("{IMAGE_SOURCELINK_$src}", $url, $htmlCode);
					$htmlCode = str_ireplace("{IMAGE_SOURCETITLE_$src}", $chanelName, $htmlCode);
					$src++;
				}
			}
		}
		// replace all Image if required
		$htmlCode = str_ireplace("{IMAGE_LIST}", $allImageHtml, $htmlCode);
		
		// cleanup
		for($i=0; $i <= 10 ; $i++) {
			$htmlCode = str_ireplace(array("{IMAGE_LIST_$i}","{IMAGE_SOURCELINK_$i}","{IMAGE_SOURCETITLE_$i}"), '', $htmlCode);
		}
		
		return $htmlCode;
	}

	public function parseVideoModule($htmlCode,$videos,$sources,$channels)
	{
		$htmlCode = !empty($htmlCode)?$htmlCode:'';
		$videoList = '';
		if(is_array($videos) && !empty($htmlCode))
		{
			if(!empty($sources)) 
			{
				foreach($sources as $channel) 
				{
					$channel = strtolower($channel);
					$videoHtml = '';
					foreach( $videos as  $content) 
					{
						if ($channel == strtolower($content['content_source']))
						{
							$videoHtml .= '<li>';
							$videoHtml .= $this->anchor('/display/video/result.php?hplink='.urlencode($content['content_link']), '<img src="'.$content['content_photo_src'].'" alt="'.stripslashes(strip_tags($content['content_title'])).'" />', array('target'=> '_self'));
							$videoHtml .= '<span  class="description"><b>'.ucwords($channel).' Video:</b>'.stripslashes($content['content_title']).'</span>';
							$videoHtml .= '</li>'.PHP_EOL;
						}	
					}
					if (!empty($videoHtml)) {
						$url 		= isset($channels[$channel])?$channels[$channel]['channel_url']:'';
						$chanelName	= isset($channels[$channel])?$channels[$channel]['channel_name']:"$channel ";
						$title[] 	= $this->anchor($url, ucwords($chanelName), array('target'=> '_blank'));
						$videoList  .= $videoHtml;
					} 
				}
				if (!empty($videoList)) {
					$htmlCode = str_ireplace('{VIDEO_SOURCES_TITLES}', implode(' ',$title), $htmlCode);
					$htmlCode = str_ireplace('{VIDEO_LIST}', $videoList, $htmlCode);
				} 
			}
		}
		return $htmlCode;
	}
	

	public function parsePollModule($htmlCode,$polldata,$voted){
		$htmlCode = !empty($htmlCode)?$htmlCode:'';
		if(is_array($polldata) && !empty($polldata) && !empty($htmlCode))
		{			
			$htmlCode = str_replace('{POLL_TITLE}',@$polldata['name'],$htmlCode);
			$htmlCode = str_replace('{POLL_SUMMARY}',@$polldata['description'],$htmlCode);			
			if($voted)
			{
				$url = '?';
				foreach($polldata['vote_detail'] as $k=>$v){
					$url .= $k."=".$v."&";	
				}
				$content = "<img src='../standard/helpers/chartGenerator.php".$url."' width='240px' />";
			}
			else{
				$url = explode('?',$_SERVER['REQUEST_URI']);
				$count = 0;			
				$content = "<form name='vote_form' id='vote_form' action='".$url[0]."' method = 'GET'><ul>";	
				foreach($polldata['vote_detail'] as $k=>$v){
					$content .= "<li><span>".$k."</span><input type='radio' name='vote' value='".$count."' ></li>";	
					$count++;
				}
				$content .= "</ul><input type='hidden' name='domain_id' value='".$polldata['domain_id']."' ><input type='hidden' name='poll_id' value='".$polldata['poll_id']."' ><input type='hidden' name='action' value='voting' ><button id='poll_button' >Go</button></form> <style>
				#poll, #poll_title, #poll_summary{width:250px;}
				#poll_title{margin:4px; text-align:center;}
				#poll_summary{text-align:justify; padding-top:10px; padding-bottom:5px;}
				#poll_content{width:200px; float:right;}
				#poll_content span{width:120px; float:left;}
				#poll_content .poll_item{width:25px!important; float:left; margin:0!important;}
				#poll_content div{float:left; height: 30px; clear:both}
				#poll_content li{clear:both}
				#poll_button{clear:both; text-align:center}
				</style>";
				
				
				$url = '?';
				foreach($polldata['vote_detail'] as $k=>$v){
					$url .= $k."=".$v."&";	
				}
				$content .= "<img src='../standard/helpers/chartGenerator.php".$url."' width='240px' />";
			}
			$htmlCode = str_replace('{POLL_CONTENT}',$content,$htmlCode);
		}
		return $htmlCode;
	}

	public function parseArticleFeedModule($htmlCode,$articles,$sources,$channels)
	{
		$htmlCode = !empty($htmlCode)?$htmlCode:'';
		$htmlResponse = '';
		if(is_array($articles) && !empty($htmlCode))
		{
			if(!empty($sources)) 
			{
				$size = count($articles) > 5?300: (count($articles) > 2?500:2000);
				foreach($sources as $channel) {
					$channel = strtolower($channel);
					$feedHtml = '';
					foreach( $articles as  $content) 
					{
						// get the list of articles
						if ($channel == strtolower($content['content_source']))
						{
							$content['content_photo_src'] = isset($content['content_photo_src'])?trim($content['content_photo_src']):'';
							$feedHtml .= '<li><div class="articleFeed">';
							$feedHtml .= !empty($content['content_photo_src'])?'<img src="'.$content['content_photo_src'].'" alt="'.stripslashes($content['content_title']).'" />':'';
							$feedHtml .= '<h2>'.stripslashes($content['content_title']).'</h2>';
							$feedHtml .= $this->cutArticle($content['content_main_content'],$size,$content['content_link']);
							$feedHtml .= '</div></li>'.PHP_EOL;
						}	
					}
					if (!empty($feedHtml)) {
						$html = str_ireplace('{ARTICLE_LIST}', $feedHtml, $htmlCode);
						$url = isset($channels[$channel])?$channels[$channel]['channel_url']:'';
						$chanelName = isset($channels[$channel])?$channels[$channel]['channel_name']:"Articles from $channel";
						$html = str_ireplace('{ARTICLE_SOURCELINK}', $url, $html);
						$html = str_ireplace('{ARTICLE_SOURCETITLE}', $chanelName, $html);
						$html = str_ireplace('{ARTICLE_SOURCE}', $channel, $html);
						$htmlResponse .= $html.PHP_EOL;
					} 
				}
			}
		}
		return $htmlResponse;
	}

	public function parseDirectoryModule($htmlCode,$directories)
	{
		$htmlCode = !empty($htmlCode)?$htmlCode:'';
		$htmlResponse = '';
		if(is_array($directories) && !empty($htmlCode))
		{
				foreach( $directories as  $content) 
				{
						$feedHtml = $htmlCode;
						$feedHtml = str_replace('{DIRECTORY_ID}',@$content['directory_id'],$feedHtml);
						$feedHtml = str_replace('{DIRECTORY_TITLE}',@$content['directory_title'],$feedHtml);
						$feedHtml = str_replace('{DIRECTORY_DESCRIPTION}',@$content['directory_description'],$feedHtml);
						$feedHtml = str_replace('{DIRECTORY_URL}',@$content['directory_url'],$feedHtml);
						$feedHtml = str_replace('{DIRECTORY_IMG}',@$content['directory_img'],$feedHtml);
						$feedHtml = str_replace('{DIRECTORY_FLAG}',@$content['directory_flag'],$feedHtml);
						$htmlResponse .= $feedHtml.PHP_EOL;
				}
		}
		return $htmlResponse;
	}
	
	public function parseEventModule($htmlCode,$events)
	{
		$htmlCode = !empty($htmlCode)?$htmlCode:'';
		$htmlResponse = '';
		if(is_array($events) && !empty($htmlCode))
		{			
				foreach( $events as  $content) 
				{
						$feedHtml = $htmlCode;
						$feedHtml = str_replace('{EVENT_ID}', @$content['event_id'],$feedHtml);
						$feedHtml = str_replace('{EVENT_EVENTFUL_ID}', @$content['event_eventful_id'],$feedHtml);
						$feedHtml = str_replace('{EVENT_TITLE}', @$content['event_title'],$feedHtml);
						$feedHtml = str_replace('{EVENT_KEYWORD}', @$content['event_keyword'],$feedHtml);
						$feedHtml = str_replace('{EVENT_DESCRIPTION}', @$content['event_description'],$feedHtml);
						$feedHtml = str_replace('{EVENT_URL}', @$content['event_url'],$feedHtml);
						$feedHtml = str_replace('{EVENT_START_TIME}', @$content['event_start_time'],$feedHtml);
						$feedHtml = str_replace('{EVENT_STOP_TIME}', @$content['event_stop_time'],$feedHtml);
						$feedHtml = str_replace('{EVENT_VENUE_NAME}', @$content['event_venue_name'],$feedHtml);
						$feedHtml = str_replace('{EVENT_VENUE_URL}', @$content['event_venue_url'],$feedHtml);
						$feedHtml = str_replace('{EVENT_VENUE_ADDRESS}', @$content['event_venue_address'],$feedHtml);
						$feedHtml = str_replace('{EVENT_CITY_NAME}', @$content['event_city_name'],$feedHtml);
						$feedHtml = str_replace('{EVENT_IMG}', @$content['event_image_url'],$feedHtml);
						$feedHtml = str_replace('{EVENT_IMG_WIDTH}', @$content['event_image_width'],$feedHtml);
			            $feedHtml = str_replace('{EVENT_IMG_HEIGHT}', @$content['event_image_height'],$feedHtml);
						$htmlResponse .= $feedHtml.PHP_EOL;
				}
		}
		return $htmlResponse;
	}
	
	public function parseQuestionModule($htmlCode,$questions)
	{
		$htmlCode = !empty($htmlCode)?$htmlCode:'';
		$htmlResponse = '';
		if(is_array($questions) && !empty($htmlCode))
		{
			foreach ($questions as $question) 
			{
				$feedHtml = $htmlCode;
				$feedHtml = str_replace('{QUESTION_ID}',@$question['question_id'],$feedHtml);
				$feedHtml = str_replace('{QUESTION_KEYWORD}',@$question['question_keyword'],$feedHtml);
				$feedHtml = str_replace('{QUESTION_CONTENT}',@$question['question_content'],$feedHtml);
				$feedHtml = str_replace('{QUESTION_SUBJECT}',@$question['question_subject'],$feedHtml);
				$feedHtml = str_replace('{QUESTION_DATE}',@$question['question_date'],$feedHtml);
				$feedHtml = str_replace('{QUESTION_USERNAME}',@$question['question_username'],$feedHtml);
				$feedHtml = str_replace('{QUESTION_USER_PHOTO}',@$question['question_user_photo'],$feedHtml);
				$feedHtml = str_replace('{QUESTION_ANSWER}',@$question['question_answer'],$feedHtml);
				$feedHtml = str_replace('{QUESTION_ANSWERER}',@$question['question_answerer'],$feedHtml);
				$answerSummary = '';
				$answerArray = !empty($question['question_answer'])?explode(" ", $question['question_answer']):array();
				for($i=0; $i<15 && $i<count($answerArray) ; $i++){ $answerSummary .=" ".$answerArray[$i]; }
				$feedHtml = str_replace('{QUESTION_ANSWER_SUMMARY}',@$answerSummary,$feedHtml);
				$htmlResponse .= $feedHtml.PHP_EOL;
			}	
		}
		return $htmlResponse;
	}

	public function parseQuestionAnswers($htmlCode,$answers)
	{
		$htmlCode = !empty($htmlCode)?$htmlCode:'';
		$htmlResponse = '';
		if(is_array($answers) && !empty($htmlCode))
		{			
			// Get answers block
			$htmlBlock = '';
			if (stripos($htmlCode, '{ANSWER_BLOCK') !== false)
			{
				$iniPos = stripos($htmlCode, '{ANSWER_BLOCK_BEGIN}');
				$endPos = stripos($htmlCode, '{ANSWER_BLOCK_END}');
				$htmlBlock 	= $htmlCode;
				while ($iniPos !== false && $endPos !== false && $endPos > $iniPos)
				{
					$htmlCode  	= substr($htmlCode, 0, $iniPos).'{ANSWER_BLOCK}'.substr ($htmlCode, $endPos + 18);
					$htmlBlock 	= substr($htmlBlock, $iniPos + 20, $endPos - ($iniPos + 20));
					$iniPos 	= stripos($htmlBlock, '{ANSWER_BLOCK_BEGIN}');
					$endPos 	= stripos($htmlBlock, '{ANSWER_BLOCK_END}');
				}
			}
			// replace tags
			foreach( $answers as  $i => $content) 
			{
					// replace by block
					$feedHtml = $htmlBlock;
					$feedHtml = str_replace('{ANSWER_ID}', @$content['answer_id'],$feedHtml);
					$feedHtml = str_replace('{ANSWER_SUBJECT}', @$content['answer_subject'],$feedHtml);
					$feedHtml = str_replace('{ANSWER_SHORT_ANSWER}', @$content['answer_short_answer'],$feedHtml);
					$feedHtml = str_replace('{ANSWER_KEYWORD}', @$content['answer_keyword'],$feedHtml);
					$feedHtml = str_replace('{ANSWER_CONTENT}', @$content['answer_content'],$feedHtml);
					$feedHtml = str_replace('{ANSWER_LINK}', @$content['answer_link'],$feedHtml);
					$feedHtml = str_replace('{ANSWER_TYPE}', @$content['answer_type'],$feedHtml);
					$feedHtml = str_replace('{ANSWER_USER_NAME}', @$content['answer_user_name'],$feedHtml);
					$feedHtml = str_replace('{ANSWER_USER_PHOTO}', @$content['answer_user_photo'],$feedHtml);
					$htmlResponse .= $feedHtml.PHP_EOL;
					// Replace by id
					$k = $i + 1;
					$htmlCode = str_replace("{ANSWER_ID_$k}", @$content['answer_id'],$htmlCode);
					$htmlCode = str_replace("{ANSWER_SUBJECT_$k}", @$content['answer_subject'],$htmlCode);
					$htmlCode = str_replace("{ANSWER_SHORT_ANSWER_$k}", @$content['answer_short_answer'],$htmlCode);
					$htmlCode = str_replace("{ANSWER_KEYWORD_$k}", @$content['answer_keyword'],$htmlCode);
					$htmlCode = str_replace("{ANSWER_CONTENT_$k}", @$content['answer_content'],$htmlCode);
					$htmlCode = str_replace("{ANSWER_LINK_$k}", @$content['answer_link'],$htmlCode);
					$htmlCode = str_replace("{ANSWER_TYPE_$k}", @$content['answer_type'],$htmlCode);
					$htmlCode = str_replace("{ANSWER_USER_NAME_$k}", @$content['answer_user_name'],$htmlCode);
					$htmlCode = str_replace("{ANSWER_USER_PHOTO_$k}", @$content['answer_user_photo'],$htmlCode);
			}
			$htmlCode = str_replace("{ANSWER_BLOCK}", $htmlResponse,$htmlCode);
		}
		return $htmlCode;
	}
	
	public function parseTypeAnswers($htmlCode,$answers,$typeArray=array())
	{
		$htmlCode = !empty($htmlCode)?$htmlCode:'';
		$htmlResponse = '';
		if(is_array($answers) && !empty($htmlCode) && is_array($typeArray) && !empty($typeArray))
		{			
			// Get answers block
			$htmlBlock = '';
			if (stripos($htmlCode, '{ANSWER_BLOCK') !== false)
			{
				$iniPos = stripos($htmlCode, '{ANSWER_BLOCK_BEGIN}');
				$endPos = stripos($htmlCode, '{ANSWER_BLOCK_END}');
				$htmlBlock 	= $htmlCode;
				while ($iniPos !== false && $endPos !== false && $endPos > $iniPos)
				{
					$htmlCode  	= substr($htmlCode, 0, $iniPos).'{ANSWER_BLOCK}'.substr ($htmlCode, $endPos + 18);
					$htmlBlock 	= substr($htmlBlock, $iniPos + 20, $endPos - ($iniPos + 20));
					$iniPos 	= stripos($htmlBlock, '{ANSWER_BLOCK_BEGIN}');
					$endPos 	= stripos($htmlBlock, '{ANSWER_BLOCK_END}');
				}
			}
			
			foreach( $typeArray as $k => $type)
			{
				$iniPos = stripos($htmlBlock, '{ANSWER_TYPE_'.strtoupper($type).'}');
				$endPos = stripos($htmlBlock, '{ANSWER_TYPE_'.strtoupper($type).'_END}');
				$length = strlen('{ANSWER_TYPE_'.strtoupper($type).'}');
				$typeBlock = substr($htmlBlock, $iniPos + $length, $endPos - ($iniPos + $length));
				// replace tags
				if (!empty($typeBlock))
				{
					foreach( $answers as  $i => $content) 
					{
						if (!empty($content['answer_type']) && strtolower($content['answer_type']) == strtolower($type))	
						{	
							// replace by block
							$feedHtml = $typeBlock;
							$feedHtml = str_replace('{ANSWER_ID}', @$content['answer_id'],$feedHtml);
							$feedHtml = str_replace('{ANSWER_SUBJECT}', @$content['answer_subject'],$feedHtml);
							$feedHtml = str_replace('{ANSWER_SHORT_ANSWER}', @$content['answer_short_answer'],$feedHtml);
							$feedHtml = str_replace('{ANSWER_KEYWORD}', @$content['answer_keyword'],$feedHtml);
							$feedHtml = str_replace('{ANSWER_CONTENT}', @$content['answer_content'],$feedHtml);
							$feedHtml = str_replace('{ANSWER_LINK}', @$content['answer_link'],$feedHtml);
							$feedHtml = str_replace('{ANSWER_TYPE}', @$content['answer_type'],$feedHtml);
							$feedHtml = str_replace('{ANSWER_USER_NAME}', @$content['answer_user_name'],$feedHtml);
							$feedHtml = str_replace('{ANSWER_USER_PHOTO}', @$content['answer_user_photo'],$feedHtml);
							$htmlResponse .= $feedHtml.PHP_EOL;
						}								
					}
				}
			}
						
			$htmlCode = str_replace("{ANSWER_BLOCK}", $htmlResponse,$htmlCode);
		}
		return $htmlCode;
	}
	
	public function parseShoppingModule($htmlCode,$products)
	{
		$htmlCode = !empty($htmlCode)?$htmlCode:'';
		$htmlResponse = '';
		if(is_array($products) && !empty($htmlCode))
		{
			foreach ($products as $product) 
			{
				$feedHtml = $htmlCode;
				$feedHtml = str_replace('{PRODUCT_ID}',@$product['product_id'],$feedHtml);
				$feedHtml = str_replace('{PRODUCT_LINK}',@$product['product_url'],$feedHtml);
				$feedHtml = str_replace('{PRODUCT_TITLE}',@$product['product_name'],$feedHtml);
				$feedHtml = str_replace('{PRODUCT_ITEMS}',@$product['product_num_items'],$feedHtml);
				$feedHtml = str_replace('{PRODUCT_CATEGORY}',@$product['product_category'],$feedHtml);
				$feedHtml = str_replace('{PRODUCT_PRICE}',@$product['product_price'],$feedHtml);
				$feedHtml = str_replace('{PRODUCT_IMAGE}',@$product['product_image'],$feedHtml);
				$feedHtml = str_replace('{PRODUCT_SOURCE}',@$product['product_source'],$feedHtml);
				$htmlResponse .= $feedHtml.PHP_EOL;
			}	
		}
		return $htmlResponse;
	}

	public function parseShoppingDetail($htmlCode,$products)
	{
		$htmlCode = !empty($htmlCode)?$htmlCode:'';
		$htmlResponse = '';
		if(is_array($products) && !empty($htmlCode))
		{
			foreach ($products as $product) 
			{
				$feedHtml = $htmlCode;
				$feedHtml = str_replace('{PRODUCT_ID}',@$product['product_id'],$feedHtml);
				$feedHtml = str_replace('{PRODUCT_LINK}',@$product['product_url'],$feedHtml);
				$feedHtml = str_replace('{PRODUCT_TITLE}',@$product['product_name'],$feedHtml);
				$feedHtml = str_replace('{PRODUCT_ITEMS}',@$product['product_num_items'],$feedHtml);
				$feedHtml = str_replace('{PRODUCT_CATEGORY}',@$product['product_category'],$feedHtml);
				$feedHtml = str_replace('{PRODUCT_PRICE}',@$product['product_price'],$feedHtml);
				$feedHtml = str_replace('{PRODUCT_IMAGE}',@$product['product_image'],$feedHtml);
				$feedHtml = str_replace('{PRODUCT_SOURCE}',@$product['product_source'],$feedHtml);
				$feedHtml = str_replace('{PRODUCT_MANUFACTURER}',@$product['product_manufacturer'],$feedHtml);
				$feedHtml = str_replace('{PRODUCT_DESCRIPTION}',@$product['product_description'],$feedHtml);
				$feedHtml = str_replace('{PRODUCT_DETAILS}',@$product['product_details'],$feedHtml);
				$feedHtml = str_replace('{PRODUCT_FEATURES}',@$product['product_features'],$feedHtml);
				$feedHtml = str_replace('{PRODUCT_LOWEST_PRICE}',@$product['product_lowest_price'],$feedHtml);
				$feedHtml = str_replace('{PRODUCT_DIMENSION}',@$product['product_dimension'],$feedHtml);
				$feedHtml = str_replace('{PRODUCT_WEIGHT}',@$product['product_weight'],$feedHtml);
				$feedHtml = str_replace('{PRODUCT_LOWEST_USED_PRICE}',@$product['product_lowest_used_price'],$feedHtml);
				$feedHtml = str_replace('{PRODUCT_LOWEST_REFURBISHED_PRICE}',@$product['product_lowest_refurbished_price'],$feedHtml);
				$feedHtml = str_replace('{PRODUCT_DISCLAIMER}',@$product['product_disclaimer'],$feedHtml);
								
				foreach ($product['product_reviews'] as $i => $rev)
				{
					$j = $i + 1;
					$feedHtml = str_replace("{PRODUCT_REVIEWSOURCE_$j}",$rev['Source'],$feedHtml);
					$feedHtml = str_replace("{PRODUCT_REVIEWCONTENT_$j}",$rev['Content'],$feedHtml);
				}
				$htmlResponse .= $feedHtml.PHP_EOL;
			}	
		}
		return $htmlResponse;
	}

	private function cutArticle ($content,$size,$link) {
		$content = stripslashes($content);
		if (($size > 0) && (strlen($content) > $size)) {
			//$content = substr($content, 0, $size).'[ '.$this->anchor($link, 'read more', array('target'=> '_blank')).' ]';
		}
		$content = $content.' [ '.$this->anchor($link, 'read more', array('class' => 'readmore', 'target' => '_blank')).' ]';
		return $content;
	}
	
	public function parseRssModule ($htmlCode,$rss,$sources,$channels)
	{
		$htmlCode = !empty($htmlCode)?$htmlCode:'';
		$htmlResponse = '';
		
		if(is_array($rss) && !empty($htmlCode))
		{
			if(!empty($sources)) 
			{
				foreach($sources as $channel) {
					$channel = strtolower($channel);
					$rssHtml = isset($rss[$channel])?$rss[$channel]:'';
					if (!empty($rssHtml)) 
					{
						$html = str_ireplace('{RSS_LIST}', $rssHtml, $htmlCode);
						$url = isset($channels[$channel])?$channels[$channel]['channel_url']:'';
						$chanelName = isset($channels[$channel])?$channels[$channel]['channel_name']:$channel;
						$html = str_ireplace('{RSS_SOURCELINK}', $url, $html);
						$html = str_ireplace('{RSS_SOURCETITLE}', $chanelName, $html);
						$html = str_ireplace('{RSS_SOURCE}', $channel, $html);
						$htmlResponse .= $html.PHP_EOL;					}
				}
			}
		}
		return $htmlResponse;
	}

	
	public function parseNewsModule($htmlCode,$news,$sources,$channels)
	{
		$htmlCode = !empty($htmlCode)?$htmlCode:'';
		$htmlResponse = '';
		
		if(is_array($news) && !empty($htmlCode))
		{
			if(!empty($sources)) 
			{
				foreach($sources as $channel) {
					$channel = strtolower($channel);
					$feedHtml = '';
					foreach( $news as  $content) 
					{
						if ($channel == strtolower($content['content_source']))
						{
							$content['content_photo_src'] = isset($content['content_photo_src'])?trim($content['content_photo_src']):'';
							$feedHtml .= '<li><div class="News">';
							$feedHtml .= !empty($content['content_photo_src'])?'<img src="'.$content['content_photo_src'].'" alt="'.stripslashes($content['content_title']).'" />':'';
							$feedHtml .= '<h2>'.stripslashes($content['content_title']).'</h2>';
							$feedHtml .= $this->cutArticle($content['content_main_content'],0,$content['content_link']);
							$feedHtml .= '</div></li>'.PHP_EOL;
						}	
					}
					if (!empty($feedHtml)) {
						$html = str_ireplace('{NEWS_LIST}', $feedHtml, $htmlCode);
						$url = isset($channels[$channel])?$channels[$channel]['channel_url']:'';
						$chanelName = isset($channels[$channel])?$channels[$channel]['channel_name']:"News from $channel";
						$html = str_ireplace('{NEWS_SOURCELINK}', $url, $html);
						$html = str_ireplace('{NEWS_SOURCETITLE}', $chanelName, $html);
						$html = str_ireplace('{NEWS_SOURCE}', $channel, $html);
						$htmlResponse .= $html.PHP_EOL;
					}
				}
			}
		}
		return $htmlResponse;
	}
	
	
	public function parseForumModule($htmlCode,$forums,$sources,$channels)
	{
		$htmlCode = !empty($htmlCode)?$htmlCode:'';
		$htmlResponse = '';
		
		if(is_array($forums) && !empty($htmlCode))
		{
			if(!empty($sources)) 
			{
				foreach($sources as $channel) {
					$channel = strtolower($channel);
					$feedHtml = '';
					foreach( $forums as  $content) 
					{
						if ($channel == strtolower($content['content_source']))
						{
							$content['content_photo_src'] = isset($content['content_photo_src'])?trim($content['content_photo_src']):'';
							$feedHtml .= '<li><div class="Forum">';
							$feedHtml .= !empty($content['content_photo_src'])?'<img src="'.$content['content_photo_src'].'" alt="'.stripslashes($content['content_title']).'" />':'';
							$feedHtml .= '<h2>'.stripslashes($content['content_title']).'</h2>';
							$feedHtml .= $this->cutArticle($content['content_main_content'],0,$content['content_link']);
							$feedHtml .= '</div></li>'.PHP_EOL;
						}	
					}
					if (!empty($feedHtml)) {
						$html = str_ireplace('{FORUM_LIST}', $feedHtml, $htmlCode);
						$url = isset($channels[$channel])?$channels[$channel]['channel_url']:'';
						$chanelName = isset($channels[$channel])?$channels[$channel]['channel_name']:"People from $channel";
						$html = str_ireplace('{FORUM_SOURCELINK}', $url, $html);
						$html = str_ireplace('{FORUM_SOURCETITLE}', $chanelName, $html);
						$html = str_ireplace('{FORUM_SOURCE}', $channel, $html);
						$htmlResponse .= $html.PHP_EOL;
					} 
				}
			}
		}
		return $htmlResponse;
	}
	
	
	public function parseTopicModule($htmlCode,$topics, $moduleStr){
		$moduleStr = strtolower($moduleStr);
		for($i=0; $i<16; $i++){
			$search[] = '{RELATED'.$i.'}';
		}
		$replace = explode(',', $topics);
		$htmlCode = str_replace($search, $replace, $htmlCode);
		$htmlCode = str_replace('{MODULES}', $moduleStr, $htmlCode);
		return $htmlCode;
	}
	
	public function parseNewsSubscribeModule($htmlCode,$domain){
		$htmlCode = str_replace('{SUBSCRIBE_DOMAIN}', $domain, $htmlCode);
		return $htmlCode;
	}
	
	public function parseDisplayVideo($video)
	{
		
		$htmlCode = empty($video)?'':
			'<object width="520" height="385">
				<param name="movie" value="'.$video.'"></param>
				<param name="allowFullScreen" value="true"></param>
				<param name="allowscriptaccess" value="always"></param>
				<embed src="'.$video.'" 
					type="application/x-shockwave-flash" 
					allowscriptaccess="always" 
					allowfullscreen="true" 
					width="520" 
					height="385">
				</embed>
			 </object>';
		return $htmlCode;
	}
	
	public function replaceHtmlTags($htmlCode,$replaceArray) 
	{
		if (is_array($replaceArray)) 
		{
			foreach($replaceArray as $rkey => $rval)
			{
				$htmlCode = str_replace("{".$rkey."}",$rval,$htmlCode);
			}
		}
		return $htmlCode;
	}
	
	public function insertHtmlCode($htmlCode,$type,$Code) 
	{
		if (!empty($Code)) 
		{
			switch ($type) {
				case 'js' :	$htmlCode = '<script language="javascript">'.$Code.'</script>'.PHP_EOL.$htmlCode;
							break;
				
				case 'css':	$htmlCode = '<style>'.$Code.'</style>'.PHP_EOL.$htmlCode;
							break;
							
				case 'jsLoad' :	$htmlCode .= PHP_EOL.'<script language="javascript"> $(window).load(function () { '.$Code.' }); </script>'.PHP_EOL;
							break;
			}
		}
		return $htmlCode;
	}
	/**
	 * Anchor Link
	 *
	 * Creates an anchor based on the local URL.
	 *
	 * @access	public
	 * @param	string	the URL
	 * @param	string	the link title
	 * @param	mixed	any attributes
	 * @return	string
	 */
	public function anchor($uri = '', $title = '', $attributes = '')
	{
		$title = (string) $title;
		$site_url = (!preg_match('|^\w+://| i', $uri)) ? $this->__fixURL($uri) : $uri;

		if (trim($title) == '')
		{
			$title = '';
		}

		if ($attributes != '')
		{
			$attributes = $this->__parse_attributes($attributes);
		}

		return '<a href="'.$site_url.'"'.$attributes.'>'.$title.'</a>';
	}
	
	private function __fixURL ($uri) 
	{
		if (empty($uri)) {
			$uri = '{DOMAIN}';
		} else {
			$uri = str_replace('\\','/',$uri);
			$parts = explode("/",$uri);
			$uri   = (!preg_match('|^/(.)+| i', $uri)) ?'{HOME}'.implode('/', $parts):implode('/', $parts);
		}
		return $uri;
	}
	/**
	 * Parse out the attributes
	 *
	 * Some of the functions use this
	 *
	 * @access	private
	 * @param	array
	 * @param	bool
	 * @return	string
	 */
	private function __parse_attributes($attributes, $javascript = FALSE)
	{
		if (is_string($attributes))
		{
			return ($attributes != '') ? ' '.$attributes : '';
		}

		$att = '';
		foreach ($attributes as $key => $val)
		{
			if ($javascript == TRUE)
			{
				$att .= $key . '=' . $val . ',';
			}
			else
			{
				$att .= ' ' . $key . '="' . $val . '"';
			}
		}

		if ($javascript == TRUE AND $att != '')
		{
			$att = substr($att, 0, -1);
		}

		return $att;
	}
	
}


?>