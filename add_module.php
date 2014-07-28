<?php
/**
 * Domains
 * Author: Gordon Ye on 08/06/2010
**/
require_once('config.php');
require_once('header.php');

$Site = Site::getInstance($db);
$Profile = new Profile();

// ---------------Ajax from here ------------------------
if(isset($_REQUEST['action']) && $_REQUEST['action'] == 'save_page')
{
	$menu_name = implode('&',explode(',',$_REQUEST['modules']));
	$menu_name_display = $_REQUEST['menu_name_display']!=''?$_REQUEST['menu_name_display']:$menu_name;
	$feed_status = $_REQUEST['feed_status'];
	$feed_position = $_REQUEST['feed_position'];
	$domain_id = $_REQUEST['domain_id'];
	$menu_id = $_REQUEST['menu_id'];
	$updateQ = "UPDATE menus SET menu_name='$menu_name', menu_name_display='$menu_name_display', menu_domain_id='$domain_id', feed_status='$feed_status', feed_position='$feed_position'  WHERE menu_id=$menu_id";

	$db->update_sql($updateQ);
	exit;
}
if(isset($_REQUEST['action']) && $_REQUEST['action'] == 'add_page')
{
	$menu_name = implode('&',explode(',',$_REQUEST['modules']));
	$menu_name_display = $_REQUEST['menu_name_display']!=''?$_REQUEST['menu_name_display']:$menu_name;
	$feed_status = $_REQUEST['feed_status'];
	$feed_position = $_REQUEST['feed_position'];
	$domain_id = $_REQUEST['domain_id'];
	$insertQ = "INSERT INTO menus (menu_domain_id,menu_name,menu_name_display,feed_status,feed_position) VALUES (".$domain_id.",'".$menu_name."','".$menu_name_display."','".$feed_status."','".$feed_position."')";

	$db->insert_sql($insertQ);
	exit;
}
if(isset($_REQUEST['action']) && $_REQUEST['action'] == 'delete_menu')
{
	$pid = $_REQUEST['pid'];
	$Menu->del_menu($pid);
	exit;
}

// ----------------Ajax end ---------------------------------

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
var domain_id=<?php echo $domain_id;?>;

$(function(){
	hideAll();
	$('#edit_page_button').removeClass('opacity'); 
	$('#edit_page').show();
});

function save_page(pid, did, mid){
	var mls = '';
	for(i=0; i < modules; i++){
		var chk = 'modules_'+pid+'_'+i;
		//$('form #mycheckbox').is(':checked');
		if($('#'+chk).is(':checked')){
			var m = $('#'+chk).val();
			if(m!=''){
				if(mls!='')
					m = ','+m;
				mls = mls + m;
			}
		}
	}
	
	var menu_name_display = $('#menu_name_display_'+pid).val();
	
	var feed_status = '0';
	var fck = 'feed_status_'+pid;
	if($('#'+fck).is(':checked')){
		feed_status = '1';
	}
	//--------- implement feed_position here later -------------------
	var feed_position =  $('#feed_position_'+pid).val();
	
	$.get('edit_domain_25.php?action=save_page&modules='+mls+'&menu_name_display='+menu_name_display+'&feed_status='+feed_status+'&feed_position='+feed_position+'&domain_id='+did+'&menu_id='+mid, function(){
		//$(".p6:eq("+sh+")").html('this is it!');		
		$('#msg_'+pid).html('Page change has been updated.').fadeIn('slow');
		setTimeout(function(){$('#msg_'+pid).fadeOut('slow');}, 2500);
	});	
}

function delete_menu(pid, mid){
	var dl = confirm('Are you going to delete this page?');
	if(dl){
		$.get('edit_domain_25.php?action=delete_menu&pid='+mid, function(){
			$('#page_'+pid).hide(300);
			$('#page_'+pid).next().hide();
		});
	}
}

function show_addPage(){
	hideAll();
	$('#add_page_button').removeClass('opacity');	
	$('#add_page, #more_page_button').show(300);	
	generate_addPage_form();
}

function show_sidebar(){
	hideAll();
	$('#sidebar_button').removeClass('opacity');
	$('#sidebar_page').show(300);	
}

function show_image(){
	hideAll();
	$('#image_button').removeClass('opacity');	
	$('#image_page').show(300);	
}

function show_configure(){
	hideAll();
	$('#configure_button').removeClass('opacity');	
	$('#configuration_page').show(300);	
}

function hideAll(){
	$('#edit_page_button, #add_page_button, #sidebar_button, #image_button, #configure_button').addClass('opacity');
	$('#edit_page, #add_page, #sidebar_page, #more_page_button, #image_page, #configuration_page').hide();
}

//-----------manipulate page ---------------------------------

var ap = 1;

