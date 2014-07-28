<?php
/**
 * Domains
 * Author: Gordon Ye on 08/06/2010
**/
require_once('config.php');
require_once('header.php');

$Site = Site::getInstance($db);
$Profile = new Profile();
$Feed = new Feed();
$Menu = Menu::getInstance($db);
$Article = Article::getInstance($db);

// Inic variables
$lm1 = '';
$lm2 = '';
$lm3 = '';
$lp1 = '';
$lp2 = '';
$lp3 = '';
$rm1 = '';
$rm2 = '';
$rm3 = '';
$rp1 = '';
$rp2 = '';
$rp3 = '';

if(isset($_REQUEST['action']) && $_REQUEST['action'] == 'update_domain')
{
	$domain_id = $_REQUEST['domain_id'];
	$updateDomain['domain_account_id'] 	= $_REQUEST['domain_account_id'];
	$updateDomain['domain_title'] 		= $_REQUEST['domain_title'];
	$updateDomain['domain_keyword'] 	= $_REQUEST['domain_keyword'];
	$updateDomain['domain_tracking_type']= $_REQUEST['domain_tracking_type'];
	$updateDomain['domain_feedtype'] 	= $_REQUEST['domain_feedtype'];
	$updateDomain['domain_feedid'] 		= $_REQUEST['domain_feedid'];
	$updateDomain['keywords_feeds'] 	= $Site->line_to_format($_REQUEST['keywords_feeds']);
	$updateDomain['domain_updatedate'] 	= date('Y-m-d');
	$updateDomain['domain_analytics']	 = isset($_REQUEST['domain_analytics'])?$_REQUEST['domain_analytics']:'';
	$updateDomain['domain_product_category'] = isset($_REQUEST['domain_product_category'])?$_REQUEST['domain_product_category']:'';
	
	
	$Site->save_domain($updateDomain,$domain_id);
}
if(isset($_REQUEST['action']) && $_REQUEST['action'] == 'delete_art')
{
	$pid = $_REQUEST['pid'];
	$Article->unlink_this_article($pid);
}
if(isset($_REQUEST['action']) && $_REQUEST['action'] == 'delete_menu')
{
	$pid = $_REQUEST['pid'];
	$Menu->del_menu($pid);
}
if(isset($_REQUEST['action']) && $_REQUEST['action'] == 'add_menu')
{
	$menu_name = $_REQUEST['menu_name'];
	$domain_id = $_REQUEST['domain_id'];
	$menu_article = $_REQUEST['menu_article'];
	$menues = array('menu_domain_id' => $domain_id
					,'menu_name' => $menu_name
					,'menu_name_display' => $menu_name
					,'menu_article' => $menu_article
					);
	$id = $Menu->save_menu($menues);
}

function trim_value(&$value) 
{ 
    $value = trim($value); 
}

$domain_id = $_REQUEST['domain_id'];
$domainInfo = $Site->get_domain_info($domain_id);

$profileName = $Profile->getProfileInfo($domainInfo['domain_profile_id']);
$profileName = $profileName['profile_name'];

$accountArray = $Profile->getAccounts($domainInfo['domain_profile_id']);

?>

<script>
var sth=0;

$(function(){		
	go_back();	
});

// pre-submit callback 
function showRequest(formData, jqForm, options) { 
    // formData is an array; here we use $.param to convert it to a string to display it 
    // but the form plugin does this for you automatically when it submits the data 
    var queryString = $.param(formData); 
    // jqForm is a jQuery object encapsulating the form element.  To access the 
    // DOM element for the form do this: 
    // var formElement = jqForm[0]; 
    alert('About to submit: \n\n' + queryString);  
    // here we could return false to prevent the form from being submitted; 
    // returning anything other than false will allow the form submit to continue 
    return true; 
}  
// post-submit callback 
function showResponse(responseText, statusText, xhr, $form)  { 
	// for normal html responses, the first argument to the success callback 
    // is the XMLHttpRequest object's responseText property 
 
    // if the ajaxSubmit method was passed an Options Object with the dataType 
    // property set to 'xml' then the first argument to the success callback 
    // is the XMLHttpRequest object's responseXML property 
 
    // if the ajaxSubmit method was passed an Options Object with the dataType 
    // property set to 'json' then the first argument to the success callback 
    // is the json data object returned by the server 
	
    //alert('status: ' + statusText + '\n\nresponseText: \n' + responseText + \n\nThe output div should have already been updated with the responseText.'); 
} 

function go_back(){
	$('#edit_landing_page, #edit_result_page, #arrow_bar').hide();
	$('#main_content').show(500);
}

function pView(page) {
	$('#arrow_bar').show(500);
	if(page=='landing_page'){
		$('#main_content, #edit_result_page').hide();
		$('#edit_landing_page').show(500);
		sth = 0;
	}
	if(page=='result_page'){
		$('#main_content, #edit_landing_page').hide();
		$('#edit_result_page').show(500);
		sth = 1;
	}
}

function switch_edit(){
	$('#arrow_bar').show(500);
	if(sth==1){
		$('#main_content, #edit_result_page').hide();
		$('#edit_landing_page').show(500);
		sth = 0;
	}
	else if(sth==0){
		$('#main_content, #edit_landing_page').hide();
		$('#edit_result_page').show(300);
		sth = 1;
	}
}
</script>

<div id="main_content">
	<table width="100%" border="0" cellspacing="0" cellpadding="0">
		<tr>

			<td class="brdrL">&nbsp;</td>
			<td valign="top" id="main" style="width:100%">
			
			
			<!-- *** START MAIN CONTENTS  *** -->
			
	<div>
         <span class="txtHdr" style="float:left;">Domain Detail for <a href="http://<?php echo $domainInfo['domain_url'];?>/" target="_blank"><?php echo $domainInfo['domain_url'];?></a></span>
    </div>
