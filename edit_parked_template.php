<?php
/**
 * output various reports
 * Author: Diana De vargas 2011-04-20
**/

require_once('config.php');

session_start();
$session_id = session_id();

$url = (isset($_REQUEST['domain']) && !empty($_REQUEST['domain']))?strtolower(trim($_REQUEST['domain'])):'';

/* ajax calls */
$queue = new cssQueue($db);
$themes = $queue->getApproveThemes();

/* css combination  */
if(isset($_REQUEST['action']) && $_REQUEST['action']=='generateCombination'){
	$queue->generateCombination();
	echo $queue->getCombinationAmount();
	exit;
}

if(isset($_REQUEST['action']) && $_REQUEST['action']=='themeSearch'){
	$themes = $queue->themeSearch($_REQUEST['seekstr']);
	echo json_encode($themes);
	exit;
}


/* ajax image */
$ic = new imageTemplate($db);
$lib = ImageLibrary::getInstance($db);

if(isset($_REQUEST['action']) && $_REQUEST['action']=='loadBingPics'){	
	$data = $ic->getBingImageSearch($_REQUEST['keyword'], $_REQUEST['start']);
	echo json_encode($data);
	exit;
}
if(isset($_REQUEST['action']) && $_REQUEST['action']=='loadGooglePics'){
	$data = $lib->getGoogleImageSearch($_REQUEST['keyword'], $_REQUEST['start']);
	echo json_encode($data);
	exit;
}
if(isset($_REQUEST['action']) && $_REQUEST['action']=='loadDatabasePics'){
	$data = $ic->loadDatabasePics($_REQUEST['start'], $_REQUEST['type']);
	echo json_encode($data);
	exit;
}
if(isset($_REQUEST['action']) && $_REQUEST['action']=='setImage'){
	$ic = new imageTemplate($db);
	$data = $ic->setImage($url, $_REQUEST['path'], $_REQUEST['location'], $_REQUEST['keyword']);
	echo json_encode(array('image' => $data));
	exit;
}

/* Get Domain Data */

$Domain = ParkedDomain::getInstance($db);
$domainInfo = $Domain->get_domain_info_name($url);
if ($domainInfo == false)
{
	require_once('header.php');
?>
	<div id="main_content">
		<table width="100%" border="0" cellspacing="0" cellpadding="0">
			<tr>
	
				<td class="brdrL">&nbsp;</td>
				<td valign="top" id="main" style="width:100%">
				
				
				<!-- *** START MAIN CONTENTS  *** -->
				
			 <div>
	         <span class="txtHdr" >Domain : <input type="text" id="domain" name="domain" size="60" max="250" value="<?php echo $url; ?>" onchange="window.location='edit_parked_template.php?domain='+this.value; " /></span>
	         <br/>
	         <?php if (!empty($url)): ?>
	         <span class="txtHdr" >Domain information not found</span>
	         <?php endif; ?>
	         <div id="arrow_bar" style="margin:auto; text-align:center;  margin-top:10px">
				<div ><table style="margin:auto; margin-top:12px"><tr><td id="bwd" style="border-bottom:0px;border-left:0px;"></td><td id="image_gallery" style="border-bottom:0px;border-left:0px;"></td><td id="fwd" style="border-bottom:0px;border-left:0px;"></td></tr></table>
	            </div>
			 </div>
	         </div>
	         </td>
	         </tr>
	     </table>
	 </div>
<?php	
	exit();
}
$domain_id = $domainInfo['domain_id'];
$keyword = $domainInfo['domain_keyword'];
$layout_id = $domainInfo['domain_layout_id'];

$Layout = new LayoutParked();
$layoutInfo = $Layout->get_layout_info($layout_id);

$css = new cssMaker($db);
$css->_domain = $url;
$css->_session = $session_id;
$css->_action = isset($_REQUEST['action'])?$_REQUEST['action']:'';
$css->_layout_id = $layout_id;

/* css combination  */
if(isset($_REQUEST['action']) && $_REQUEST['action']=='startTestCombination'){
	$resultArray['css_pending_id'] = $css->getNextCombination($url);
	$resultArray['combination_amount'] = $queue->getCombinationAmount();
	echo json_encode($resultArray);
	exit;
}

/* restore previous css file content */
if(isset($_REQUEST['action']) && $_REQUEST['action']=='undo'){
	$css->undoCss();
	exit;
}

/* remove all css cache in css_backup table */
if(isset($_REQUEST['action']) && $_REQUEST['action']=='clean_cache'){ 
	$css->clean_cache();
	exit;
}

