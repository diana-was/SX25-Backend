<?php
require_once('config.php');

if(isset($_REQUEST['action']) && $_REQUEST['action']=='crop_image'){	
		$cropStartX = $_REQUEST['left'];
		$cropStartY = $_REQUEST['top'];
		$cropW = $_REQUEST['width'];
		$cropH = $_REQUEST['height'];
		$imgfile = $config->imageLibraryPath.$_REQUEST['image_name'];
		$format = substr($imgfile, -3, 3);
		switch ($format)
		{
			case 'jpg' :
				// Create two images
				$origimg = imagecreatefromjpeg($imgfile);
				$cropimg = imagecreatetruecolor($cropW,$cropH);
				
				// Get the original size
				list($width, $height) = getimagesize($imgfile);
				
				// Crop
				//@chmod($c_imgfile,0777);
				@chmod($imgfile,0777); 
				imagecopyresized($cropimg, $origimg, 0, 0, $cropStartX, $cropStartY, $width, $height, $width, $height);	
				unlink($imgfile);
				imagejpeg($cropimg,$imgfile);
				break;
			case 'png':
				// Create two images
				$origimg = imagecreatefrompng($imgfile);
				$cropimg = imagecreatetruecolor($cropW,$cropH);
				
				// Get the original size
				list($width, $height) = getimagesize($imgfile);
				
				// Crop
				//@chmod($c_imgfile,0777);
				@chmod($imgfile,0777); 
				imagecopyresized($cropimg, $origimg, 0, 0, $cropStartX, $cropStartY, $width, $height, $width, $height);	
				unlink($imgfile);
				imagepng($cropimg,$imgfile);
				break;
			case 'gif' :
				// Create two images
				$origimg = imagecreatefromgif($imgfile);
				$cropimg = imagecreatetruecolor($cropW,$cropH);
				
				// Get the original size
				list($width, $height) = getimagesize($imgfile);
				
				// Crop
				//@chmod($c_imgfile,0777);
				@chmod($imgfile,0777); 
				imagecopyresized($cropimg, $origimg, 0, 0, $cropStartX, $cropStartY, $width, $height, $width, $height);	
				unlink($imgfile);
				imagegif($cropimg,$imgfile);
				break;
			default : 
				echo "Image Format can't be processed!";
				exit();
				break;
		}
		echo "Image has been saved. Clean browser cache and reload the page if you can't see the new image.";
		exit();
}

?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="fr" lang="fr">
<head>
		<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
		<title>Cropper</title>

		<link rel="stylesheet" type="text/css" href="http://yui.yahooapis.com/2.7.0/build/resize/assets/skins/sam/resize.css" />
		<link rel="stylesheet" type="text/css" href="http://yui.yahooapis.com/2.7.0/build/fonts/fonts-min.css" />
		<link rel="stylesheet" type="text/css" href="http://yui.yahooapis.com/2.7.0/build/button/assets/skins/sam/button.css" />
		<link rel="stylesheet" type="text/css" href="http://yui.yahooapis.com/2.7.0/build/imagecropper/assets/skins/sam/imagecropper.css" />
		
		<script type="text/javascript" src="http://yui.yahooapis.com/2.7.0/build/yahoo-dom-event/yahoo-dom-event.js"></script>
		<script type="text/javascript" src="http://yui.yahooapis.com/2.7.0/build/connection/connection-min.js"></script>
		
		<script type="text/javascript" src="http://yui.yahooapis.com/2.7.0/build/element/element-min.js"></script>
		<script type="text/javascript" src="http://yui.yahooapis.com/2.7.0/build/button/button-min.js"></script>
		<script type="text/javascript" src="http://yui.yahooapis.com/2.7.0/build/dragdrop/dragdrop-min.js"></script>
		<script type="text/javascript" src="http://yui.yahooapis.com/2.7.0/build/resize/resize-min.js"></script>
		<script type="text/javascript" src="http://yui.yahooapis.com/2.7.0/build/imagecropper/imagecropper-min.js"></script>
		<script type="text/javascript" src="http://ajax.googleapis.com/ajax/libs/jquery/1.3.1/jquery.min.js"></script>
		<script type="text/javascript" src="js/blockUI.js"></script>

<style>
#main_content{
margin:auto;
text-align:center;	
}
.yui-crop {
margin:auto;
}
</style>

</head>
<body class="yui-skin-sam">

<div id="main_content">

<h3>Image Name: <?php echo $_REQUEST['image_name']; ?></h3>
<div id="results"><?php if(isset($_REQUEST['sucMsg'])) echo '<font color="Red">'.$_REQUEST['sucMsg'].'</font>'; ?></div>
<br />
<div><img id="rawimg" src="<?php echo $config->imageLibrary.$_REQUEST['image_name'].'?'.strtotime('now'); ?>" alt="" /></div>
<br /><br />
<div id="button1" ></div>
<br />
<div id="closeButton" ><div/> 


<script>
var image_name = '<?php echo $_REQUEST['image_name']; ?>';

(function() {
    var Dom = YAHOO.util.Dom,
        Event = YAHOO.util.Event,
        conn = null,
        results = null;

    Event.onDOMReady(function() {
        results = Dom.get('results');
        var callback = {
            success: function(o) {
                var json = o.responseText;            
                results.innerHTML = '<p><strong>' + json + '</strong></p>';
            },
            failure: function() {
                results.innerHTML = '<p><strong>An error occurred, please try again later.</strong></p>';
            }
        };
        var crop = new YAHOO.widget.ImageCropper('rawimg', {
			minHeight: 70,
			minWidth: 70,
			initHeight: 100,
			initWidth: 100,
			ratio: true
        });

        var _button = new YAHOO.widget.Button({
            id: 'cropIt',
            container: 'button1',
            label: 'Crop Image',
            value: 'crop'
        });

        _button.on('click', function() {
            var coords = crop.getCropCoords();
            //var url = 'edit_image.php?action=crop_image&image_name='+encodeURIComponent(image_name)+'&top=' + coords.top + '&left=' + coords.left + '&height=' + coords.height + '&width=' + coords.width;
            //alert(url);//conn = YAHOO.util.Connect.asyncRequest('GET', url, callback);

        	$.blockUI({ message: '<h1><img src="images/loading.gif" /> Just a moment...</h1>' }); 	
        	$.get("edit_image.php", {action:'crop_image', image_name:encodeURIComponent(image_name), top:coords.top, left:coords.left, height:coords.height, width:coords.width},function(data){			
        			$.unblockUI();
        			var message = data;
        			var url = 'edit_image.php?image_name='+encodeURIComponent(image_name)+'&sucMsg='+message;
        			window.location.href = url;
            });
        });
        
        var _closeButton = new YAHOO.widget.Button({
            id: 'closeIt',
            container: 'closeButton',
            label: 'Close',
            value: ''
        });

        _closeButton.on('click', function() {
            // reload the opener or the parent window
            if (window.opener)
	            window.opener.location.reload();
            // then close this pop-up window
            window.close();
        });
   });
})();

</script>
</div>
</body>
</html>