<br>
<br>
<table align="center"  border="0" cellpadding="3" cellspacing="0" width="100%" id="boxGray">
		<tr>	
		<td align="center" valign="top" colspan="2">
			<table width="100%">
			<tr>
				<td class="greenHdr" width="20%">Detail</td>
		        <td class="greenHdr" align="left" width="40%">Articles</td> 
		        <td class="greenHdr" align="left" width="20%">Edit Images</td>
		        <td class="greenHdr" align="left" width="20%">Custom Menu</td>
			</tr>
			<tr>
			<td width="22%" valign="top">
			<form action="/edit_domain.php" method="POST" name="d_design">
			<input type="hidden" name="action" value="update_domain">
			<input type="hidden" name="domain_id" id="domain_id" value="<?php echo $domainInfo['domain_id'];?>">
			<table border="0" cellspacing="4">
				<tr>
					<td align="right" valign="top" width="160px">
					Domain:
					</td>
					<td align="left" valign="top">
					<b><?php echo $domainInfo['domain_url'];?></b>

					</td>
				</tr>
				<tr>
					<td align="right" valign="top">
					Profile:
					</td>
					<td align="left" valign="top">
					<b><?php echo $profileName;?></b>

					</td>
				</tr>
                <tr>
					<td align="right" valign="top">
					Account:
					</td>
					<td align="left" valign="top">
                    	<select name="domain_account_id">
					<?php
					foreach($accountArray as $accountInfo)
					{
						echo '<option value="'.$accountInfo['account_id'].'"';
						if($accountInfo['account_id'] == $domainInfo['domain_account_id'])
							echo ' selected';
						echo '>'.$accountInfo['account_name'].'</option>';
					}
					?>
					</select>
					</td>
				</tr>
				<tr>
					<td align="right" valign="top">
					Title:
					</td>
					<td align="left" valign="top">
					<input type="text" name="domain_title" value="<?php echo $domainInfo['domain_title'];?>" style="width:200px;"/>
					</td>

				</tr>
				<tr>
					<td align="right" valign="top">
					Keyword:
					</td>
					<td align="left" valign="top">
					<input type="text" name="domain_keyword" value="<?php echo $domainInfo['domain_keyword'];?>" style="width:150px;"/>
					</td>
				</tr>
				<tr>
					<td align="right" valign="top">
					Tracking type:
					<td align="left" valign="top">
					<select name="domain_tracking_type">
	                <option value='keyword'<?php echo $domainInfo['domain_tracking_type'] == 'keyword' ? ' selected' : '';?>>Keyword (keywords param in url)</option>
                    <option value='source'<?php echo $domainInfo['domain_tracking_type'] == 'source' ? ' selected' : '';?>>Source (traffictype param in url)</option>
	                </select>
					</td>
				</tr>
				<tr>
					<td align="right" valign="top">
					Tracking List:
					</td>
					<td align="left" valign="top">
					<textarea name="keywords_feeds" cols="90" rows="12" style="width:150px;height:150px;"><?php echo str_replace('||', "\n", $domainInfo['keywords_feeds']);?></textarea>
					</td>
				</tr>
				<tr>
					<td align="right" valign="top"></td>
					<td align="left" valign="top"><b>Format:</b><br />keyword1,tracking1<br />keyword2,tracking2<br />keyword3,tracking3</td>
				</tr>
				<tr>
					<td align="right" valign="top">
					Feed Type:
					</td>
					<td align="left" valign="top">
					<select name="domain_feedtype">
	                <option value='TZ'<?php echo $domainInfo['domain_feedtype'] == 'TZ' ? ' selected' : '';?>>TrafficZ</option>
	                <option value='TZ-2'<?php echo $domainInfo['domain_feedtype'] == 'TZ-2' ? ' selected' : '';?>>TrafficZ 2 - Clean Referer</option>
                    <option value='TS'<?php echo $domainInfo['domain_feedtype'] == 'TS' ? ' selected' : '';?>>TrafficScoring</option>
                    <option value='TS-2'<?php echo $domainInfo['domain_feedtype'] == 'TS-2' ? ' selected' : '';?>>TrafficScoring 2 - Clean Referer</option>
	                <option value='VC'<?php echo $domainInfo['domain_feedtype'] == 'VC' ? ' selected' : '';?>>ValidClick</option>
	                <option value='OB'<?php echo $domainInfo['domain_feedtype'] == 'OB' ? ' selected' : '';?>>OB Media</option>
	                <option value='IS'<?php echo $domainInfo['domain_feedtype'] == 'IS' ? ' selected' : '';?>>InfoSpace</option>
	                <option value='TS'<?php echo $domainInfo['domain_feedtype'] == 'TS' ? ' selected' : '';?>>Traffic Scoring</option>
	                </select>
					</td>
				</tr>
				
				<tr>
					<td align="right" valign="top">
					Default Tracking ID:
					</td>
					<td align="left" valign="top">
					<input type="text" name="domain_feedid" value="<?php echo $domainInfo['domain_feedid'];?>" style="width:200px;"/>
					(Feed ID for OB)
					</td>
				</tr>
				<tr>
					<td align="right" valign="top">
					Analytics ID:
					</td>
					<td align="left" valign="top">
					<input type="text" name="domain_analytics" value="<?php echo $domainInfo['domain_analytics'];?>" style="width:200px;"/>
					</td>
				</tr>
				<tr>
					<td align="right" valign="top">
					Product Category:
					</td>
					<td align="left" valign="top">
					<input type="text" name="domain_product_category" value="<?php echo $domainInfo['domain_product_category'];?>" style="width:200px;"/>
					</td>
				</tr>
				
                <tr>
					<td align="center" valign="top" colspan="2">
					<input type="submit" id="tempChange" name="Submit" value="Save Changes">
					</td>
				</tr>
			</table>
			</form>
			</td>
            <td width="38%" valign="top">
            	<table border="0" cellspacing="4">
                <?php
				$i=1;
				$pResults = $Article->get_domain_articles($domainInfo['domain_url'],'ORDER BY article_id');
				foreach ($pResults as $pRow) 
				{
				
                echo'<tr>
                    	<td align="left" width="5">'.$i.'.
                        </td>
                        <td align="left"><a href="/edit_article.php?pid='.$pRow['article_id'].'&domain='.$domainInfo['domain_url'].'" target="_blank">'.$pRow['article_title'].'</a>
                        </td>
                        <td align="left" width="20"><a href="/edit_domain.php?action=delete_art&pid='.$pRow['article_id'].'&domain_id='.$domainInfo['domain_id'].'" title="delete article"><img src="/images/del.gif" border="0"></a>
                        </td>
                    </tr>';
					$i++;
				}
                    ?>
                    <tr>
					<td align="center" valign="top" colspan="2">
                    <form action="/edit_article.php" method="POST">
                    <input type="hidden" name="domain" value="<?php echo $domainInfo['domain_url'];?>" />
					<input type="submit" name="Submit" value="Add Custom Article">
                    </form>
					</td>
				</tr>
              	</table>
            </td>
            <td valign="top" width="20%">
            
            <div style="float:right; margin:10px 5px 10px 10px; width:120px; height:200px; border:1px #C00 dashed;  padding-top:50px; text-align:center; cursor:pointer" onclick="pView('result_page')">Result page</div> 
            <div style="margin:10px 5px 10px 10px;  width:120px; height:200px; border:1px #C00 dashed; padding-top:50px; text-align:center; cursor:pointer" onclick="pView('landing_page')">Landing page</div>
            </td>
			
            <td width="20%" valign="top">
            	<table border="0" cellspacing="4">
                <?php
				$i=1;
				$menuList = $Menu->get_domain_menus($domainInfo['domain_id']);
				foreach ($menuList as $mRow) 
				{
                echo'<tr>
                    	<td align="left" width="5">'.$i.'.
                        </td>
                        <td align="left">'.$mRow['menu_name_display'].'
                        </td>
                        <td align="left" width="20"><a href="/edit_domain.php?action=delete_menu&pid='.$mRow['menu_id'].'&domain_id='.$domainInfo['domain_id'].'" title="delete menu item"><img src="/images/del.gif" border="0"></a>
                        </td>
                    </tr>';
					$i++;
				}
                    ?>
                    <tr>
					<td align="center" valign="top" colspan="2">
                    <form action="/edit_domain.php" method="POST">
                    <input type="hidden" name="domain_id" value="<?php echo $domainInfo['domain_id'];?>" />
                    <input type="text" name="menu_name" value=""  style="width:100px;"/>
                    <input type="hidden" name="menu_article" value="1"/>
                    <input type="hidden" name="action" value="add_menu"/>
					<input type="submit" name="Submit" value="Add Item">
                    </form>
					</td>
				</tr>
              	</table>
            </td>
            
			</tr>
			</table>
		</td>
	</tr>
  </table>
