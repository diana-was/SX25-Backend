<?php
/**
 * Pages Content
 * Author: Diana Devargas 20/03/2011
**/
require_once('config.php');
require_once('header.php');

$Page = Page::getInstance($db);

$pageInfo = array('page_name' => ''
					, 'page_id' => ''
					, 'page_display_name' => ''
					, 'page' => ''
			);
			
$pageSave = '';
$succ = 0;
$fail = 0;
$error = false;

if(isset($_REQUEST['action']) && $_REQUEST['action'] == 'save_page')
{
	$pageInfo = array('page_name' => str_replace(' ','',$_REQUEST['page_name'])
						, 'page_display_name' => $_REQUEST['page_display_name']
						, 'page' => replace_tag($_REQUEST['page'])
						);
	
	// Validate errors
	$name = trim($_REQUEST['page_name']);
	if (empty($name)) $error = true;
	$displayName = trim($_REQUEST['page_display_name']);
	if (empty($displayName)) $error = true;
	if ($error) {
			$pageSave = '<font color="red">Page NOT Saved, Name or Display Name Missing!!</font>';
	}
	
	$checkID = $Page->check_page_id($name);
	if (empty($_REQUEST['page_id']) && ($checkID > 0)) {
		$error = true;
		$pageSave = '<font color="red">Page name already exist!</font>';
	}

	if (!$error) 
	{
		$page_id = $_REQUEST['page_id'] != '' ? $_REQUEST['page_id'] : 0;
		$new_page_id = $Page->save_page($pageInfo,$page_id,$error);
		
		if($new_page_id)
		{
			$pageSave = '<font color="green">Page Saved</font>';
		} 
		else
		{
			$pageSave .= '<font color="red">Error Updating!!!</font>';
		}
	} 
	else 
	{
		$pageInfo['page_id'] = isset($_REQUEST['page_id'])?$_REQUEST['page_id']:'';
		foreach ($pageInfo as $key => $val) {
			$pageInfo[$key] = stripslashes($val);
		}
	}
}

function trim_value(&$value) 
{ 
    $value = trim($value); 
}

if(!isset($_REQUEST['new']) && !$error)
{
	$pageID = isset($_REQUEST['page_id']) ? $_REQUEST['page_id'] : 1;
	$pageInfo = $Page->get_page_info($pageID);
}
?>
<script language="JavaScript">
    var changed = false;

    function change() {
    	changed = true;
    }

    function save(form) {
    	form.target='_self';
    	changed = false;
    	this.form.submit();
    }

    window.onbeforeunload = confirmExit;
    function confirmExit()
    {
      if (changed)
        return "You have unsave changes.";
    }
</script>

<div id="content">
	<table width="100%" border="0" cellspacing="0" cellpadding="0">
		<tr align="center">
			<td class="brdrL">&nbsp;</td>
			<td valign="top" id="main">
				<?php if($pageSave != '') : ?>
				<table border="0" cellpadding="0" cellspacing="0" width="100%" id="boxGray">
					<tr>
						<td valign="top"><div class="blueHdr">System Message</div>
						<div class="content" align="center">
				        <?php echo $pageSave;?>
						</div>
						</td>
					</tr>
				</table>
				<br />
				<?php endif; ?>
			
				<!-- *** START MAIN CONTENTS  *** -->
				<table border="0" cellpadding="0" cellspacing="3" width="100%">
				<tr>
					<td>
						<div id="boxGray">
							<div class="greenHdr">Custom Page</div>
				
								<form action="/page_content.php" method="post" enctype="multipart/form-data" name="custom_page">
						    	<input type="hidden" name="action" value="save_page">
						        
						        <div id="custLayout">
						        	<div class="custTextR">Existing Pages: 
					                    <select class="inputSelect" name="page_go" onchange="location.href=this.options[this.selectedIndex].value; change();">
					                    	<option value="/page_content.php?new=1" >Create new blank page</option>
											<?php
											$pages = $Page->get_pages();
											foreach ($pages as $pRow) 
											{
												echo '<option value="/page_content.php?page_id='.$pRow['page_id'].'&mod=1"';
												if(!isset($_REQUEST['new']) && $pRow['page_id'] == $pageInfo['page_id'])
													echo ' selected';
												echo '>'.$pRow['page_name'].'</option>';
											}
											?>
					                	</select>
						        	</div>	
						        	<div class="custTextL">Page Name: 
						        		<input name="page_name" type="text" class="frmCLText" value="<?php echo $pageInfo['page_name'];?>" size="40" maxlength="255"  onChange="change();">
						        	</div>
						           	<br class="clearboth">
									<div class="custTextL">Display name:
						        		<input name="page_display_name" id="page_display_name" type="text" class="frmCLText" value="<?php echo $pageInfo['page_display_name'];?>" size="40" maxlength="255" onChange="change();">
						        	</div>	
						           	<br class="clearboth">
						           	<br class="clearboth">
				
									<div class="custTextL">Page Content:<br />
										<textarea cols="120" rows="40" name="page" id="page" class="frmCLArea"  onChange="change();"><?php echo $pageInfo['page'];?></textarea>
									</div>
										
								  	<br class="clearboth">
								  	<?php if ($user->userLevel >= 5) : ?>
			                        <p  class="alignCenter">
										<input name="apply" type="submit" class="submitbutton"  value="Save" onclick="save(this.form)">
									</p>
				                    <input type="hidden" name="page_id" value="<?php echo $pageInfo['page_id'];?>" />
				                    <?php endif; ?>
								</div>
				    		</form>
							</div>
					</td>
				</tr>
				</table>
		
			
			<!-- *** END MAIN CONTENTS  *** -->
			
					
			</td>
			<td class="brdrR">&nbsp;</td>
		</tr>
	</table>

</div>


<?php
function replace_tag($string)
{
	$string = str_replace('<', '&lt;', $string);
	$string = str_replace('>', '&gt;', $string);
	return $string;
}
require_once('footer.php');
?>