/* backup css file before any changing */
if(isset($_REQUEST['action']) && !empty($_REQUEST['action']) && !empty($url)){ 
	$css->backupCss();
}
/***************************************/

if(isset($_REQUEST['action']) && $_REQUEST['action']=='auto_reset_template'){
	//$css->setRandomCss($url);
	echo $css->setRandomTheme($url,'parked');
	exit;
}
if(isset($_REQUEST['action']) && $_REQUEST['action']=='reset_default_template'){
	$css->setDefaultCss($url,$layout_id,'parked');
	echo $url;
	exit;
}
if(isset($_REQUEST['action']) && $_REQUEST['action']=='setColorTheme'){
	$css->setColorTheme($url, $_REQUEST['theme'], $layout_id, 'parked');
	echo $url;
	exit;
}
if(isset($_REQUEST['action']) && $_REQUEST['action']=='enableOpacity'){
	$css->enableOpacity($url,  $_REQUEST['value']);
	exit;
}
if(isset($_REQUEST['action']) && $_REQUEST['action']=='disableOpacity'){
	$css->disableOpacity($url);
	exit;
}
if(isset($_REQUEST['action']) && $_REQUEST['action']=='removeBackground'){
	$css->removeBackground($url);
	exit;
}
if(isset($_REQUEST['action']) && $_REQUEST['action']=='updateHeaderHeight'){
	$css->updateHeaderHeight($url, $_REQUEST['height']);
	exit;
}
if(isset($_REQUEST['action']) && $_REQUEST['action']=='existingImageBackground'){
	$css->existingImageBackground($url,  $_REQUEST['css_id'], $_REQUEST['type']);
	exit;
}
if(isset($_REQUEST['action']) && $_REQUEST['action']=='setFontFamily'){
	$css->setFontFamily($url, $_REQUEST['value']);
	exit;
}
if(isset($_REQUEST['action']) && $_REQUEST['action']=='updateCss'){
	$value = empty($_REQUEST['color']) ? $_REQUEST['value'] : $_REQUEST['color'];
	$css->updateCss($url, $_REQUEST['location'], $_REQUEST['keyword'], $value);
	exit;
}

$fonts = $css->getFontFamily();

require_once('header.php');
?>

<link rel="stylesheet" media="screen" type="text/css" href="css/edit_template.css" />
<link rel="stylesheet" media="screen" type="text/css" href="css/colorpicker/css/colorpicker.css" />
<script type="text/javascript" src="js/colorpicker/js/colorpicker.js"></script>
<script>
var domainurl='<?php echo $url; ?>';
var domain_id = <?php echo $domain_id; ?>;
var start = 1;
var keyword = '<?php echo $keyword; ?>';
var myeid; 
var page='index';
var css_pending_id;

$(function(){
	hideAll();
		
	$('#tc_button2').click(function () {
		if ($("#edit_template").is(":hidden")) {
			$("#edit_template").slideDown("slow");
			$('#tc_button2').attr('src','images/up.png');
		} else {
			$("#edit_template").hide();
			$('#tc_button2').attr('src','images/down.png');
		}
	}); 
	
	$('#tc_button1').click(function () {
		if ($("#theme").is(":hidden")) {
			$("#theme").slideDown("slow");
			$('#tc_button1').attr('src','images/up.png');
		} else {
			$("#theme").hide();
			$('#tc_button1').attr('src','images/down.png');
		}
	}); 
	
	$('#tc_button').click(function () {
		if ($("#combination").is(":hidden")) {
			$("#combination").slideDown("slow");
			$('#tc_button').attr('src','images/up.png');
		} else {
			$("#combination").hide();
			$('#tc_button').attr('src','images/down.png');
		}
	});
	
	$('#css_button').click(function () {
		if ($("#css_content").is(":hidden")) {
			$("#css_content").slideDown("slow");
			$('#css_button').attr('src','images/up.png');
		} else {
			$("#css_content").hide();
			$('#css_button').attr('src','images/down.png');
		}
	});
	
		
	$("#background_image_link").click(function () {
     	 $("#background_list").slideToggle("slow");
    });
	
	$("#color_themes_link").click(function () {
      	$("#color_themes_list").slideToggle("slow");
    });
	
	$("#background, #header_background_color, #header_text_color, #footer_background_color, #footer_text_color, #left_sidebar_background_color,  #left_sidebar_text_color,#right_sidebar_background_color, #right_sidebar_text_color, #sidebar_text_color, #menu_color, #menu_background_color, #text_color, #logo_color, #logo_background_color, #sponsor_title_color, #sponsor_description_color, #sponsor_url_color").click(function () {
     	myeid = $(this).attr('id');
    });
	
	$('#background, #header_background_color, #header_text_color, #footer_background_color, #footer_text_color, #left_sidebar_background_color, #left_sidebar_text_color, #right_sidebar_background_color, #right_sidebar_text_color, #sidebar_text_color, #menu_color, #menu_background_color, #text_color, #logo_color, #logo_background_color, #sponsor_title_color, #sponsor_description_color, #sponsor_url_color').ColorPicker({
			onSubmit: function(hsb, hex, rgb, el) {
				$(el).val(hex);
				$(el).ColorPickerHide();
				updateCss();
			},
			onBeforeShow: function () {
				$(this).ColorPickerSetColor(this.value);
			}
		})
		.bind('keyup', function(){
			$(this).ColorPickerSetColor(this.value);
	});
		
		
	$('#theme li').click(function(){
		$('#theme li').removeClass('selected');
		$(this).addClass('selected');
		var i = $(this).children('b').text();
		setColorTheme(i);
		return false;
	});
	
});