</td>
<td rowspan="5" class="brdrR">&nbsp;</td>
</tr>
</table>			
	
</div>

<!-- Get Data for Page Edit -->

<?php

/*----- assign keyword to specified image according of their location---------*/
if(empty($domainInfo)) 
{
	echo 'fatal error!';
	exit;
}
$_relates = $Feed->loadRelates($domainInfo['domain_feedtype'], $domainInfo['domain_feedid'], $domainInfo['domain_keyword'], $domainInfo['domain_url']);
$relateString = $Feed->displayRelates($_relates,$domainInfo['domain_feedtype']);
//get custom menu as menu keyword to menu_pic_1_2_3
	
	$mResults = $Menu->get_domain_article_menus($domain_id);
	$customMenu = '';
	$v=0;
	foreach ($mResults as $mRow) 
	{
		if($mRow['menu_name'] != '' && $v != '0')
			$customMenu .= ',';
		$customMenu .= @trim($mRow['menu_name_display']);
		$v++;
	}
	if($customMenu != '')
		$domainInfo['page_menus'] = $customMenu.','.$relateString;
	else
		$domainInfo['page_menus'] = $relateString;

//multiple images
	$rKeyArray = explode(',', $relateString);
	$kws['page_pic_1'] = (isset($rKeyArray[0])&&$rKeyArray[0]!='') ? ($rKeyArray[0]) : $domainInfo['domain_keyword'];
	$kws['page_pic_2'] = (isset($rKeyArray[1])&&$rKeyArray[1]!='') ? ($rKeyArray[1]) : $domainInfo['domain_keyword'];
	$kws['page_pic_3'] = (isset($rKeyArray[2])&&$rKeyArray[2]!='') ? ($rKeyArray[2]) : $domainInfo['domain_keyword'];
	$kws['page_pic_4'] = (isset($rKeyArray[3])&&$rKeyArray[3]!='') ? ($rKeyArray[3]) : $domainInfo['domain_keyword'];
	$kws['page_pic_5'] = (isset($rKeyArray[4])&&$rKeyArray[4]!='') ? ($rKeyArray[4]) : $domainInfo['domain_keyword'];
	$kws['page_pic_6'] = (isset($rKeyArray[5])&&$rKeyArray[5]!='') ? ($rKeyArray[5]) : $domainInfo['domain_keyword'];
	$kws['page_pic_7'] = (isset($rKeyArray[6])&&$rKeyArray[6]!='') ? ($rKeyArray[6]) : $domainInfo['domain_keyword'];
	$kws['page_pic_8'] = (isset($rKeyArray[7])&&$rKeyArray[7]!='') ? ($rKeyArray[7]) : $domainInfo['domain_keyword'];
	$kws['page_pic_9'] = (isset($rKeyArray[8])&&$rKeyArray[8]!='') ? ($rKeyArray[8]) : $domainInfo['domain_keyword'];
	$kws['page_pic_10'] = (isset($rKeyArray[9])&&$rKeyArray[9]!='') ? ($rKeyArray[9]) : $domainInfo['domain_keyword'];
	$mKeyArray = explode(',', $domainInfo['page_menus']);
	$kws['menu_pic_1'] = (isset($mKeyArray[0])&&$mKeyArray[0]!='') ? ($mKeyArray[0]) : $domainInfo['domain_keyword'];
	$kws['menu_pic_2'] = (isset($mKeyArray[1])&&$mKeyArray[1]!='') ? ($mKeyArray[1]) : $domainInfo['domain_keyword'];
	$kws['menu_pic_3'] = (isset($mKeyArray[2])&&$mKeyArray[2]!='') ? ($mKeyArray[2]) : $domainInfo['domain_keyword'];
	$kws['menu_pic_4'] = (isset($mKeyArray[3])&&$mKeyArray[3]!='') ? ($mKeyArray[3]) : $domainInfo['domain_keyword'];
	$kws['menu_pic_5'] = (isset($mKeyArray[4])&&$mKeyArray[4]!='') ? ($mKeyArray[4]) : $domainInfo['domain_keyword'];
	$kws['menu_pic_6'] = (isset($mKeyArray[5])&&$mKeyArray[5]!='') ? ($mKeyArray[5]) : $domainInfo['domain_keyword'];
	$kws['menu_pic_7'] = (isset($mKeyArray[6])&&$mKeyArray[6]!='') ? ($mKeyArray[6]) : $domainInfo['domain_keyword'];
	$kws['menu_pic_8'] = (isset($mKeyArray[7])&&$mKeyArray[7]!='') ? ($mKeyArray[7]) : $domainInfo['domain_keyword'];
	$kws['menu_pic_9'] = (isset($mKeyArray[8])&&$mKeyArray[8]!='') ? ($mKeyArray[8]) : $domainInfo['domain_keyword'];
	$kws['menu_pic_10'] = (isset($mKeyArray[9])&&$mKeyArray[9]!='') ? ($mKeyArray[9]) : $domainInfo['domain_keyword'];
	
