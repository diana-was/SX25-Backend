<?php
/**
 * Domains
 * Author: Gordon Ye on 29/11/2010
**/

require_once('config.php');
$msg = Message::getInstance($db);

if (!empty($_REQUEST['action']))
{
	switch ($_REQUEST['action'])
	{
		case 'delete_message':
			if (!empty($_REQUEST['message_id']))
				$msg->del_message($_REQUEST['message_id']);
			break;
		case 'reply_contactus':
			if(!empty($_REQUEST['message_id']))
			{
				$msg->change_message_status($_REQUEST['message_id'],1);
				
				$subject = $_REQUEST['subject'];
				$body = $_REQUEST['content'];
				$email = $_REQUEST['email'];
				$receiver = $_REQUEST['receiver'];
				$sender = 'Team '.strtoupper($_REQUEST['domain']);
				require_once 'classes/Swift-4.0.6/lib/swift_required.php';
				 
				 $transport = Swift_MailTransport::newInstance();
				 $mailer = Swift_Mailer::newInstance($transport);
				 $message = Swift_Message::newInstance()
				  ->setSubject($subject)
				  ->setFrom(array('info@'.$_REQUEST['domain'] => $sender))
				  ->setTo(array($email, $email => $receiver))
				  ->setBody($body);
				 $result = $mailer->send($message);
			}
			break;
	}
	exit;
}

require_once('header.php');
?>

<link rel="stylesheet" href="thickbox/thickbox.css" type="text/css" media="screen" />
<script>
function display_form(id){
	$('#reply_form_'+id).show(300);
}

function hide_form(){
	$('.reply_form').hide(300);
}

function delete_message(id){
	var rm = confirm('Do you want to remove this message without reply? ');
	if(rm){
		$.get('contactus_message.php?action=delete_message&message_id='+id, function(data){
			$('#message_row_'+id).hide(300);			  
		});	
	}
	return false;
}

function reply_contactus(id, email, receiver, domain){	
	var content = $('#reply_content_'+id).val();
	var reply_subject = $('#subject_'+id).val();
	$.get("contactus_message.php", {action:'reply_contactus', email:email, receiver:receiver, domain:domain, content:content, subject:reply_subject, message_id:id},function(data){
     	hide_form();
		$('#message_row_'+id).hide(300);
   });	
}

</script>
<style>
.normal{font-size:12px; margin: 5px;}
.outstanding{margin: 5px; font-size:15px; font-weight:bold; text-decoration: none;}
</style>

<?php
$totalNum = 0;
$page = isset($_REQUEST['page'])?$_REQUEST['page']:'1';
$from = (empty($_REQUEST['from']) || $_REQUEST['from']!='parked')?'sx2':'parked';
$offset = 30*($page-1);
$pages = ceil($msg->count_comments($from,0)/30);
$pResults = $msg->list_comments($from,0,30,$offset);

?>

<div id="content">
	<table width="100%" border="0" cellspacing="0" cellpadding="0">
		<tr>
			<td class="brdrL" style="width:12px;">&nbsp;</td>

			<td valign="top" id="main">
			
			
			<!-- *** START MAIN CONTENTS  *** -->

<table align="center" border="0" cellspacing="0" cellpadding="0" width="100%" class="tablePop">
	<tr>
		<td align="left" valign="top"><span class="txtHdr"><?php if($_REQUEST['from']!='parked') echo 'SX2 '; else echo 'Parked '; ?> Contact Us Messages</span>
        	<span style="margin-left:100px;">
			<?php 
			for($i=1; $i<=$pages; $i++){
				$class=($page==$i)?'outstanding':'normal';
				echo "<a href='contactus_message.php?from=".$from."&page=".$i."' class='".$class."'>".$i."</a>";
			}
			
			?></span>
        </td>
		<td align="right" valign="top"></td>
	</tr>

	<tr>
		<td colspan="2" valign="top">

<div id="data_table" >
<div id="tableData">
<table width="100%" border="0" cellspacing="1" cellpadding="0" id="dt1" class="dataTable">
	<tr>
	<td align="center" class="cellHdr"></td><td align="center" class="cellHdr"><a>Subject</a></td><td align="center" class="cellHdr"  style="width:70%"><a>Content</a></td><td align="center" class="cellHdr"><a>Author</a></td><td align="center" class="cellHdr"><a>Domain</a></td><td align="center" class="cellHdr"><a>Email</a></td><td align="center" class="cellHdr"><a>Date</a></td></tr>
    
<?php

foreach ($pResults as $pRow) 
{
	if($_REQUEST['from']!='parked')
		$domainUrl = $db->select_one("SELECT domain_url FROM domains WHERE domain_id='".$pRow['domain_id']."'");
	else
		$domainUrl = $pRow['domain'];
		
	$message_id = $pRow['message_id'];
	$email = $pRow['email'];
	?>



    
    <tr class="alter1" id="message_row_<?php echo $message_id; ?>">
    	<td  width="30" align="center" valign="middle" ><?php echo $message_id; ?></td>
        <td align="left" valign="middle" ><a><?php echo $pRow['subject'];?></a></td>
        <td align="left" valign="middle" width="70%"><?php echo $pRow['message'];?>
       		<p><br />      		
        		<a href="#" onclick="delete_message(<?php echo $message_id; ?>); return false;" style="margin-left:330px">Remove</a>
                <a href="#" onclick="display_form('<?php echo $message_id; ?>'); return false;" style="margin-left:130px">Reply</a><br /><br />
            </p>
        		<div id="reply_form_<?php echo $pRow['message_id']; ?>" class="reply_form" style="display:none; padding:15px; margin:15px; border-top:1px dashed #ccc;">
                	<h3>Subject:</h3><input type="text" name="reply_subject" id="subject_<?php echo $pRow['message_id']; ?>" value="" style="margin-left:120px;  width:320px" />
                    <h3>Message:</h3>
                    <textarea cols="100" rows="20" name="content" id="reply_content_<?php echo $message_id; ?>"  style="margin-left:120px">Dear <?php echo $pRow['name'];?>, 
                    
 
                   
                    
Team <?php echo  strtoupper($domainUrl);?> 

http://<?php echo $domainUrl;?></textarea>
                	<button onclick="reply_contactus('<?php echo $message_id; ?>', '<?php echo $email; ?>', '<?php echo $pRow['name'];?>', '<?php echo $domainUrl;?>')" >Send</button>
                     <button style=" margin-left: 50px;margin-bottom:5px;" onclick="hide_form()">Close</button>
                    
        	    </div>
        	</td>
        <td align="left" valign="middle" ><?php echo $pRow['name'];?></td>
        <td align="center" valign="middle" ><a href="http://<?php echo $domainUrl;?>" target="_blank" ><?php echo $domainUrl;?></a></td>
        <td align="center" valign="middle" ><?php echo $pRow['email'];?></td>
         <td align="center" valign="middle" ><?php echo $pRow['message_date'];?></td>
		
   </tr>
<?php
}
?>

	</table>
</div>

<br>

</div>

		</td>
	</tr>
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