function updateCss(){
	var clr = $('#'+myeid).val();
	reloadIframe();
	$.get("edit_parked_template.php", {action:'updateCss', location:myeid, color:clr, keyword: keyword, domain:domainurl},function(data){
		reloadURL();		
    });
}

function setSponsorSize(loc, value){
	if(loc=='title')
		location = 'sponsor_title_size';
	else if(loc=='title')
		location = 'sponsor_description_size';
	else if(loc=='title')
		location = 'sponsor_url_size';
		
	reloadIframe();	
	$.get("edit_parked_template.php", {action:'updateCss', location:location, value:value, domain:domainurl},function(data){
			reloadURL();
    });
}
function setTextSize(value){
	reloadIframe();	
	$.get("edit_parked_template.php", {action:'updateCss', location:'font_size', value:value, keyword: keyword, domain:domainurl},function(data){
			reloadURL();
    });
}

function setMenuTextSize(value){
	reloadIframe();
	$.get("edit_parked_template.php", {action:'updateCss', location:'menu_font_size', value:value, domain:domainurl},function(data){
			reloadURL();	
    });
}

function setRightSize(value){
	reloadIframe();
	$.get("edit_parked_template.php", {action:'updateCss', location:'setRightSize', value:value, domain:domainurl},function(data){
			reloadURL();	
    });
}
function setLeftSize(value){
	reloadIframe();
	$.get("edit_parked_template.php", {action:'updateCss', location:'setLeftSize', value:value, domain:domainurl},function(data){
			reloadURL();	
    });
}

function setMenuTextBold(value){
	reloadIframe();	
	$.get("edit_parked_template.php", {action:'updateCss', location:'setMenuTextBold', value:value, domain:domainurl},function(data){
			reloadURL();
    });
}

function setRightTextBold(value){
	reloadIframe();	
	$.get("edit_parked_template.php", {action:'updateCss', location:'setRightTextBold', value:value, domain:domainurl},function(data){
			reloadURL();
    });
}

function setLeftTextBold(value){
	reloadIframe();	
	$.get("edit_parked_template.php", {action:'updateCss', location:'setLeftTextBold', value:value, domain:domainurl},function(data){
			reloadURL();
    });
}

function setFontFamily(value){
	var clr = $('#'+myeid).val();
	reloadIframe();	
	$.get("edit_parked_template.php", {action:'setFontFamily', value:value, domain:domainurl},function(data){
			reloadURL();
    });
}

function updateHeaderHeight(){
	var height = $('#header_height').val();
	reloadIframe();	
	$.get("edit_parked_template.php", {action:'updateHeaderHeight', domain:domainurl, height:height},function(data){
		reloadURL();		
    });
}

function reloadIframe(){
	$.blockUI({ message: '<h1><img src="images/loading.gif" /> Just a moment...</h1>' }); 	
}

function reloadURL(){
	$("#iFrame").attr("src", "http://"+domainurl);
 	$("#iFrame").load();
 	$.unblockUI();
}

function undo(){
	reloadIframe();	
	$.get("edit_parked_template.php", {action:'undo', domain:domainurl},function(data){
			reloadURL();
    });
}