$kws['landing_pic'] = $kws['result_pic'] = $domainInfo['domain_keyword'];

//--------- inspect what images in layout, then generate a general layout for edit -----------------------
$layout_id = $domainInfo['domain_layout_id'];
$layout_landing= html_entity_decode($db->select_one("select layout_landing from layouts WHERE layout_id = '".$layout_id."'"));
$layout_result= html_entity_decode($db->select_one("select layout_result from layouts WHERE layout_id = '".$layout_id."'"));
$layout_sponsored= html_entity_decode($db->select_one("select layout_sponsored from layouts WHERE layout_id = '".$layout_id."'"));

$landing_img = '';
$results_img = '';
$pos = strrpos($layout_landing, "{LANDING_IMG}");
if ($pos !== false){$landing_img = '<div class="pic">landing_pic<br /><br />Keyword: '.$domainInfo['domain_keyword'].'<br /><img src="{LANDING_IMG}"  id="landing_pic" /></div>';}
$pos = strrpos($layout_landing, "{RESULTS_IMG}");
if ($pos !== false){$results_img = '<div class="pic">result_pic<br /><br />Keyword: '.$domainInfo['domain_keyword'].'<br /><img src="{RESULTS_IMG}"  id="result_pic" /></div>';}
for($i = 1; $i <= 10; $i++){
	$lm = 'lm'.$i;
	$$lm = '';
	$pos = strrpos($layout_landing, "{M_IMG_$i}");
	if ($pos !== false){$$lm = '<div class="pic">menu_pic_'.$i.'<br /><br />Keyword: '.$kws["menu_pic_$i"].'<br /><img src="{M_IMG_'.$i.'}"  id="menu_pic_'.$i.'" /></div>';}
	$lp = 'lp'.$i;
	$$lp = '';
	$pos = strrpos($layout_landing, "{IMG_$i}");
	if ($pos !== false){$$lp = '<div class="pic">page_pic_'.$i.'<br /><br />Keyword: '.$kws["page_pic_$i"].'<br /><img src="{IMG_'.$i.'}"  id="page_pic_'.$i.'" /></div>';}
	$rm = 'rm'.$i;
	$$rm = '';
	$pos = strrpos($layout_result, "{M_IMG_$i}");
	if ($pos !== false && empty($$lm)){$$rm = '<div class="pic">menu_pic_'.$i.'<br /><br />Keyword: '.$kws["menu_pic_$i"].'<br /><img src="{M_IMG_'.$i.'}"  id="menu_pic_'.$i.'" /></div>';}
	$rp = 'rp'.$i;
	$$rp = '';
	$pos = strrpos($layout_result, "{IMG_$i}");
	if ($pos !== false && empty($$lp)){$$rp = '<div class="pic">page_pic_'.$i.'<br /><br />Keyword: '.$kws["page_pic_$i"].'<br /><img src="{IMG_'.$i.'}"  id="page_pic_'.$i.'" /></div>';}
}

$pos = strrpos($layout_result, "{LANDING_IMG}");
if ($pos !== false && empty($landing_img)){$landing_img = '<div class="pic">landing_pic<br /><br />Keyword: '.$domainInfo['domain_keyword'].'<br /><img src="{LANDING_IMG}"  id="landing_pic" /></div>';}
$pos = strrpos($layout_result, "{RESULTS_IMG}");
if ($pos !== false && empty($results_img)){$results_img = '<div class="pic">result_pic<br /><br />Keyword: '.$domainInfo['domain_keyword'].'<br /><img src="{RESULTS_IMG}"  id="result_pic" /></div>';}

$pos = strrpos($layout_sponsored, "{LANDING_IMG}");
if ($pos !== false && empty($landing_img)){$landing_img = '<div class="pic">landing_pic<br /><br />Keyword: '.$domainInfo['domain_keyword'].'<br /><img src="{LANDING_IMG}"  id="landing_pic" /></div>';}
$pos = strrpos($layout_sponsored, "{RESULTS_IMG}");
if ($pos !== false && empty($results_img)){$results_img = '<div class="pic">result_pic<br /><br />Keyword: '.$domainInfo['domain_keyword'].'<br /><img src="{RESULTS_IMG}"  id="result_pic" /></div>';}

