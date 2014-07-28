/*
*  Manage Drag and drop file upload
*  required: css/drag_drop.css 
*
*  Include division :
*  <div id="output" class="dropzone" type="image_files" accept="gif|jpg|png|bmp" request="custom_layout.php?action=upload&layout_id=<?php echo $layoutInfo['layout_id'];?>">
*		<ul id="output-listing"></ul>
*	</div>	 
*
*  type:  images_files, other_files or blank
*  accept: type of files to accept used in validation
*  request: post request to use for file upload
*/

		var TCNDDU = TCNDDU || {};
	    $(document).ready(function() {
	    		jQuery.event.props.push('dataTransfer');  // required to have the event dataTransfer property
	    		
    			$(".dropzone").each(function(){
    				    var dropzone = $(this);
    				    
    				    dropzone.bind({
    				    	afterUpload: function(event){
    				    		var afterUpload = dropzone.attr('afterUpload');
    				    		if(afterUpload) 
    				    			$("#"+afterUpload).trigger('ondblclick');
    				    	},
    						dragenter : function(event){
    				    	dropzone.find("#output-listing").html(''); event.stopPropagation(); event.preventDefault();
    						},
    						dragover : false,
    					    drop : function (event) {
    							event.stopPropagation();
    							event.preventDefault();
    							jQuery.each( event.dataTransfer.files, function(index, file){
    						        var fileReader = new FileReader();
    					            fileReader.onload = (function (file, index) {
    					    			var getBinaryDataReader = new FileReader(),
    					    			type = dropzone.attr('type');
    					    			request = dropzone.attr('request');
    					    			if (type == 'image_files') 
    						    			dropzone.find("#output-listing").append('<li id="item'+type+index+'"><a><img id="img-'+type+index+'" src="../images/editIcon.jpg" alt="'+file.name+'" width="110" height="100"></a></li>');
    					    			else
    						    			dropzone.find("#output-listing").append('<li id="item'+type+index+'"><a><img id="img-'+type+index+'" src="../images/editIcon.jpg" alt="'+file.name+'" width="110" height="100"></a></li>');
    					    				
    					    			getBinaryDataReader.addEventListener("loadend", function(evt){TCNDDU.processXHR(file, index, evt.target.result, request, type, dropzone.attr('accept'));}, false);
    					    			getBinaryDataReader.readAsBinaryString(file);
    					            })(file, index);
    					            fileReader.addEventListener("loadend", function(evt){if (dropzone.attr('type') == 'image_files') $('#img-'+dropzone.attr('type')+index).attr("src", evt.target.result); }, false); 
    						        fileReader.readAsDataURL(file);
    						    });
    						}
    					});
    			});
	    });
	    
		TCNDDU.processXHR = function (file, index, bin, request, type, accept) {
			var xhr = new XMLHttpRequest(),
				fileUpload = xhr.upload;

			// validate filetype
			var filetype = file.name.substring((file.name.lastIndexOf('.') == -1) ? file.name.length : file.name.lastIndexOf('.')+1, file.name.length);
			filetype = filetype.toLowerCase();
			if (accept) 
				accept = accept.toLowerCase();
			if (accept && ((filetype.length == 0) || (accept.indexOf(filetype) == -1)))
			{
				$("#item"+type+index).append('<p>File type not allow:'+filetype+'</p>');
				return;
			}
			
			$("#item"+type+index).append('<div class="progressBar"><p id="loaderIndicator'+type+index+'">0%</p></div>');
			
			fileUpload.log = $("#item"+type+index);
			
			fileUpload.addEventListener("progress", function(event) {
				if (event.lengthComputable) {
					var percentage = Math.round((event.loaded * 100) / event.total);
					if (percentage <= 100) {
						$("#loaderIndicator"+type+index).css ('width', percentage + "px");
						$("#loaderIndicator"+type+index).text(percentage + "%");
					}
				}
			}, false);
			
			fileUpload.addEventListener("load", function(event) {
				$("#item"+type+index).attr('class' , "loaded");
				$("#loaderIndicator"+type+index).css ('width', "100px");
				$("#item"+type+index).append('<p>'+file.name+'</p>');
				console.log("xhr upload of "+type+index+" complete");
			}, false);
			
			fileUpload.addEventListener("error", function (error) {console.log("error: " + error.code); fileUpload.log.append('<p>Error:'+error.code+'</p>');}, false);

			/* Build RFC2388 string. */
			var boundary = '------multipartformboundary' + (new Date).getTime();
			var dashdash = '--';
		    var crlf     = '\r\n';
		    var builder = '';
		    builder += dashdash;
		    builder += boundary;
		    builder += crlf;
		    
			if (!type)
				type = 'user_file';
			
			/* Send the request */
	        var formData = new FormData();
	        formData.append(type+'[]', file);
	        
			xhr.open("POST", request);
			xhr.send(formData);
			xhr.onload = function(event) { 
		        /* If we got an error display it. */
		        if (xhr.responseText) 
		        	$("#loaderIndicator"+type+index).text(xhr.responseText);
		        else
		        	$("#loaderIndicator"+type+index).text("Loaded");	   
		        $(".dropzone").trigger('afterUpload');
		    };
		};
		

