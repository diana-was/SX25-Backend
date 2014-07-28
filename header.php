<?php
require_once('check_user.php');
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
<head>
<title><?php echo $systemName;?></title>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
<link href="css/interior.css" rel="stylesheet" type="text/css">
<link href="css/parked.css" rel="stylesheet" type="text/css">
<link href="css/drag_drop.css" rel="stylesheet" type="text/css">
<link href="http://ajax.googleapis.com/ajax/libs/jqueryui/1.8/themes/base/jquery-ui.css" rel="stylesheet" type="text/css"/>
<link href="http://static.jquery.com/ui/css/demo-docs-theme/ui.theme.css" rel="stylesheet"  type="text/css" media="all" />
<link href="js/jMenu_v1.8/jMenu.jquery.css" rel="stylesheet" type="text/css"/>

<script src="/js/tool_tip.js" language="JavaScript" type="text/JavaScript"></script>
<script src="/js/mulselect.js" language="JavaScript" type="text/JavaScript"></script>
<script src="/js/jquery.min.js" language="JavaScript"  type="text/javascript"></script>
<script src='http://jquery-multifile-plugin.googlecode.com/svn/trunk/jquery.MetaData.js' type="text/javascript" language="javascript"></script>
<script src='http://jquery-multifile-plugin.googlecode.com/svn/trunk/jquery.MultiFile.js' type="text/javascript" language="javascript"></script>
<script src="http://ajax.googleapis.com/ajax/libs/jqueryui/1.8/jquery-ui.min.js"></script> 
<script src="/js/jMenu_v1.8/jMenu.jquery.js" language="JavaScript"  type="text/javascript"></script>
<script src="/js/drag_drop.js" language="JavaScript" type="text/JavaScript"></script>
<script src="http://jquery-ui.googlecode.com/svn/tags/latest/external/jquery.bgiframe-2.1.2.js" type="text/javascript"></script>
<script type="text/javascript" src="js/blockUI.js"></script>
<script type="text/javascript" src="js/jquery.form.js"></script> 

<script language="JavaScript" type="text/JavaScript">
	$(document).ready(function(){ 
		    var w = Math.floor($("#navTr").width()/7);
		    $("#jMenu li a").css('width',w-20);
			// more complex jMenu plugin called 
			$("#jMenu").jMenu({ 
					ulWidth : w,
					effects : { effectSpeedOpen : 100, 
								effectTypeClose : 'slide',
								effectSpeedClose: 400
							   }, 
					animatedText : false, 
					effectOpen : 'Slide', 
					effectClose : 'Puff',
					TimeBeforeOpening: 100,
					TimeBeforeClosing: 300
			}); 
	});	