function switchTemplate(){
	var s = domainurl.indexOf('result.php');
	if(s=='-1'){
		domainurl = domainurl+'/result.php?Keywords=car';
		page='result';
	}
	else{
		var url = domainurl.split('/');
		domainurl =url[0];
		page='index';
	}
	reloadIframe();
	reloadURL();
}

function hideAll(){
	$('#combination, #theme, #edit_template, #css_content, #dialog, #dialog_css').hide(); 
}

function auto_reset_template(){
	reloadIframe();
	$.get('edit_parked_template.php?action=auto_reset_template&domain='+domainurl,function(data){
		reloadURL();
		$('#theme li').removeClass('selected');
		$('#theme_'+data).addClass('selected');			
	});
}

function reset_default_template(){
	reloadIframe();
	$('#theme li').removeClass('selected');
	$.get('edit_parked_template.php?action=reset_default_template&domain='+domainurl,function(data){
		reloadURL();																				
	});
}

function setColorTheme(theme){
	if(null==theme || theme=='')
		return false;
	reloadIframe();
	$.get('edit_parked_template.php?action=setColorTheme&theme='+theme+'&domain='+domainurl,function(data){
			reloadURL();
	});
}

function from_database(s, type){
	if(s=='+')
		start+=10;
	else if(s=='-' && start>10){
		start-=10;
	}else if(s=='-' && start<10){
		$("#image_gallery").html('<span style="font-size:12px;">No more images. Please look forward.</span>');
		return false;
	}else{
		start=0;	
	}

	$("#image_gallery").html('<img src="images/loading.gif" style="border:0px;" />');
	
	$.getJSON("edit_parked_template.php", {action:'loadDatabasePics', start:start, type:type},function(data){
		  if(null==data || data==''){
				$("#image_gallery").html('<span style="font-size:12px;">No more images. Please look backward.</span>');
				return false;  
		  }
		  $("#image_gallery").html('');
		  $("#bwd").html('<img onclick="from_database(\'-\', \''+type+'\')" src="images/ar6.gif" style="cursor:pointer; border:0px; padding:6px 12px;" />');
		  $("#fwd").html('<img onclick="from_database(\'+\', \''+type+'\')" src="images/ar5.gif" style="cursor:pointer; border:0px; padding:6px 12px;" />');
			if(type=='background')
		  		var wh = 'width:120px; height:160px;';
		  	else if(type=='header_background_image')
		  		var wh = 'width:220px; height:100px;';
		  for(x in data){
  				$("#image_gallery").append("<img src='<?php echo $config->sx25cssImageLink; ?>/"+data[x].image+"' style='"+wh+" cursor:pointer;background:#CBE3F5; margin-left:3px; padding:5px;' onclick='existingImageBackground(this.src, "+data[x].id+", \""+type+"\")' />");
		  } 	 
     });
}
	
	
function from_bing(s){
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
	var kw = getKeyword();
	$("#image_gallery, #background_image_link").html('<img src="images/loading.gif" style="border:0px;" />');
	
	$.getJSON("edit_parked_template.php", {action:'loadBingPics',  keyword: kw, start:start},function(data){
		  if(null==data || data==''){
				$("#image_gallery").html('<span style="font-size:12px;">No more images. Please look backward.</span>');
				return false;  
		  }
		  $("#image_gallery").html('');
		  $("#bwd").html('<img onclick="from_bing(\'-\')" src="images/ar6.gif" style="cursor:pointer; border:0px; padding:6px 12px;" />');
		  $("#fwd").html('<img onclick="from_bing(\'+\')" src="images/ar5.gif" style="cursor:pointer; border:0px; padding:6px 12px;" />');
		  $.each(data, function(key, value) { 					
  			 $("#image_gallery").append("<img src='"+value+"' style='width:120px; height:160px; cursor:pointer;background:#CBE3F5; margin-left:3px; padding:5px;' onclick='set_image(this.src, \"background\", \""+kw+"\")' />");
			 $("#background_image_link").html('Show/Hide');
		  });		 
     });
}

function from_google(s){
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
	var kw = getKeyword();
	$("#image_gallery, #background_image_link").html('<img src="images/loading.gif" style="border:0px;" />');
	
	$.getJSON("edit_parked_template.php", {action:'loadGooglePics', keyword: kw, start:start},function(data){
		  if(null==data || data==''){
				$("#image_gallery").html('<span style="font-size:12px;">No more images. Please look backward.</span>');
				return false;  
		  }
		  $("#image_gallery").html('');
		  $("#bwd").html('<img onclick="from_google(\'-\')" src="images/ar6.gif" style="cursor:pointer; border:0px; padding:6px 12px;" />');
		  $("#fwd").html('<img onclick="from_google(\'+\')" src="images/ar5.gif" style="cursor:pointer; border:0px; padding:6px 12px;" />');
		  $.each(data, function(key, value) { 					
  				$("#image_gallery").append("<img src='"+value+"' style='width:120px; height:160px; cursor:pointer;background:#CBE3F5; margin-left:3px; padding:5px;' onclick='set_image(this.src, \"background\", \""+kw+"\")' />");
				$("#background_image_link").html('Show/Hide');
			});		 
     });
}