function generate_addPage_form(){
	var module_str = '';
	for (var key in mo) {
	  	if (mo.hasOwnProperty(key)) {		
			module_str += '<div class="s'+key+'"><input type="checkbox" name="modules_a_'+ap+'_'+key+'"  id="modules_a_'+ap+'_'+key+'"  value="'+mo[key]+'" >'+mo[key]+'<br /> </div>';
		//alert(key + " -> " + mo[key]);
	  	}
	}

	var str = '<div id="page_'+ap+'" class="page"><div class="p1">New Page '+ap+'. </div><div class="p2">'+module_str+'</div><div class="p3"><input type="text" name="menu_name_display_a_'+ap+'" id="menu_name_display_a_'+ap+'" value=""></div><div class="p4"><input type="checkbox" class="fs" name="feed_status_a_'+ap+'" id="feed_status_a_'+ap+'"  checked ></div><div class="p5"><input type="text" name="feed_position_a_'+ap+'" id="feed_position_a_'+ap+'"  value="0"></div><div class="p6"><button class="save_page_button" onclick="add_page('+ap+', '+domain_id+'); this.style.display=\'none\'; return false;">Add</button></div></div><div class="spacer"></div>';
	$('#add_page').append(str);
	ap++;
}

function add_page(npid, did){
	var mls = '';
	for(i=0; i < modules; i++){
		var chk = 'modules_a_'+npid+'_'+i;
		//$('form #mycheckbox').is(':checked');
		if($('#'+chk).is(':checked')){
			var m = $('#'+chk).val();
			if(m!=''){
				if(mls!='')
					m = ','+m;
				mls = mls + m;
			}
		}
	}
	
	var menu_name_display = $('#menu_name_display_a_'+npid).val();
	
	var feed_status = '0';
	var fck = 'feed_status_a_'+npid;
	if($('#'+fck).is(':checked')){
		feed_status = '1';
	}
	//--------- implement feed_position here later -------------------
	var feed_position =  $('#feed_position_a_'+npid).val();
	
	$.get('edit_domain_25.php?action=add_page&modules='+mls+'&menu_name_display='+menu_name_display+'&feed_status='+feed_status+'&feed_position='+feed_position+'&domain_id='+did, function(){
		$('#page_'+npid+' .p6').html('<span class="msg">Page is added.</span>');
	});	
}

function update_widget(did, wid){
	var action = 'delete_widget';
	var fck = 'widget_'+wid;
	if($('#'+fck).is(':checked')){
		action = 'insert_widget';
	}
		
	$.get('edit_domain_25.php?action='+action+'&widget_id='+wid+'&domain_id='+did, function(){
		$('#msg_widget').html('Sidebar widget has been updated.').fadeIn('slow');
		setTimeout(function(){$('#msg_widget').fadeOut('slow');}, 2500);
	});	
}

</script>

<style>
.p1, .p2, .p3, .p4, .p5{float:left; width:160px;}
.page{clear:both; padding:10px; margin-left:20px}
.save_page_button{margin-right:15px;}
.spacer{height:20px; width: 90%; margin:10px; border-bottom:1px dashed #333; clear: both;}
.fs{margin-left:45px}
.opacity{
	opacity: .5;
	filter: alpha(opacity=50); 	
}
#edit_page_button, #add_page_button, #sidebar_button, #image_button, #configure_button{margin:10px 45px;}
#more_page_button{margin:15px auto auto 300px}
.msg{color:red; padding:15px;}
</style>

<div id="main_content">
	<table width="100%" border="0" cellspacing="0" cellpadding="0">
		<tr>

			<td class="brdrL">&nbsp;</td>
			<td valign="top" id="main" style="width:100%">
			
			
			<!-- *** START MAIN CONTENTS  *** -->
			
	<div>
         <span class="txtHdr" style="float:left;">Add Module</span>
    </div>
<br>
<br>
<table align="center"  border="0" cellpadding="3" cellspacing="0" width="100%" id="boxGray">


		<tr>	
		<td align="center" valign="top" colspan="2">
			
		<table width="100%">
				<tr>
		<td class="greenHdr" width="20%"></td>
       
        <td class="greenHdr" align="left" width="80%"><button id="edit_page_button" onClick="window.location.reload();">Edit Pages</button><button id="add_page_button" onClick="show_addPage(); return false;">Add Page</button><button id="sidebar_button" onClick="show_sidebar(); return false;">Edit Sidebar</button><button id="image_button" onClick="show_image(); return false;">Organise Images</button><button id="configure_button" onClick="show_configure(); return false;">Domain Configuration</button></td>
	</tr>
			<tr>
			<td width="20%" valign="top">
            
            
			</td>
            
            
            
            
        
            
            
                        
            <!--------------------------------------- menus / sidebar---------------------------------------------->  
            
			
            <td valign="top" >
            <div id="edit_page">	
             
            </div>
            
            
            
             <div id="add_page">            
            	
        
            </div>                 
               
            
            
            
           
                
            </td>         
          <!--------------------------------------- menus / sidebar end---------------------------------------------->  
         
         
         
         
         
         
         
         
         
         
         
            
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



<?php
require_once('footer.php');
?>