$layout_landing='<table width="700" border="1">
  <tr>
    <td colspan="5">
    <div style="float:right;"><a href="manage_images.php?search_type=domain&search='.$domainInfo['domain_url'].'" style="border: 0 none;" target="blank"><img src="images/editImage.jpg" title="Edit Images" style="width:120px; height:120px; margin-right:0px; padding:0px"  /></a></div>
    <br />
    <h2>'.$domainInfo['domain_url'].'</h2>
    <br />
    <h3>index page</h3>
    <br /><br /></td>
  </tr>
  <tr>
    <td width="20%">'.$landing_img.'</td>
    <td colspan="4">&nbsp;</td>
  </tr>
  <tr>
    <td>'.$lm1.'</td>
    <td>'.$lm2.'</td>
    <td>'.$lm3.'</td>
    <td>'.$lm4.'</td>
    <td>'.$lm5.'</td>
  </tr>
  <tr>
    <td>'.$lm6.'</td>
    <td>'.$lm7.'</td>
    <td>'.$lm8.'</td>
    <td>'.$lm9.'</td>
    <td>'.$lm10.'</td>
  </tr>
  <tr>
    <td>'.$lp1.'</td>
    <td>'.$lp2.'</td>
    <td>'.$lp3.'</td>
    <td>'.$lp4.'</td>
    <td>'.$lp5.'</td>
  </tr>
  <tr>
    <td>'.$lp6.'</td>
    <td>'.$lp7.'</td>
    <td>'.$lp8.'</td>
    <td>'.$lp9.'</td>
    <td>'.$lp10.'</td>
  </tr>
</table><br /><br /><br />';
$layout_result='<table width="700" border="1">
  <tr>
    <td colspan="5">
    <div style="float:right;"><a href="manage_images.php?search_type=domain&search='.$domainInfo['domain_url'].'" style="border: 0 none;" target="blank"><img src="images/editImage.jpg" title="Edit Images" style="width:120px; height:120px; margin-right:0px; padding:0px"  /></a></div>
    <br />
    <h2>'.$domainInfo['domain_url'].'</h2>
    <br />
    <h3>result page</h3>
    <br /><br />
    </td>
  </tr>
  <tr>
    <td width="20%">'.$results_img.'</td>
    <td colspan="4">&nbsp;</td>
  </tr>
  <tr>
    <td>'.$rm1.'</td>
    <td>'.$rm2.'</td>
    <td>'.$rm3.'</td>
    <td>'.$rm4.'</td>
    <td>'.$rm5.'</td>
  </tr>
  <tr>
    <td>'.$rm6.'</td>
    <td>'.$rm7.'</td>
    <td>'.$rm8.'</td>
    <td>'.$rm9.'</td>
    <td>'.$rm10.'</td>
  </tr>
  <tr>
    <td>'.$rp1.'</td>
    <td>'.$rp2.'</td>
    <td>'.$rp3.'</td>
    <td>'.$rp4.'</td>
    <td>'.$rp5.'</td>
  </tr>
  <tr>
    <td>'.$rp6.'</td>
    <td>'.$rp7.'</td>
    <td>'.$rp8.'</td>
    <td>'.$rp9.'</td>
    <td>'.$rp10.'</td>
  </tr>
</table><br /><br /><br />';


/*-------------- extract images ---------------------------
// firstly check images table to grab images, go to Bing if it is empty */
$imagePath = $config->imageLibrary;
$productQuerychk = "SELECT * FROM images WHERE image_domain_id='".$domain_id."'";

$pResultchk =  $db->select($productQuerychk);

//---------- insert images in pages ---------------
while($array=$db->get_row($pResultchk, 'MYSQL_ASSOC')){
	if($array['image_location']=='result_pic')  //|| $array['image_location']=='page_pic'
		$replaceArray['RESULTS_IMG'] = $imagePath.$array['image_name'];
	else if(trim($array['image_location'])=='landing_pic')  //|| $array['image_location']=='page_pic'
		$replaceArray['LANDING_IMG'] = $imagePath.$array['image_name'];
	else if($array['image_location']=='page_pic_1') 
		$replaceArray['IMG_1'] = $imagePath.$array['image_name'];
	else if($array['image_location']=='page_pic_2') 
		$replaceArray['IMG_2'] = $imagePath.$array['image_name'];
	else if($array['image_location']=='page_pic_3') 
		$replaceArray['IMG_3'] = $imagePath.$array['image_name'];
	else if($array['image_location']=='page_pic_4') 
		$replaceArray['IMG_4'] = $imagePath.$array['image_name'];
	else if($array['image_location']=='page_pic_5') 
		$replaceArray['IMG_5'] = $imagePath.$array['image_name'];
	else if($array['image_location']=='page_pic_6') 
		$replaceArray['IMG_6'] = $imagePath.$array['image_name'];
	else if($array['image_location']=='page_pic_7') 
		$replaceArray['IMG_7'] = $imagePath.$array['image_name'];
	else if($array['image_location']=='page_pic_8') 
		$replaceArray['IMG_8'] = $imagePath.$array['image_name'];
	else if($array['image_location']=='page_pic_9') 
		$replaceArray['IMG_9'] = $imagePath.$array['image_name'];
	else if($array['image_location']=='page_pic_10') 
		$replaceArray['IMG_10'] = $imagePath.$array['image_name'];
	else if(trim($array['image_location'])=='menu_pic_1') 
		$replaceArray['M_IMG_1'] = $imagePath.$array['image_name'];
	else if($array['image_location']=='menu_pic_2') 
		$replaceArray['M_IMG_2'] = $imagePath.$array['image_name'];
	else if($array['image_location']=='menu_pic_3') 
		$replaceArray['M_IMG_3'] = $imagePath.$array['image_name'];
	else if($array['image_location']=='menu_pic_4') 
		$replaceArray['M_IMG_4'] = $imagePath.$array['image_name'];
	else if($array['image_location']=='menu_pic_5') 
		$replaceArray['M_IMG_5'] = $imagePath.$array['image_name'];
	else if($array['image_location']=='menu_pic_6') 
		$replaceArray['M_IMG_6'] = $imagePath.$array['image_name'];
	else if($array['image_location']=='menu_pic_7') 
		$replaceArray['M_IMG_7'] = $imagePath.$array['image_name'];
	else if($array['image_location']=='menu_pic_8') 
		$replaceArray['M_IMG_8'] = $imagePath.$array['image_name'];
	else if($array['image_location']=='menu_pic_9') 
		$replaceArray['M_IMG_9'] = $imagePath.$array['image_name'];
	else if($array['image_location']=='menu_pic_10') 
		$replaceArray['M_IMG_10'] = $imagePath.$array['image_name'];
}