function upload(){
	var kw = getKeyword();
	var addStr = '<form id="upload_image" name="upload_image" action="classes/editImage.php" enctype="multipart/form-data" method="POST" style="color:red;"><input type="file" size="1" name="upimage" /><input type="hidden" name="kw" value="'+kw+'" ><input type="hidden" name="domain" value="'+domainurl+'" ><input type="hidden" name="action" value="upload_image" ><button >Go</button></form><br />* Only accept jpg/png/gif.<br /><br />';
	var addStr2 = "<SCRIPT>$(function(){ var options = {target:'#upload_image'};  $('#upload_image').submit(function(){$(this).ajaxSubmit(options);return false; }); }); <"+"/"+"SCRIPT>";
	$('#upload_image_area').append(addStr+addStr2);
	return false;
}

function set_image(path, loc, kw){
	var si = confirm('Do you want to set this image as '+loc+' image?');
	if(si){
		reloadIframe();
		$.get("edit_parked_template.php", {action:'setImage', path:path, location:loc, keyword: kw, domain:domainurl},function(data){				
				reloadURL();
				$("#image_gallery").html('');
				$("#bwd").html('');
				$("#fwd").html(''); 
     	});
	}
	return false;
}

function enableOpacity(){
	var val = $('#opacity').val();
	reloadIframe();	  	
	$.get("edit_parked_template.php", {action:'enableOpacity', value: val, domain:domainurl},function(data){
			reloadURL();		 
    });
}

function disableOpacity(){
	reloadIframe();
	$.get("edit_parked_template.php", {action:'disableOpacity', domain:domainurl},function(data){
			reloadURL();		  		 
    });
}

function clean_cache(){
	reloadIframe();	
	$.get("edit_parked_template.php", {action:'clean_cache'},function(data){
			reloadURL();		 
    });
}

function removeBackground(){		  
	reloadIframe();
	$.get("edit_parked_template.php", {action:'removeBackground', domain:domainurl},function(data){
			reloadURL();			 
    });
}

function existingImageBackground(path, id, type){
	var si = confirm('Do you want to set this image as background?');
	if(si){
		reloadIframe();
		$.get("edit_parked_template.php", {action:'existingImageBackground', css_id: id, domain:domainurl, type:type},function(data){				
				reloadURL();
				$("#image_gallery").html('');		  		 
     		});
	}
	return false;
}

function startTestCombination(){
	reloadIframe();
	$.getJSON("edit_parked_template.php", {action:'startTestCombination', domain:domainurl},function(data){			
			reloadURL();
			$.each(data, function(key, val) {
			if(key=='css_pending_id') css_pending_id = val; 
			if(key=='combination_amount') $('#leftover').html(val+' left ');
		});
    });
}

function generateCombination(){
	var cf = confirm('This button is for the one-off combination generation once the css_pending table being re-built. It could cause data damaged.  Are you sure to use it now? ');
	if(cf){
		reloadIframe();
		$.get("edit_parked_template.php", {action:'generateCombination', domain:domainurl},function(data){			
				$('#leftover').html(data);
		});	
	} 
	return false;
}

function themeSearch(seekstr){
	$.getJSON("edit_parked_template.php", {action:'themeSearch', domain:domainurl, seekstr:seekstr}, function(data){			
		var str = '';
		$.each(data, function(i, theme){			
			str+= '<li id="theme_'+theme['id']+'" ><span>'+theme['background']+'_'+theme['header']+'_'+theme['color']+'</span><b>'+theme['id']+'</b>'+theme['theme_name']+'</li>';
		});
		$('#color_themes_link').html(str);
		//-- bind the click event here again ---
		$('#theme li').click(function(){
			$('#theme li').removeClass('selected');
			$(this).addClass('selected');
			var i = $(this).children('b').text();
			setColorTheme(i);
			return false;
		});
	});		
}

