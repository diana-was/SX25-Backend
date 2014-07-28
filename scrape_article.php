<?php
/**
 * Domains
 * Author: Archie Huang on 07/05/2009
**/
require_once('config.php');
require_once('header.php');

$Article = Article::getInstance($db);
$Profile = new Profile();
$Site = Site::getInstance($db); 
$Parked = ParkedDomain::getInstance($db); 
$artExist = 0;
$artNoKey = 0;
$domainExist = 0;
$succ = 0;
$fail = 0;
$sucMsg = '';
$failMsg = '';
// menues
$menExist = 0;
$menNoKey = 0;
$succMenu = 0;
$failMenu = 0;

if(isset($_REQUEST['action']) && $_REQUEST['action'] == 'default_article')
{
	echo '<div id="log"><br><input type="button" value="Hide" onclick="$(\'#log\').hide();"><br><br>';
	$Menu = Menu::getInstance($db); 
	
	$account_id = isset($_REQUEST['account_id'])?$_REQUEST['account_id']:'';
	if($account_id != '')
	{
		$pResults = $Site->get_domain_data_list('account',$account_id);
		$article_source = isset($_REQUEST['article_source'])?$_REQUEST['article_source']:'ehow';
		foreach($pResults as $pRow) 
		{
			$keyword = '';
			$domain_id = '';
			$keyword = $pRow['domain_keyword'];
			$domain_id = $pRow['domain_id'];
			$domain_url =  strtolower(trim($pRow['domain_url']));
			
			if($keyword != '' && $domain_id != '')
			{
				echo "Article -> Domain: $domain_url, keyword: $keyword<br>";
				if(!($article_exist = $Article->check_article_set($keyword, $domain_id)))
				{
						$article_id = $Article->scrape_article($keyword, $domain_id, $domain_url, 1, 1, 0, 0, $article_source);
						if(!empty($article_id))
							$succ++;
						else
							$fail++;
				}
				else
				{
					$artExist++;
				}

				//----------- set default images ---------
				echo "Image -> Domain: $domain_url, keyword: $keyword<br>";
				$landingImg = $domain_id.'_landing_pic';
				$img = image::getInstance($db);
				$lib = ImageLibrary::getInstance($db);
				
				$checkLan = $img->checkImage($domain_id, 'landing_pic');
				$checkRes = $img->checkImage($domain_id, 'result_pic');
				if(!$checkLan || !$checkRes)
				{
					$pic  = !$checkLan?1:0;
					$pic += !$checkRes?1:0;
					$images	= $lib->getKeywordImages($keyword, $pic, true, $domain_id);
					$exist 	= count($images);
					if (!is_array($images) ||  $exist < $pic)
					{
						$images = $lib->getGoogleImageSearch($keyword, ($pic - $exist), $exist);
						if (!empty($images))
						{
							foreach($images as $k=>$v){
								if (!empty($v['content_photo_src']))
								{
									$id = $lib->saveImage($v['content_photo_src'], $keyword);
									if ($id) {
										$images[$exist]['image_library_id'] = $id;
										$exist++;
									}
								}
							}
						}
					}

					if(!$checkLan)
					{
						$image = array_pop($images);
						$location = 'landing_pic';
						if (!empty($image))
						{
							$url = $img->setImageLibrary($image['image_library_id'], $domain_id, $location);	
							echo '<p style="margin-left:30px;">'.$pRow['domain_url'].'<br />Landing page image, Keyword : '.$keyword.' <br /><img src="'.$url.'" width="60px" /><a href="edit_domain.php?domain_id='.$domain_id.'" target="_blank" >Change</a></p>';
						}
					}
					
					if(!$checkRes)
					{
						$image = array_pop($images);
						$location = 'result_pic';
						if (!empty($image))
						{
							$url = $img->setImageLibrary($image['image_library_id'], $domain_id, $location);
							echo '<p style="margin-left:30px;">'.$pRow['domain_url'].'<br />Result page image, Keyword : '.$keyword.' <br /><img src="'.$url.'" width="60px" /><a href="edit_domain.php?domain_id='.$domain_id.'" target="_blank" >Change</a></p>';
						}
					}
				}
				//---------- finish adding default image -----------------------------
			}
			else
			{
				$artNoKey++;
			}
			
			echo "Menu -> Domain: $domain_url<br>";
			$menuList = $Menu->get_domain_article_menus($domain_id);
			foreach($menuList as $menuRow) 
			{
				$keyword = $menuRow['menu_name_display'];
				if($keyword != '' && $domain_id != '')
				{
					if(!($article_exist = $Article->check_article_set($keyword, $domain_id)))
					{
							$article_id = $Article->scrape_article($keyword, $domain_id, $domain_url, 0, 1, 0, 0, $article_source);
							if($article_id != '')
								$succMenu++;
							else
								$failMenu++;
					}
					else
					{
						$menExist++;
					}
				}
				else
				{
					$menNoKey++;
				}
			}
			echo "End -> Domain: $domain_url<br>";
		}
	}	
	echo '<br><input type="button" value="Hide" onclick="$(\'#log\').hide();"><br><br></div>';
}
else if(isset($_REQUEST['action']) && $_REQUEST['action'] == 'extra_article')
{
	echo '<div id="log"><br><input type="button" value="Hide" onclick="$(\'#log\').hide();"><br><br>';
	$uploaddir = $config->uploadFolder;
	$uploadfile = $uploaddir . basename($_FILES['csvfile']['name']);

	if (move_uploaded_file($_FILES['csvfile']['tmp_name'], $uploadfile)) 
	{
		$article_source = isset($_REQUEST['article_source'])?$_REQUEST['article_source']:'ehow';
		$row = 1;
		$handle = fopen($uploadfile, "r");
		while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) 
		{
			if($row == 1 || empty($data) || empty($data[0]))
			{
				$row++;
				continue; //skip first line and empty lines
			}
			else
			{
				$domain_url = strtolower(trim($data[0]));
				$keyword = isset($data[1])?trim($data[1]):'';
				$numArticles = (isset($data[2]) && is_numeric($data[2]))?$data[2]:1;
				
				if($domain_id = $Site->check_domain_id($domain_url))
				{
					if($keyword != '' && $domain_id != '')
					{
						if(!($article_exist = $Article->check_article_set($keyword, $domain_id)))
						{
								$article_id = $Article->scrape_article($keyword, $domain_id, $domain_url, 0, 1, 0, 0, $article_source);
								if($article_id != '')
									$succ++;
								else
									$fail++;
						}
						else
						{
							$artExist++;
						}
					}
					else
					{
						$artNoKey++;
					}
				}
				else // It is a parked domain
				{
					$domainExist++;
					if($keyword != '')
					{
						$domainInfo = $Parked->get_domain_info_name($domain_url);
						if (!$domainInfo || empty($domainInfo['domain_keyword']))
						{
							if ($numArticles > 1)
								if ($domainInfo['domain_layout_id'] == 55) // if the domain has amz_default_related template get domain and keyword
								{
									$Parked->setDomainRelatedKeywords($domain_url,$keyword);
								}
							else
							{
								$domain_id = $domainInfo['domain_id'];
								if ($domain_id)
									$Parked->save_domain(array('domain_keyword' => $keyword),$domain_id);
								// else 
								//	$Parked->save_domain(array('domain_url' => $domain_url,'domain_keyword' => $keyword));
							}
						}
						
						if(!($article_exist = $Article->check_article_set2($keyword, $domain_url)))
						{
								$article_id = $Article->scrape_article($keyword, NULL, $domain_url, 1, 1, 0, 0, $article_source);
								
								if($article_id != '')
									$succ++;
								else
									$fail++;
						}
						else
						{
							$artExist++;
						}
						// Get extra articles
						if ($numArticles > 1)
						{
							$related = $Parked->getDomainRelatedKeywords($domain_url,false);
							$max =  (count($related) > ($numArticles - 1))?$numArticles - 1:count($related);

							for ($i=0;$i<$max;$i++)
							{
								if(!($article_exist = $Article->check_article_set2($related[$i], $domain_url)))
								{
										$article_id = $Article->scrape_article($related[$i], NULL, $domain_url, 0, 1, 0, 0, $article_source);
								        if($article_id != '')
											$succ++;
										else
											$fail++;
								}
								else
								{
									$artExist++;
								}
							}
						}
					}
					else
					{
						$artNoKey++;
					}
				}
			}
			$row++;
		}
	}
		echo '<br><input type="button" value="Hide" onclick="$(\'#log\').hide();"><br><br></div>';
}
if($succ > 0)
	$sucMsg = $succ." articles populated successfully";