if(isset($replaceArray) && is_array($replaceArray)){
	foreach($replaceArray as $rkey => $rval)
	{
		$layout_landing = str_replace("{".$rkey."}",$rval,$layout_landing);
		$layout_result = str_replace("{".$rkey."}",$rval,$layout_result);
	}
}

//---- set a default photo for which hasn't been picked --------
$default_photo = "images/nophoto.jpg";
$layout_landing = str_replace("{LANDING_IMG}",$default_photo,$layout_landing);
$layout_landing = str_replace("{M_IMG_1}",$default_photo,$layout_landing);
$layout_landing = str_replace("{M_IMG_2}",$default_photo,$layout_landing);
$layout_landing = str_replace("{M_IMG_3}",$default_photo,$layout_landing);
$layout_landing = str_replace("{M_IMG_4}",$default_photo,$layout_landing);
$layout_landing = str_replace("{M_IMG_5}",$default_photo,$layout_landing);
$layout_landing = str_replace("{M_IMG_6}",$default_photo,$layout_landing);
$layout_landing = str_replace("{M_IMG_7}",$default_photo,$layout_landing);
$layout_landing = str_replace("{M_IMG_8}",$default_photo,$layout_landing);
$layout_landing = str_replace("{M_IMG_9}",$default_photo,$layout_landing);
$layout_landing = str_replace("{M_IMG_10}",$default_photo,$layout_landing);
$layout_landing = str_replace("{IMG_1}",$default_photo,$layout_landing);
$layout_landing = str_replace("{IMG_2}",$default_photo,$layout_landing);
$layout_landing = str_replace("{IMG_3}",$default_photo,$layout_landing);
$layout_landing = str_replace("{IMG_4}",$default_photo,$layout_landing);
$layout_landing = str_replace("{IMG_5}",$default_photo,$layout_landing);
$layout_landing = str_replace("{IMG_6}",$default_photo,$layout_landing);
$layout_landing = str_replace("{IMG_7}",$default_photo,$layout_landing);
$layout_landing = str_replace("{IMG_8}",$default_photo,$layout_landing);
$layout_landing = str_replace("{IMG_9}",$default_photo,$layout_landing);
$layout_landing = str_replace("{IMG_10}",$default_photo,$layout_landing);

$layout_result = str_replace("{RESULTS_IMG}",$default_photo,$layout_result);
$layout_result = str_replace("{M_IMG_1}",$default_photo,$layout_result);
$layout_result = str_replace("{M_IMG_2}",$default_photo,$layout_result);
$layout_result = str_replace("{M_IMG_3}",$default_photo,$layout_result);
$layout_result = str_replace("{M_IMG_4}",$default_photo,$layout_result);
$layout_result = str_replace("{M_IMG_5}",$default_photo,$layout_result);
$layout_result = str_replace("{M_IMG_6}",$default_photo,$layout_result);
$layout_result = str_replace("{M_IMG_7}",$default_photo,$layout_result);
$layout_result = str_replace("{M_IMG_8}",$default_photo,$layout_result);
$layout_result = str_replace("{M_IMG_9}",$default_photo,$layout_result);
$layout_result = str_replace("{M_IMG_10}",$default_photo,$layout_result);
$layout_result = str_replace("{IMG_1}",$default_photo,$layout_result);
$layout_result = str_replace("{IMG_2}",$default_photo,$layout_result);
$layout_result = str_replace("{IMG_3}",$default_photo,$layout_result);
$layout_result = str_replace("{IMG_4}",$default_photo,$layout_result);
$layout_result = str_replace("{IMG_5}",$default_photo,$layout_result);
$layout_result = str_replace("{IMG_6}",$default_photo,$layout_result);
$layout_result = str_replace("{IMG_7}",$default_photo,$layout_result);
$layout_result = str_replace("{IMG_8}",$default_photo,$layout_result);
$layout_result = str_replace("{IMG_9}",$default_photo,$layout_result);
$layout_result = str_replace("{IMG_10}",$default_photo,$layout_result);


/*$layout_landing = explode('</head>', $layout_landing);
$layout_result = explode('</head>', $layout_result);*/
//--------------------css for edit panel --------------------------------------------
$css = "<style>
.pic{
border:1px red dashed;
margin:20px 20px;
}


#EWconsole-blockoptionshold div {
cursor:pointer;
padding:4px;
clear:both;
width:100px;
height:45px;
}

.ui-corner-all {
-moz-border-radius-bottomleft:4px;
-moz-border-radius-bottomright:4px;
-moz-border-radius-topleft:4px;
-moz-border-radius-topright:4px;
cursor:pointer;
}

.ui-state-default, .ui-widget-content .ui-state-default {
-moz-background-clip:border;
-moz-background-inline-policy:continuous;
-moz-background-origin:padding;
background:#111111 url(images/ui-bg_glass_40_111111_1x400.png) repeat-x scroll 80% 80%;
border:1px solid #777777;
color:#E3E3E3;
font-weight:normal;
outline-color:-moz-use-text-color;
outline-style:none;
outline-width:medium;
cursor:pointer;
height:100px;
}

body{
background-color:#FFFFFF!important;
background-image:url()!important;	
}

#edit_result_page img, #edit_landing_page img{
width:150px; height:160px; cursor:pointer;background:#CBE3F5; margin-left:3px; padding:5px;
}

#edit_result_page ul li, #edit_landing_page ul li{
color:#033e78;
margin:5px;
}
</style>
";

?>

<!-- Page Edit -->

<div id="arrow_bar" style="margin:auto; text-align:center;  margin-top:10px">
	<button onclick="go_back();">Back to Main Panel </button>&nbsp;&nbsp;<button onclick="switch_edit();">Flip Template </button>
	<div >
		<table style="margin:auto; margin-top:12px">
			<tr>
				<td id="bwd" style="border-bottom:0px;border-left:0px;"></td>
				<td id="image_gallery" style="border-bottom:0px;border-left:0px;"></td>
				<td id="fwd" style="border-bottom:0px;border-left:0px;"></td>
			</tr>
		</table>
	</div>
