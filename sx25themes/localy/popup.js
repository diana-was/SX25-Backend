var popupStatus = 0;  

$(document).ready(function(){  
   
	$("#popupContactClose").click(function(){  
		disablePopup();  
	});  
 
	$("#backgroundPopup").click(function(){  
		disablePopup();  
	});  

	$(document).keypress(function(e){  
		if(e.keyCode==27 && popupStatus==1){
			disablePopup();
		} 
	});  
	
}); 

function show_advice(){
    $('#adviceTitle').text('Get advice');
    $('#adviceSubTitle').text('Give your word here:');
    centerPopup();  
    loadPopup(); 
}

function show_advice_expert(){
    $('#adviceTitle').text('Become an expert');
    $('#adviceSubTitle').text('Please tell us your detail:');
    centerPopup();  
    loadPopup(); 
}
 

function loadPopup(){  
//loads popup only if it is disabled  
	if(popupStatus==0){  
		$("#backgroundPopup").css({  
			"opacity": "0.7"  
		});  
		$("#backgroundPopup").fadeIn("slow");  
		$("#popupContact").fadeIn("slow");  
		popupStatus = 1;  
	}  
}  

function disablePopup(){  
//disables popup only if it is enabled  
	if(popupStatus==1){  
		$("#backgroundPopup").fadeOut("slow");  
		$("#popupContact").fadeOut("slow");  
		popupStatus = 0;  
	}  
}  

function centerPopup(){  
//request data for centering  
	var windowWidth = document.documentElement.clientWidth;  
	var windowHeight = document.documentElement.clientHeight;  
	var popupHeight = $("#popupContact").height();  
	var popupWidth = $("#popupContact").width();  
	//centering  
	$("#popupContact").css({  
		"position": "absolute",  
		"top": windowHeight/2-popupHeight/2,  
		"left": windowWidth/2-popupWidth/2  
	});  
	//only need force for IE6  
	  
	$("#backgroundPopup").css({  
		"height": windowHeight  
	});  
  
}  