</script>
</head>
<body>
<div id="hdr">
	<table width="100%" border="0" cellspacing="0" cellpadding="0">
		<tr>
			<td rowspan="6" class="brdrL">&nbsp;</td>
			<td colspan="2"><img src="/images/s.gif" width="700" height="4" alt=""></td>
			<td rowspan="6" class="brdrR">&nbsp;</td>
		</tr>
		<tr>
			<td width="400" id="logoRow"><a href="/"><img src="/images/logo.jpg" width="221" height="60" alt="SX2" border="0" style="float:left"></a><div style="margin-top:26px"><?php echo APPLICATION_ENVIRONMENT; ?></div></td>
			<td id="logoRow"><div id="summary">Account: <?php echo $user->userData['user_name'];?> | <a href="/logout.php">Logout</a></div></td>
		</tr>
		<tr>
			<td colspan="2"><img src="/images/s.gif" width="700" height="2" alt="" style="clear:both"></td>
		</tr>

		<tr>
			<td colspan="2" id="navTr" align="center">
			<ul id="jMenu">
			<li><a href="/index.php" >Home</a></li>
			<?php switch ($user->userLevel) :
			case 0 : ?>
			<li><a class="fNiv">Domains</a>
			<ul>
				<li><a href="domains_manager.php" >SX25 Domains</a>
					<ul>
						<li><a href="domains_manager.php">Domains Manager<div class="explain">Edit, delete and search domains in SX25</div></a></li>
						<li><a href="add_custom_menus.php">Add Custom Menus<div class="explain">Import fix menu for domains</div></a></li>
						<li><a href="add_mapping_keywords.php">Add Mapping Keywords<div class="explain">Import mapping keywords for domains</div></a></li>
						<li><a href="sx25_genz.php">Regenerate GenZ css<div class="explain">Generate a new base template for sx25 domains</div></a></li>
						<li><a href="set_bulk_content.php">Set Up Domain Content<div class="explain">Set Up Domain Content for sx25 domains</div></a></li>
						<li><a href="tool_generate_sitemap.php">Generate Domain Sitemap<div class="explain">Read csv and generate the sitemaps for those domains</div></a></li>
						<li><a href="keyword_tracking.php">Keyword Tracking<div class="explain">Upload a csv that contains Domain, Keyword, Tracking, and save this info into the domain</div></a></li>
					</ul> 
				</li>
				<li><a href="parked_domains_manager.php" >Parked Domains</a>
	            	<ul>
	            		<li><a href="parked_domains_manager.php">Parked Domains Manager<div class="explain">Create, delete and search domains in Parked</div></a></li>
	            		<li><a href="edit_parked_template.php">Edit Parked Template<div class="explain">Edit and change base templates</div></a></li>
	            		<li><a href="parked_genz.php">Generate Parked Templates<div class="explain">Generate a new base template for listing domains</div></a></li>
	            	</ul> 
				</li>
			</ul>
			</li>
			<li><a class="fNiv">Libraries</a>
			<ul>
	            <li><a href="scrape_article.php" >Articles</a> 
	            	<ul>
	            		<li><a href="scrape_article.php">Scrape articles<div class="explain">Scrape articles from the web</div></a></li>
	            		<li><a href="manage_articles.php?server=sx25">Manage SX25 Articles<div class="explain">Create, edit and delete SX25 domains&rsquo; articles</div></a></li>
	            		<li><a href="manage_articles.php?server=parked">Manage Parked Articles<div class="explain">Create, edit and delete Parked domains&rsquo; articles</div></a></li>
	            		<li><a href="manage_articles.php?server=library">Manage Articles Library<div class="explain">Create, edit, delete and attach articles</div></a></li>
	            	</ul> 
	            </li>
	            <li><a href="scrape_directory.php" >Directory</a> 
	            	<ul>
	            		<li><a href="scrape_directory.php">Scrape Directories<div class="explain">Scrape listings from the web</div></a></li>
	            		<li><a href="add_directories.php">Upload Directories<div class="explain">Import listings from file</div></a></li>
	            		<li><a href="manage_directories.php">Manage Directories<div class="explain">Create, edit and delete listings in the directory library</div></a></li>
	            	</ul> 
	            </li>
	            <li><a href="scrape_question_answer.php" >Question&amp;Answer</a> 
	            	<ul>
	            		<li><a href="scrape_question_answer.php">Scrape Questiones &amp; Answers<div class="explain">Scrap Q&amp;A from the web</div></a></li>
	            		<li><a href="manage_question_answer.php">Manage Questiones &amp; Answers<div class="explain">Create, edit and delete Q&amp;A in the library</div></a></li>
	            		<li><a href="add_related_keywords.php">Upload Related Keywords<div class="explain">Upload related keywords to display Q&amp;A in domains</div></a></li>
	            	</ul> 
	            </li>
	            <li><a href="scrape_event.php" >Events</a>
            	<ul>
            		<li><a href="scrape_event.php">Scrape Events<div class="explain">Scrap Events from the web</div></a></li>
            		<li><a href="manage_event.php">Manage Event<div class="explain">Create, edit and delete Events in the library</div></a></li>
            	</ul> 
	            <li><a href="manage_goal.php" >Goals</a></li>
	            <li><a href="manage_images.php" >Images</a></li>
	        </ul>
	        </li>
			<li><a class="fNiv">Community</a>
				<ul>
		            <li><a href="approve_comments.php" >Comments<div class="explain">Websites&rsquo; comments from community</div></a>
		            	<ul>
		            		<li><a href="approve_comments.php">Unapproved Comments<div class="explain">Delete, store or approve unapproved comments</div></a></li>
		            		<li><a href="view_comments.php">Approved Comments<div class="explain">Edit or delete approved comments</div></a></li>
		            		<li><a href="stored_comments.php">Stored Comments<div class="explain">Edit, Approve or delete stored comments</div></a></li>
		            	</ul> 
		            </li>
		            <li><a href="contactus_message.php?from=sx2">Messages<div class="explain">Websites&rsquo; emails from community</div></a>
		            	<ul>
		            		<li><a href="contactus_message.php?from=sx2">SX2 websites&rsquo; messages<div class="explain">Respond or delete emails from sx25 sites</div></a></li>
		            		<li><a href="contactus_message.php?from=parked">Parked websites&rsquo; messages<div class="explain">Respond or delete emails from Parked sites</div></a></li>
		            	</ul> 
		            </li>
	            </ul>
            </li>
            <li><a href="reports.php" class="fNiv">Reports</a> </li>
			</li>
            
			<?php break;
			case 1 : ?>
			<li><a class="fNiv">Domains</a>
			<ul>
				<li><a href="domains_manager.php" >SX25 Domains</a>
					<ul>
						<li><a href="domains_manager.php">Domains Manager<div class="explain">Edit, delete and search domains in SX25</div></a></li>
					</ul> 
				</li>
			</ul>
			</li>
			<li><a class="fNiv">Libraries</a>
			<ul>
	            <li><a href="manage_images.php" >Images</a></li>
	        </ul>
	        </li>
			<?php break;
			case 2 :	case 3 :	?>
			<li><a class="fNiv">Domains</a>
			<ul>
				<li><a href="domains_manager.php" >SX25 Domains</a>
					<ul>
						<li><a href="domains_manager.php">Domains Manager<div class="explain">Edit, delete and search domains in SX25</div></a></li>
						<li><a href="add_domains.php">Add Domains<div class="explain">Create new domains in SX25 Amazon servers</div></a></li>
						<li><a href="mass_modify.php">Mass Modify<div class="explain">Import and export domains settings</div></a></li>
						<li><a href="add_custom_menus.php">Add Custom Menus<div class="explain">Import fix menu for domains</div></a></li>
						<li><a href="add_mapping_keywords.php">Add Mapping Keywords<div class="explain">Import mapping keywords for domains</div></a></li>
						<li><a href="sx25_genz.php">Regenerate GenZ css<div class="explain">Generate a new base template for sx25 domains</div></a></li>
						<li><a href="set_bulk_content.php">Set Up Domain Content<div class="explain">Set Up Domain Content for sx25 domains</div></a></li>
						<li><a href="tool_generate_sitemap.php">Generate Domain Sitemap<div class="explain">Read csv and generate the sitemaps for those domains</div></a></li>
						<li><a href="keyword_tracking.php">Keyword Tracking<div class="explain">Upload a csv that contains Domain, Keyword, Tracking, and save this info into the domain</div></a></li>
					</ul> 
				</li>
				<li><a href="parked_domains_manager.php" >Parked Domains</a>
	            	<ul>
	            		<li><a href="parked_domains_manager.php">Parked Domains Manager<div class="explain">Create, delete and search domains in Parked</div></a></li>
	            		<li><a href="add_parked_domains.php">Add Parked Domains<div class="explain">Add list of domains in Parked with its keywords</div></a></li>
	            		<li><a href="edit_parked_template.php">Edit Parked Template<div class="explain">Edit and change base templates</div></a></li>
	            		<li><a href="parked_genz.php">Generate Parked Templates<div class="explain">Generate a new base template for listing domains</div></a></li>
	            	</ul> 
				</li>
			</ul>
			</li>
			<li><a class="fNiv">Designs</a>
			<ul>
	 			<li><a href="page_content.php">Pages<div class="explain">Edit fix pages to display in layouts</div></a></li>
	            <li><a href="module_layout.php">Modules<div class="explain">Edit layouts for modules</div></a></li>
				<li><a href="custom_layout.php">SX25 Layouts<div class="explain">Edit websites layouts to use in SX25</div></a></li>
				<li><a href="parked_layout.php">Parked Layouts<div class="explain">Edit websites layouts to use in Parked</div></a></li>
	 		</ul>
	 		</li>
			<li><a class="fNiv">Libraries</a>
			<ul>
	            <li><a href="scrape_article.php" >Articles</a> 
	            	<ul>
	            		<li><a href="scrape_article.php">Scrape articles<div class="explain">Scrape articles from the web</div></a></li>
	            		<li><a href="manage_articles.php?server=sx25">Manage SX25 Articles<div class="explain">Create, edit and delete SX25 domains&rsquo; articles</div></a></li>
	            		<li><a href="manage_articles.php?server=parked">Manage Parked Articles<div class="explain">Create, edit and delete Parked domains&rsquo; articles</div></a></li>
	            		<li><a href="manage_articles.php?server=library">Manage Articles Library<div class="explain">Create, edit, delete and attach articles</div></a></li>
	            	</ul> 
	            </li>
	            <li><a href="scrape_directory.php" >Directory</a> 
	            	<ul>
	            		<li><a href="scrape_directory.php">Scrape Directories<div class="explain">Scrape listings from the web</div></a></li>
	            		<li><a href="add_directories.php">Upload Directories<div class="explain">Import listings from file</div></a></li>
	            		<li><a href="manage_directories.php">Manage Directories<div class="explain">Create, edit and delete listings in the directory library</div></a></li>
	            	</ul> 
	            </li>
	            <li><a href="scrape_question_answer.php" >Question&amp;Answer</a> 
	            	<ul>
	            		<li><a href="scrape_question_answer.php">Scrape Questiones &amp; Answers<div class="explain">Scrap Q&amp;A from the web</div></a></li>
	            		<li><a href="manage_question_answer.php">Manage Questiones &amp; Answers<div class="explain">Create, edit and delete Q&amp;A in the library</div></a></li>
	            		<li><a href="add_related_keywords.php">Upload Related Keywords<div class="explain">Upload related keywords to display Q&amp;A in domains</div></a></li>
	            	</ul> 
	            </li>
	            <li><a href="scrape_event.php" >Events</a>
            	<ul>
            		<li><a href="scrape_event.php">Scrape Events<div class="explain">Scrap Events from the web</div></a></li>
            		<li><a href="manage_event.php">Manage Event<div class="explain">Create, edit and delete Events in the library</div></a></li>
            	</ul> 
	            <li><a href="manage_goal.php" >Goals</a></li>
	            <li><a href="manage_images.php" >Images</a></li>
	        </ul>
	        </li>
			<li><a class="fNiv">Community</a>
				<ul>
		            <li><a href="approve_comments.php" >Comments<div class="explain">Websites&rsquo; comments from community</div></a>
		            	<ul>
		            		<li><a href="approve_comments.php">Unapproved Comments<div class="explain">Delete, store or approve unapproved comments</div></a></li>
		            		<li><a href="view_comments.php">Approved Comments<div class="explain">Edit or delete approved comments</div></a></li>
		            		<li><a href="stored_comments.php">Stored Comments<div class="explain">Edit, Approve or delete stored comments</div></a></li>
		            	</ul> 
		            </li>
		            <li><a href="contactus_message.php?from=sx2">Messages<div class="explain">Websites&rsquo; emails from community</div></a>
		            	<ul>
		            		<li><a href="contactus_message.php?from=sx2">SX2 websites&rsquo; messages<div class="explain">Respond or delete emails from sx25 sites</div></a></li>
		            		<li><a href="contactus_message.php?from=parked">Parked websites&rsquo; messages<div class="explain">Respond or delete emails from Parked sites</div></a></li>
		            	</ul> 
		            </li>
	            </ul>
            </li>
            <li><a href="reports.php" class="fNiv">Reports</a> </li>
			
			<?php break;
			case 4 : ?>
			<li><a class="fNiv">Domains</a>
			<ul>
				<li><a href="domains_manager.php" >SX25 Domains</a>
					<ul>
						<li><a href="domains_manager.php">Domains Manager<div class="explain">Edit, delete and search domains in SX25</div></a></li>
						<li><a href="add_domains.php">Add Domains<div class="explain">Create new domains in SX25 Amazon servers</div></a></li>
						<li><a href="mass_modify.php">Mass Modify<div class="explain">Import and export domains settings</div></a></li>
						<li><a href="add_custom_menus.php">Add Custom Menus<div class="explain">Import fix menu for domains</div></a></li>
						<li><a href="add_mapping_keywords.php">Add Mapping Keywords<div class="explain">Import mapping keywords for domains</div></a></li>
   						<li><a href="sx25_genz.php">Regenerate GenZ css<div class="explain">Generate a new base template for sx25 domains</div></a></li>
						<li><a href="set_bulk_content.php">Set Up Domain Content<div class="explain">Set Up Domain Content for sx25 domains</div></a></li>
						<li><a href="tool_generate_sitemap.php">Generate Domain Sitemap<div class="explain">Read csv and generate the sitemaps for those domains</div></a></li>
						<li><a href="keyword_tracking.php">Keyword Tracking<div class="explain">Upload a csv that contains Domain, Keyword, Tracking, and save this info into the domain</div></a></li>
					</ul> 
				</li>
				<li><a href="parked_domains_manager.php" >Parked Domains</a>
	            	<ul>
	            		<li><a href="parked_domains_manager.php">Parked Domains Manager<div class="explain">Create, delete and search domains in Parked</div></a></li>
	            		<li><a href="add_parked_domains.php">Add Parked Domains<div class="explain">Add list of domains in Parked with its keywords</div></a></li>
	            		<li><a href="edit_parked_template.php">Edit Parked Template<div class="explain">Edit and change base templates</div></a></li>
	            		<li><a href="parked_genz.php">Generate Parked Templates<div class="explain">Generate a new base template for listing domains</div></a></li>
	            	</ul> 
				</li>
			</ul>
			</li>
			<li><a class="fNiv">Designs</a>
			<ul>
				<li><a href="custom_layout.php">SX25 Layouts<div class="explain">Edit websites layouts to use in SX25</div></a></li>
				<li><a href="parked_layout.php">Parked Layouts<div class="explain">Edit websites layouts to use in Parked</div></a></li>
				<li><a href="copy_layoutToParked.php">Copy Layouts to Parked<div class="explain">Copy layouts to Parked accounts</div></a></li>
	 		</ul>
	 		</li>
			<li><a class="fNiv">Libraries</a>
			<ul>
	            <li><a href="scrape_article.php" >Articles</a> 
	            	<ul>
	            		<li><a href="scrape_article.php">Scrape articles<div class="explain">Scrape articles from the web</div></a></li>
	            		<li><a href="manage_articles.php?server=sx25">Manage SX25 Articles<div class="explain">Create, edit and delete SX25 domains&rsquo; articles</div></a></li>
	            		<li><a href="manage_articles.php?server=parked">Manage Parked Articles<div class="explain">Create, edit and delete Parked domains&rsquo; articles</div></a></li>
	            		<li><a href="manage_articles.php?server=library">Manage Articles Library<div class="explain">Create, edit, delete and attach articles</div></a></li>
	            	</ul> 
	            </li>
	            <li><a href="scrape_directory.php" >Directory</a> 
	            	<ul>
	            		<li><a href="scrape_directory.php">Scrape Directories<div class="explain">Scrape listings from the web</div></a></li>
	            		<li><a href="add_directories.php">Upload Directories<div class="explain">Import listings from file</div></a></li>
	            		<li><a href="manage_directories.php">Manage Directories<div class="explain">Create, edit and delete listings in the directory library</div></a></li>
	            	</ul> 
	            </li>
	            <li><a href="scrape_question_answer.php" >Question&amp;Answer</a> 
	            	<ul>
	            		<li><a href="scrape_question_answer.php">Scrape Questiones &amp; Answers<div class="explain">Scrap Q&amp;A from the web</div></a></li>
	            		<li><a href="manage_question_answer.php">Manage Questiones &amp; Answers<div class="explain">Create, edit and delete Q&amp;A in the library</div></a></li>
	            		<li><a href="add_related_keywords.php">Upload Related Keywords<div class="explain">Upload related keywords to display Q&amp;A in domains</div></a></li>
	            	</ul> 
	            </li>
	            <li><a href="scrape_event.php" >Events</a>
            	<ul>
            		<li><a href="scrape_event.php">Scrape Events<div class="explain">Scrap Events from the web</div></a></li>
            		<li><a href="manage_event.php">Manage Event<div class="explain">Create, edit and delete Events in the library</div></a></li>
            	</ul> 
	            <li><a href="manage_goal.php" >Goals</a></li>
	            <li><a href="manage_images.php" >Images</a></li>
	        </ul>
	        </li>
			<li><a class="fNiv">Community</a>
				<ul>
		            <li><a href="approve_comments.php" >Comments<div class="explain">Websites&rsquo; comments from community</div></a>
		            	<ul>
		            		<li><a href="approve_comments.php">Unapproved Comments<div class="explain">Delete, store or approve unapproved comments</div></a></li>
		            		<li><a href="view_comments.php">Approved Comments<div class="explain">Edit or delete approved comments</div></a></li>
		            		<li><a href="stored_comments.php">Stored Comments<div class="explain">Edit, Approve or delete stored comments</div></a></li>
		            	</ul> 
		            </li>
		            <li><a href="contactus_message.php?from=sx2">Messages<div class="explain">Websites&rsquo; emails from community</div></a>
		            	<ul>
		            		<li><a href="contactus_message.php?from=sx2">SX2 websites&rsquo; messages<div class="explain">Respond or delete emails from sx25 sites</div></a></li>
		            		<li><a href="contactus_message.php?from=parked">Parked websites&rsquo; messages<div class="explain">Respond or delete emails from Parked sites</div></a></li>
		            	</ul> 
		            </li>
	            </ul>
            </li>
            <li><a href="reports.php" class="fNiv">Reports</a> </li>
			<li><a class="fNiv">Tools</a>
				<ul>
					<li><a href="parked_twin_domains.php">Upload Parked Twin Domains<div class="explain">Import file with twin domains pair</div></a></li>
					<li><a href="manage_cheapads.php">Manage List of Cheap Ads <div class="explain">List, edit and create list of Cheap Ads to check banned domains</div></a></li>
					<li><a href="tool_check_keywordsAds.php">Check number of Ads per keyword<div class="explain">Upload csv with domain and keyword and check the number of Ads keyword given by the feed</div></a></li>
				</ul> 
			</li>
			
			<?php break;
			case 5 : case 6 : ?>
			<li><a class="fNiv">Domains</a>
			<ul>
				<li><a href="domains_manager.php" >SX25 Domains</a>
					<ul>
						<li><a href="domains_manager.php">Domains Manager<div class="explain">Edit, delete and search domains in SX25</div></a></li>
						<li><a href="add_domains.php">Add Domains<div class="explain">Create new domains in SX25 Amazon servers</div></a></li>
						<li><a href="mass_modify.php">Mass Modify<div class="explain">Import and export domains settings</div></a></li>
						<li><a href="add_custom_menus.php">Add Custom Menus<div class="explain">Import fix menu for domains</div></a></li>
						<li><a href="add_mapping_keywords.php">Add Mapping Keywords<div class="explain">Import mapping keywords for domains</div></a></li>
						<li><a href="sx25_genz.php">Regenerate SX25 GenZ css<div class="explain">Generate a new base template for sx25 domains</div></a></li>
						<li><a href="set_bulk_content.php">Set Up Domain Content<div class="explain">Set Up Domain Content for sx25 domains</div></a></li>
						<li><a href="tool_generate_sitemap.php">Generate Domain Sitemap<div class="explain">Read csv and generate the sitemaps for those domains</div></a></li>
						<li><a href="keyword_tracking.php">Keyword Tracking<div class="explain">Upload a csv that contains Domain, Keyword, Tracking, and save this info into the domain</div></a></li>
					</ul> 
				</li>
				<li><a href="parked_domains_manager.php" >Parked Domains</a>
	            	<ul>
	            		<li><a href="parked_domains_manager.php">Parked Domains Manager<div class="explain">Create, delete and search domains in Parked</div></a></li>
	            		<li><a href="add_parked_domains.php">Add Parked Domains<div class="explain">Add list of domains in Parked with its keywords</div></a></li>
	            		<li><a href="edit_parked_template.php">Edit Parked Template<div class="explain">Edit and change base templates</div></a></li>
	            		<li><a href="parked_genz.php">Generate Parked Templates<div class="explain">Generate a new base template for listing domains</div></a></li>
	            	</ul> 
				</li>
			</ul>
			</li>
			<li><a class="fNiv">Designs</a>
			<ul>
	 			<li><a href="page_content.php">Pages<div class="explain">Edit fix pages to display in layouts</div></a></li>
	            <li><a href="module_layout.php">Modules<div class="explain">Edit layouts for modules</div></a></li>
				<li><a href="custom_layout.php">SX25 Layouts<div class="explain">Edit websites layouts to use in SX25</div></a></li>
				<li><a href="parked_layout.php">Parked Layouts<div class="explain">Edit websites layouts to use in Parked</div></a></li>
				<li><a href="copy_layoutToParked.php">Copy Layouts to Parked<div class="explain">Copy layouts to Parked accounts</div></a></li>
	 		</ul>
	 		</li>
			<li><a class="fNiv">Libraries</a>
			<ul>
	            <li><a href="scrape_article.php" >Articles</a> 
	            	<ul>
	            		<li><a href="scrape_article.php">Scrape articles<div class="explain">Scrape articles from the web</div></a></li>
	            		<li><a href="manage_articles.php?server=sx25">Manage SX25 Articles<div class="explain">Create, edit and delete SX25 domains&rsquo; articles</div></a></li>
	            		<li><a href="manage_articles.php?server=parked">Manage Parked Articles<div class="explain">Create, edit and delete Parked domains&rsquo; articles</div></a></li>
	            		<li><a href="manage_articles.php?server=library">Manage Articles Library<div class="explain">Create, edit, delete and attach articles</div></a></li>
	            	</ul> 
	            </li>
	            <li><a href="scrape_directory.php" >Directory</a> 
	            	<ul>
	            		<li><a href="scrape_directory.php">Scrape Directories<div class="explain">Scrape listings from the web</div></a></li>
	            		<li><a href="add_directories.php">Upload Directories<div class="explain">Import listings from file</div></a></li>
	            		<li><a href="manage_directories.php">Manage Directories<div class="explain">Create, edit and delete listings in the directory library</div></a></li>
	            	</ul> 
	            </li>
	            <li><a href="scrape_question_answer.php" >Question&amp;Answer</a> 
	            	<ul>
	            		<li><a href="scrape_question_answer.php">Scrape Questiones &amp; Answers<div class="explain">Scrap Q&amp;A from the web</div></a></li>
	            		<li><a href="manage_question_answer.php">Manage Questiones &amp; Answers<div class="explain">Create, edit and delete Q&amp;A in the library</div></a></li>
	            		<li><a href="add_related_keywords.php">Upload Related Keywords<div class="explain">Upload related keywords to display Q&amp;A in domains</div></a></li>
	            	</ul> 
	            </li>
	            <li><a href="scrape_event.php" >Events</a>
            	<ul>
            		<li><a href="scrape_event.php">Scrape Events<div class="explain">Scrap Events from the web</div></a></li>
            		<li><a href="manage_event.php">Manage Event<div class="explain">Create, edit and delete Events in the library</div></a></li>
            	</ul> 
	            <li><a href="manage_goal.php" >Goals</a></li>
	            <li><a href="manage_images.php" >Images</a></li>
	        </ul>
	        </li>
			<li><a class="fNiv">Community</a>
				<ul>
		            <li><a href="approve_comments.php" >Comments<div class="explain">Websites&rsquo; comments from community</div></a>
		            	<ul>
		            		<li><a href="approve_comments.php">Unapproved Comments<div class="explain">Delete, store or approve unapproved comments</div></a></li>
		            		<li><a href="view_comments.php">Approved Comments<div class="explain">Edit or delete approved comments</div></a></li>
		            		<li><a href="stored_comments.php">Stored Comments<div class="explain">Edit, Approve or delete stored comments</div></a></li>
		            	</ul> 
		            </li>
		            <li><a href="contactus_message.php?from=sx2">Messages<div class="explain">Websites&rsquo; emails from community</div></a>
		            	<ul>
		            		<li><a href="contactus_message.php?from=sx2">SX2 websites&rsquo; messages<div class="explain">Respond or delete emails from sx25 sites</div></a></li>
		            		<li><a href="contactus_message.php?from=parked">Parked websites&rsquo; messages<div class="explain">Respond or delete emails from Parked sites</div></a></li>
		            	</ul> 
		            </li>
	            </ul>
            </li>
            <li><a href="reports.php" class="fNiv">Reports</a> </li>
			<li><a class="fNiv">Tools</a>
				<ul>
					<li><a href="replace_images.php">Replace Images<div class="explain">Replace the maked images for new from the web</div></a></li>
					<li><a href="parked_twin_domains.php">Upload Parked Twin Domains<div class="explain">Import file with twin domains pair</div></a></li>
					<li><a href="cleanup.php">CleanUp Tools<div class="explain">Tools to cleanup database tables and folders</div></a></li>
					<li><a href="manage_themeimages.php">Manage CSS Library Images<div class="explain">Display and Upload Background images used in CSS for base templates</div></a>
					<li><a href="manage_cheapads.php">Manage List of Cheap Ads <div class="explain">List, edit and create list of Cheap Ads to check banned domains</div></a></li>
					<li><a href="tool_check_keywordsAds.php">Check number of Ads per keyword<div class="explain">Upload csv with domain and keyword and check the number of Ads keyword given by the feed</div></a></li>
				</ul> 
			</li>
			<?php break; 
			endswitch;?>
			</ul>
			</td>
		</tr>
		<tr>
			<td colspan="2" id="subnav" >
				<div id="describe"></div>
			</td>
		</tr>
		<tr>
			<td colspan="2"><img src="/images/s.gif" width="700" height="2" alt=""></td>
		</tr>
	</table>
</div>