</div>
<br />

<div id="edit_place" style="margin:auto; text-align:center;width:800px; height800px;">

	<div id="edit_result_page" style="width:800px; height800px;">
		<?php echo $css.$layout_result;//$layout_result[0].'</head>'.$css.$layout_result[1]; ?>
	</div>

	<div id="edit_landing_page"  style="width:800px; height800px;">
		<?php //echo $layout_landing[0].$css; ?>
		<?php echo $layout_landing;//'</head>'.$layout_landing[1]; ?>
		<link rel="stylesheet" href="thickbox/thickbox.css" type="text/css" media="screen" />
		<script language="javascript">
			var start = 1;
			var imageLibrary = '<?php echo $config->imageLibrary; ?>';
			var domain_id = '<?php echo $domain_id; ?>';
			var kws = {land:'<?php echo $domainInfo['domain_keyword']; ?>'
					,result:'<?php echo $domainInfo['domain_keyword']; ?>'
					,page1:'<?php echo $kws['page_pic_1']; ?>'
					,page2:'<?php echo $kws['page_pic_2']; ?>'
					,page3:'<?php echo $kws['page_pic_3']; ?>'
					,page4:'<?php echo $kws['page_pic_4']; ?>'
					,page5:'<?php echo $kws['page_pic_5']; ?>'
					,page6:'<?php echo $kws['page_pic_6']; ?>'
					,page7:'<?php echo $kws['page_pic_7']; ?>'
					,page8:'<?php echo $kws['page_pic_8']; ?>'
					,page9:'<?php echo $kws['page_pic_9']; ?>'
					,page10:'<?php echo $kws['page_pic_10']; ?>'
					,menu1:'<?php echo $kws['menu_pic_1']; ?>'
					,menu2:'<?php echo $kws['menu_pic_2']; ?>'
					,menu3:'<?php echo $kws['menu_pic_3']; ?>'
					,menu4:'<?php echo $kws['menu_pic_4']; ?>'
					,menu5:'<?php echo $kws['menu_pic_5']; ?>'
					,menu6:'<?php echo $kws['menu_pic_6']; ?>'
					,menu7:'<?php echo $kws['menu_pic_7']; ?>'
					,menu8:'<?php echo $kws['menu_pic_8']; ?>'
					,menu9:'<?php echo $kws['menu_pic_9']; ?>'
					,menu10:'<?php echo $kws['menu_pic_10']; ?>'
					};
			
			
			$(".pic").click(			  

				function(){
					var found = $(this).find("span:last");
					if (found.length == 0) {
						var loc = $(this).find('img').attr('id');   
						var name = $(this).find('img').attr('src');
						name = encodeURIComponent(name.substring(13));
						var kw = getKeyword(kws, loc);
						$(this).append('<span class="ui-state-default ui-corner-all" id="EWconsole-blockoptions" ><br /><ul><li style="color:#033e78;"><a class="thickbox" href="edit_image.php?keepThis=true&image_name='+name+'&TB_iframe=true&height=500&width=700" >Edit</a></li><li>Change Search Keyword<input type="text" id="searchKeyword'+loc+'" value="'+kw+'" /></li><li onclick="from_bing(\''+loc+'\',\'1\')" ><a href="#">More From Bing</a></li> <li onclick="from_google(\''+loc+'\',\'1\')" ><a href="#">More From Google</a></li> <li onclick="from_imagelibrary(\''+loc+'\',\'1\')" ><a href="#">More From ImageLibrary</a></li> <li onclick="upload(\''+loc+'\',\''+kw+'\'); return false;"><a href="#">Upload Image</a></li></ul><div id="'+loc+'_upload_area" style="color:red"></div></span>'); 
						tb_init('a.thickbox'); //it is in thickbox.js
					}
				  }
				);

			function from_bing(loc, s){
				if(s=='+')
					start+=10;
				else if(s=='-' && start>10){
					start-=10;
				}else if(s=='-' && start<10){
					$("#image_gallery").html('<span style="font-size:12px;">No more images. Please look forward.</span>');
					return false;
				}else{
					start=1;	
				}
				var kw = getKeyword2(loc);
				$("#image_gallery").html('<img src="images/loading.gif" style="border:0px;" />');
				
				$.getJSON("editImage.php", {action:'loadBingPics', keyword: kw, start:start, location:loc},function(data){
					  if(null==data || data==''){
							$("#image_gallery").html('<span style="font-size:12px;">No more images. Please look backward.</span>');
							return false;  
					  }
					  $("#image_gallery").html('');
					  $("#bwd").html('<img onclick="from_bing(\''+loc+'\',\'-\')" src="images/ar6.gif" style="cursor:pointer; border:0px; padding:6px 12px;" />');
					  $("#fwd").html('<img onclick="from_bing(\''+loc+'\',\'+\')" src="images/ar5.gif" style="cursor:pointer; border:0px; padding:6px 12px;" />');
					  $.each(data, function(key, value) { 					
			  				$("#image_gallery").append("<img src='"+value+"' style='width:120px; height:160px; cursor:pointer;background:#CBE3F5; margin-left:3px; padding:5px;' onclick='set_image(this.src, \""+loc+"\", \""+kw+"\")' />");
						});		 
			     });
			}


			function from_google(loc, s){
				if(s=='+')
					start+=10;
				else if(s=='-' && start>10){
					start-=10;
				}else if(s=='-' && start<10){
					$("#image_gallery").html('<span style="font-size:12px;">No more images. Please look forward.</span>');
					return false;
				}else{
					start=1;	
				}
				var kw = getKeyword2(loc);
				$("#image_gallery").html('<img src="images/loading.gif" style="border:0px;" />');
			
			
				$.getJSON("editImage.php", {action:'loadGooglePics', keyword: kw, start:start, location:loc},function(data){
					  if(null==data || data==''){
							$("#image_gallery").html('<span style="font-size:12px;">No more images. Please look backward.</span>');
							return false;  
					  }
					  $("#image_gallery").html('');
					  $("#bwd").html('<img onclick="from_google(\''+loc+'\',\'-\')" src="images/ar6.gif" style="cursor:pointer; border:0px; padding:6px 12px;" />');
					  $("#fwd").html('<img onclick="from_google(\''+loc+'\',\'+\')" src="images/ar5.gif" style="cursor:pointer; border:0px; padding:6px 12px;" />');
					  $.each(data, function(key, value) { 					
			  				$("#image_gallery").append("<img src='"+value+"' style='width:120px; height:160px; cursor:pointer;background:#CBE3F5; margin-left:3px; padding:5px;' onclick='set_image(this.src, \""+loc+"\", \""+kw+"\")' />");
						});		 
			     });
			}
			
			
						
			function from_imagelibrary(loc, s){
				if(s=='+')
					start+=10;
				else if(s=='-' && start>10){
					start-=10;
				}else if(s=='-' && start<10){
					$("#image_gallery").html('<span style="font-size:12px;">No more images. Please look forward.</span>');
					return false;
				}else{
					start=1;	
				}
				var kw = getKeyword2(loc);
				$("#image_gallery").html('<img src="images/loading.gif" style="border:0px;" />');
			
			
				$.getJSON("editImage.php", {action:'getImageFromLibrary', keyword: kw, start:start, location:loc,  limit:10},function(data){
					  if(null==data || data==''){
							$("#image_gallery").html('<span style="font-size:12px;">No more images. Please look backward.</span>');
							return false;  
					  }
					  $("#image_gallery").html('');
					  $("#bwd").html('<img onclick="from_imagelibrary(\''+loc+'\',\'-\')" src="images/ar6.gif" style="cursor:pointer; border:0px; padding:6px 12px;" />');
					  $("#fwd").html('<img onclick="from_imagelibrary(\''+loc+'\',\'+\')" src="images/ar5.gif" style="cursor:pointer; border:0px; padding:6px 12px;" />');
					  $.each(data, function(key, value) { 					
			  				$("#image_gallery").append("<img src='"+imageLibrary+value['image_library_name']+"' style='width:120px; height:160px; cursor:pointer;background:#CBE3F5; margin-left:3px; padding:5px;' onclick='set_image_library(this.src, \""+value['image_library_id']+"\", \""+loc+"\", \""+kw+"\")' />");
						});		 
			     });
			}
			
			
			function set_image_library(path, image_library_id, loc, kw){
				var si = confirm('Do you want to set this image as '+loc+'?');
				if(si){
					$.get("editImage.php", {action:'setImageFromLibrary', image_library_id:image_library_id, domain_id:domain_id, keyword: kw, location:loc},function(data){
			          		$("#"+loc).attr('src',path);
							$("#image_gallery").html('');		  		 
			     		});
				}
				return false;
			}
			
			
			function set_image(path,loc,kw){
				var si = confirm('Do you want to set this image as '+loc+'?');
				if(si){
					$.get("editImage.php", {action:'setImage', path:path, domain_id:domain_id, keyword: kw, location:loc},function(data){
			          		$("#"+loc).attr('src',path);
							$("#image_gallery").html('');		  		 
			     		});
				}
				return false;
			}
			
			function upload(loc,kw){
				var addStr = '<form id="upload_image" name="upload_image" action="editImage.php" enctype="multipart/form-data" method="POST" style="color:red;"><input type="file" size="1" name="upimage" /><input type="hidden" name="image_location" value="'+loc+'" /><input type="hidden" name="kw" value="'+kw+'" ><input type="hidden" name="domain_id" value="'+domain_id+'" ><input type="hidden" name="action" value="upload_image" ><button >Go</button></form><br />* Only accept jpg/png/gif.<br /><br />';
				var addStr2 = "<SCRIPT>$(function(){ var options = {target:'#"+loc+"_upload_area', success:showResponse};  $('#upload_image').submit(function(){$(this).ajaxSubmit(options);return false; }); }); <"+"/"+"SCRIPT>";
				//beforeSubmit:showRequest,
				$('#'+loc+'_upload_area').append(addStr+addStr2);
				return false;
			}
			
			function after_upload(data){
				alert(data);	
			}
			
			function getKeyword(kws, loc){
				if(loc=='landing_pic'){
					return kws.land;	
				}else if(loc=='result_pic'){
					return kws.result;	
				}else if(loc=='page_pic_1'){ return kws.page1;	
				}else if(loc=='page_pic_2'){ return kws.page2;	
				}else if(loc=='page_pic_3'){ return kws.page3;	
				}else if(loc=='page_pic_4'){ return kws.page4;	
				}else if(loc=='page_pic_5'){ return kws.page5;	
				}else if(loc=='page_pic_6'){ return kws.page6;	
				}else if(loc=='page_pic_7'){ return kws.page7;	
				}else if(loc=='page_pic_8'){ return kws.page8;	
				}else if(loc=='page_pic_9'){ return kws.page9;	
				}else if(loc=='page_pic_10'){ return kws.page10;	
				}else if(loc=='menu_pic_1'){ return kws.menu1;	
				}else if(loc=='menu_pic_2'){ return kws.menu2;	
				}else if(loc=='menu_pic_3'){ return kws.menu3;	
				}else if(loc=='menu_pic_4'){ return kws.menu4;	
				}else if(loc=='menu_pic_5'){ return kws.menu5;	
				}else if(loc=='menu_pic_6'){ return kws.menu6;	
				}else if(loc=='menu_pic_7'){ return kws.menu7;	
				}else if(loc=='menu_pic_8'){ return kws.menu8;	
				}else if(loc=='menu_pic_9'){ return kws.menu9;	
				}else if(loc=='menu_pic_10'){ return kws.menu10;	
				}
			}
			
		function getKeyword2(loc){
			var kw = $('#searchKeyword'+loc).val();
			return kw;
		}
		</script>
		<script type="text/javascript" src="thickbox/thickbox.js"></script>
	</div>
</div> <!-- end edit_place -->

<?php
require_once('footer.php');
?>