if($fail > 0)
	$failMsg = $fail." not found articles ";
if($artExist > 0)
	$failMsg .= "<br>$artExist articles already exist with that domain and keyword";
if($artNoKey > 0)
	$failMsg .= "<br>$artNoKey domains don't have keyword set yet";
if($domainExist > 0)
	$failMsg .= "<br>$domainExist domains from parked";
// Menues
if($succMenu > 0)
	$sucMsg .= '<br>'.$succMenu." menues populated successfully ";
if($failMenu > 0)
	$failMsg .= '<br>'.$failMenu." Menues not found articles ";
if($menExist > 0)
	$failMsg .= "<br>".$menExist." Menues already have articles";
if($menNoKey > 0)
	$failMsg .= "<br>".$menNoKey." Menues don't have keyword set yet";

?>
<div id="content">
	<table width="100%" border="0" cellspacing="0" cellpadding="0">
		<tr>
			<td class="brdrL">&nbsp;</td>

			<td valign="top" id="main">
			
			<?php if($sucMsg != '' || $failMsg != '') : ?>
				<table border="0" cellpadding="0" cellspacing="0" width="100%" id="boxGray">
					<tr>
						<td valign="top"><div class="blueHdr">System Message</div>
						<div class="content" align="center">
				        <font color="Green"><?php echo $sucMsg;?></font><br />
						<font color="Red"><?php echo $failMsg;?></font>
				
						</div>
						</td>
					</tr>
				</table>
				<br />
			<?php endif; ?>
			<!-- *** START MAIN CONTENTS  *** -->
			
			<span class="txtHdr">Populate Articles &amp; Default Images </span>
			<table width="100%" cellspacing="0" cellpadding="0" border="0" align="center" class="tablePop">
			<tbody>
			<tr>
				<td valign="top" colspan="2">
					
					<div id="tableData">
					<table width="100%" cellspacing="1" cellpadding="0" border="0" class="data_table" id="dt1">
						<tbody><tr>
						<td align="left" class="cellHdr"><strong>Populate Default Articles</strong></td>
                        <td align="left" class="cellHdr"><strong>Populate Extra Articles</strong></td>
                        </tr>

						<tr class="alter1">
							<td valign="top" align="center" width="50%">	 
			                    <form action="/scrape_article.php" method="POST" name="export">
								<input type="hidden" value="default_article" name="action"/>
								<table border="0" cellpadding="0" cellspacing="0" width="100%">
									<tr>
										<td align="left" colspan="3">Select the Account:</td>
									</tr>
									<tr>
										<td width="33%">&nbsp;</td>
										<td align="left" valign="top" width="200px"><br>
											<?php $Profiles = $Profile->getProfileList(); ?>
											<select name="profile_id" id="profile_id" onChange="initCs();">
                                            <option value="">Please Select</option>
											<?php foreach ($Profiles as $row) : ?>
												<option value="<?php echo ($row['profile_id']); ?>"><?php echo $row['profile_name']; ?></option>
											<?php endforeach; ?>
											</select>
											&nbsp;&nbsp;<select name="account_id" id="account_id"></select>
										</td>
										<td width="33%">&nbsp;</td>
									</tr>
									<tr>
										<td width="33%">&nbsp;</td>
										<td align="left" valign="top" width="200px"><br>
                                            From <select name="article_source" id="article_source">
                                            	 <option value="ehow" selected="selected">ehow</option>
                                                 <option value="articleBase">articleBase</option>
                                                 <option value="EzineArticles">EzineArticles</option>
                                                 <option value="hubpages">hubpages</option>
                                            </select>
											<br><br />
											<font size="-2">Make sure the default keywords are set before populating</font>
										</td>
										<td width="33%">&nbsp;</td>
									</tr>
                                    <tr>
										<td align="center" colspan="3"> <input type="submit" value="Populate Default" name="submit"></td>
									</tr>
                                   
								</table>
		                       </form>
							</td>
                            
							<td valign="top" align="left">
		                       	<form enctype="multipart/form-data" action="/scrape_article.php" method="POST" id="data_table" name="import">
		                       	<input type="hidden" value="extra_article" name="action"/>
								<table border="0" cellpadding="0" cellspacing="0" width="100%">

									<tr>
										<td align="left">Select CSV file to upload:</td>
									</tr>
									<tr>
										<td width="33%">&nbsp;</td>
										<td align="left" valign="top" width="300px"><br>
											<input name="csvfile" type="file" />
											<br>
											<font size="-2">header must contain column names: domain,keyword,num_articles</font>
										</td>
										<td width="33%">&nbsp;</td>
									</tr>
									<tr>
										<td width="33%">&nbsp;</td>
										<td align="left" valign="top" width="200px"><br>
                                            From <select name="article_source" id="article_source">
                                            	 <option value="ehow" selected="selected">ehow</option>
                                                 <option value="articleBase">articleBase</option>
                                                 <option value="EzineArticles">EzineArticles</option>
                                                 <option value="hubpages">hubpages</option>
                                            </select><br>
										</td>
										<td width="33%">&nbsp;</td>
									</tr>
                                    <tr>
										<td align="center" colspan="3"> <input type="submit" value="Populate Extra" name="submit"></td>
									</tr>
								</table>
	                            </form>
							</td>
						</tr>
						</tbody></table>
					</div>
					</td>
				</tr>
			</tbody>
			</table>		
			
			<!-- *** END MAIN CONTENTS  *** -->
			
					
			</td>
			<td rowspan="5" class="brdrR">&nbsp;</td>
		</tr>
	</table>
</div>
<?php
require_once('footer.php');
?>