function submitenter(myfield,e){
	var keycode;
	if(window.event) 
		keycode = window.event.keyCode;
	else if (e) 
		keycode = e.which;
	else 
		return true;
	
	if(keycode == 13){
	   var seekstr = $('#theme_search').val();
	   themeSearch(seekstr);
	   return false;
	}
	else
	   return true;
}



function getKeyword(){
	return $('#keyword').val();
}
</script>

<style>
#color_themes_link{
	background: none repeat scroll 0 0 #FCFCFC;
    border: 1px solid #DDDDDD;
    height: 255px;
    list-style: none outside none;
    margin: 0;
    overflow: auto;
    padding: 3px;
    width: 200px;
}
#color_themes_link span{width:80px; float:left; font-weight:bold;}
#color_themes_link li{
	line-height:22px; 
	cursor: pointer;
    padding: 1px 0; 
	display: list-item;
}
#color_themes_link>li>b{display:none}
.selected{background:#ccc}
#theme_search{background:#d9f2af; width:205px}
</style>
<div id="main_content">
	<table width="100%" border="0" cellspacing="0" cellpadding="0">
		<tr>

			<td class="brdrL">&nbsp;</td>
			<td valign="top" id="main" style="width:100%">
			
			
			<!-- *** START MAIN CONTENTS  *** -->
			
	<div>
         <span class="txtHdr" >Domain : <input type="text" id="domain" name="domain" size="60" max="250" value="<?php echo $url; ?>" onchange="window.location='edit_parked_template.php?domain='+this.value; " /></span>
         <br/>
         <span class="txtHdr" >Edit Template : <?php echo $layoutInfo['layout_name']; ?></span>
         
         <div id="arrow_bar" style="margin:auto; text-align:center;  margin-top:10px">
			<div ><table style="margin:auto; margin-top:12px"><tr><td id="bwd" style="border-bottom:0px;border-left:0px;"></td><td id="image_gallery" style="border-bottom:0px;border-left:0px;"></td><td id="fwd" style="border-bottom:0px;border-left:0px;"></td></tr></table>
            </div>
		 </div>
		 <br />

    </div>
<br>
<br>
<table align="center"  border="0" cellpadding="3" cellspacing="0" width="100%" id="boxGray">


		<tr>	
		<td align="center" valign="top" colspan="2">
			
		<table width="100%">
				<tr>
		<td class="greenHdr" width="20%"><button id="preview_button" onclick="switchTemplate();">Switch Index/Result page</button></td>
       
        <td class="greenHdr" align="left" width="80%">      			
        			
         </td>
	</tr>
			<tr>
			<td width="20%" valign="top"> 
            
             <iframe id="iFrame" name="iFrame" background-color="#ffffff" height="900" width="1200" src="http://<?php echo $url; ?>"></iframe>
                     
            </td>
            
			
            <td valign="top" >
            	 
            
                <h3><img id="tc_button1" src="images/down.png">Theme Setting</h3>
                <div id="theme">
                	  <button class="button" onclick="reset_default_template(); return false;" style="float:right; margin-top:0px">Reset</button><button class="button" onclick="auto_reset_template(); return false;" style="float:right; margin-top:0px">Random</button> 
                      <div class="etf">
                     	<div style="margin-left:25px;"><span><input type="text" id="theme_search" name="theme_search" value="search theme here" onclick="this.value=''; return false;" onblur="themeSearch(this.value); return false;" onKeyPress="return submitenter(this,event)"  /></span>
                       
                        <ul id="color_themes_link">
                        	 <?php						 
								while ($theme=$db->get_row($themes, 'MYSQL_ASSOC')){
									$selectedstr = '';
									if($domainInfo['domain_theme_id']==$theme['id'])
										$selectedstr = ' class="selected" ';
									echo   '<li id="theme_'.$theme['id'].'" '.$selectedstr.'><span>'.$theme['background'].'_'.$theme['header'].'_'.$theme['color'].'</span><b>'.$theme['id'].'</b>'.$theme['theme_name'].'</li>';
								}
							?>
                        </ul>
                        </div>
                     </div>
                </div>
                                  
                <br /><br />
                <h3><img id="tc_button2" src="images/down.png">Micro Adjustment</h3>
                <div id="edit_template">	
                	<button id="undo_button" onclick="undo(); return false;">Undo</button><button id="clean_cache_button" onclick="clean_cache(); return false;" style="float:right">Remove Cache</button>							
                    <br />
                    <fieldset>
    				<legend>Page Level</legend>
                        <div class="etf"><span>Website Background Color:</span><input type="text" name="background" id="background" value="333" /></div><br /><br />
                        <div class="etf"><span>Website Background Image:</span><button id="background_image_link">Show/Hide</button><button onclick="removeBackground(); return false;" id="remove_background">Remove Image</button></div>
                        <div class="etf" id="background_list"><span>&nbsp;</span>
                        	<ul>
                            <li>Search keyword: <input type="text" name="keyword" id="keyword" value="<?php echo $keyword; ?>" onclick="this.value=''" /></li>
                             <li><a href="#" onclick="from_database(0, 'background'); return false;">From PrincetonIT database</a></li>
                            <li><a href="#" onclick="from_bing(); return false;">More From Bing</a></li> 
                            <li id="upload_image_area"><a href="#" onclick="upload(); return false;">Upload Image</a></li></ul>
                        </div><br /><br />
                        
                        <div class="etf" ><span>Font Detail</span>
                       		<select onchange="setFontFamily(this.value); return false;" name="font_family" id="font_family"  style="width:60px">
                        	<option selected="selected" value="">Family</option>
                            <?php
							
                            while ($font=$db->get_row($fonts, 'MYSQL_ASSOC')){
								echo   '<option value="'.$font['css_id'].'">'.$font['description'].'</option>';
							}
							
							?>                           
                        </select>
                        <select onchange="setTextSize(this.value); return false;" name="font_size" id="font_size"  style="width:60px">
                        	<option selected="selected" value="">Size</option>
                            <option value="8">8</option>
                            <option value="9">9</option>
                            <option value="10">10</option>
                            <option value="11">11</option>
                            <option value="12">12</option>
                            <option value="13">13</option>
                            <option value="14">14</option>
                            <option value="15">15</option>
                        </select>
                        <input type="text" name="text_color" id="text_color" value="color"  style="width:60px"/>
                        </div><br /><br />
                    </fieldset><br />
                    
                    <fieldset>
    				<legend>Header&Footer</legend>
                    	<div class="etf"><span>Header Image:</span><a href="#" onclick="from_database(0, 'header_background_image'); return false;">From PrincetonIT database</a></div><br />
                        <div class="etf"><span>Header Height:</span><input type="text" name="header_height" id="header_height" value="200" />px<input type="submit"  style="margin-left:30px"  onclick="updateHeaderHeight(); return false;" value="Save" /></div><br />
                		<div class="etf"><span>Header Background Color:</span><input type="text" name="header_background_color" id="header_background_color" value="color" /></div><br />
                        <div class="etf"><span>Header Text Color:</span><input type="text" name="header_text_color" id="header_text_color" value="color" /></div><br />
                        <div class="etf"><span>Header Menu Background Color:</span><input type="text" name="menu_background_color" id="menu_background_color" value="color" /></div><br />
                        <div class="etf"><span>Header Menu Text:</span><input type="text" name="menu_color" id="menu_color" value="color" style="width:40px" />
                        		<select onchange="setMenuTextSize(this.value); return false;" name="menu_font_size" id="menu_font_size" style="width:80px">
                                    <option selected="selected" value="">font size</option>
                                    <option value="10">10</option>
                                    <option value="11">11</option>
                                    <option value="12">12</option>
                                    <option value="13">13</option>
                                    <option value="14">14</option>
                                    <option value="15">15</option>
                                </select> 
                                Bold<input type="checkbox" name="menu_text_bold" id="menu_text_bold" onchange="setMenuTextBold(this.value); return false;" value="bold" />
                        </div><br /><br />
                        
                        <div class="etf"><span>Logo Background Color:</span><input type="text" name="logo_background_color" id="logo_background_color" value="color" /></div><br />
                        <div class="etf"><span>Logo Text Color:</span><input type="text" name="logo_color" id="logo_color" value="color" /></div><br /><br />
                        
                        <div class="etf"><span>Footer Background Color:</span><input type="text" name="footer_background_color" id="footer_background_color" value="color" /></div><br />
                        <div class="etf"><span>Footer Text Color:</span><input type="text" name="footer_text_color" id="footer_text_color" value="color" /></div><br /><br />
                    </fieldset><br />
                                     
                 
                    <fieldset>
    				<legend>Side Blocks</legend>
                		<div class="etf"><span>Left Block Background Color:</span><input type="text" name="left_sidebar_background_color" id="left_sidebar_background_color" value="color" /></div><br />
                        <div class="etf"><span>Left Block Font:</span><input type="text" name="left_sidebar_text_color" id="left_sidebar_text_color"  value="color"  style="width:40px"  />
                        		<select onchange="setLeftSize(this.value); return false;" name="left_font_size" id="left_font_size" style="width:80px">
                                    <option selected="selected" value="">font size</option>
                                    <option value="10">10</option>
                                    <option value="11">11</option>
                                    <option value="12">12</option>
                                    <option value="13">13</option>
                                    <option value="14">14</option>
                                    <option value="15">15</option>
                                </select> 
                                Bold<input type="checkbox" name="left_text_bold" id="left_text_bold" onchange="setLeftTextBold(this.value); return false;" value="bold" />
                        </div><br /><br />
                        
                        <div class="etf"><span>Right Block Background Color:</span><input type="text" name="right_sidebar_background_color" id="right_sidebar_background_color" value="color" /></div><br />
                        <div class="etf"><span>Right Block Font:</span><input type="text" name="right_sidebar_text_color" id="right_sidebar_text_color" value="color"  style="width:40px" />
                        		<select onchange="setRightSize(this.value); return false;" name="right_font_size" id="right_font_size" style="width:80px">
                                    <option selected="selected" value="">font size</option>
                                    <option value="10">10</option>
                                    <option value="11">11</option>
                                    <option value="12">12</option>
                                    <option value="13">13</option>
                                    <option value="14">14</option>
                                    <option value="15">15</option>
                                </select> 
                                Bold<input type="checkbox" name="right_text_bold" id="right_text_bold" onchange="setRightTextBold(this.value); return false;" value="bold" />
                        </div><br /><br />
                    </fieldset><br />
                    
                   
                    
                     <fieldset>
    				<legend>Sponsor Link</legend>
                        <div class="etf"><span>Title:</span><input type="text" name="sponsor_title_color" id="sponsor_title_color" value="color"   style="width:60px" />
                        	<select onchange="setSponsorSize('sponsor_title_size', this.value); return false;" name="sponsor_title_size" id="sponsor_title_size"   style="width:60px">
                        	<option selected="selected" value="">font size</option>                       
                            <option value="12">12</option>
                            <option value="13">13</option>
                            <option value="14">14</option>
                            <option value="15">15</option>
                            <option value="16">16</option>
                            <option value="17">17</option>
                            <option value="18">18</option>
                        </select>
                        </div><br />
                        
                        <div class="etf"><span>Description:</span><input type="text" name="sponsor_description_color" id="sponsor_description_color" value="color"   style="width:60px" />
                        	<select onchange="setSponsorSize('sponsor_description_size', this.value); return false;" name="sponsor_description_size" id="sponsor_description_size"   style="width:60px">
                        	<option selected="selected" value="">font size</option>                       
                            <option value="8">8</option>
                            <option value="9">9</option>
                            <option value="10">10</option>
                            <option value="11">11</option>
                            <option value="12">12</option>
                            <option value="13">13</option>
                            <option value="14">14</option>                          
                        </select>
                        </div><br />
                        
                        <div class="etf"><span>Bottom Url:</span><input type="text" name="sponsor_url_color" id="sponsor_url_color" value="color"   style="width:60px" />
                        	<select onchange="setSponsorSize('sponsor_url_size', this.value); return false;" name="sponsor_url_size" id="sponsor_url_size"   style="width:60px">
                        	<option selected="selected" value="">font size</option>                       
                            <option value="8">8</option>
                            <option value="9">9</option>
                            <option value="10">10</option>
                            <option value="11">11</option>
                            <option value="12">12</option>
                            <option value="13">13</option>
                            <option value="14">14</option>  
                        </select>
                        </div>
                    </fieldset><br />
                    
                     <fieldset>
    				<legend>Special Effects</legend>
                		<div class="etf"><span>Opacity/Transparency:</span>
                            <select name="opacity" id="opacity">
                            	<option value="0.1">0.1</option>
                                <option value="0.2">0.2</option>
                                <option value="0.3">0.3</option>
                                <option value="0.4">0.4</option>
                                <option value="0.5">0.5</option>
                                <option value="0.6">0.6</option>
                                <option value="0.7">0.7</option>
                                <option value="0.8">0.8</option>
                                <option value="0.9">0.9</option>
                            </select>
                            <input type="submit" onclick="enableOpacity(); return false;" value="Enable" /><input type="submit" onclick="disableOpacity(); return false;" value="Disable" />
                        </div><br /><br />
                        <div class="etf"><span> xxx :</span> ... </div>
                    </fieldset><br />
                    
                </div>                 
                <br /> 

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



<?php
require_once('footer.php');
?>
