
// Draw Blank Tool Tip
document.write("<div id='tooltip' style='position: absolute; border: 1px solid; background-color: #FFFFFF; visibility: hidden; padding: 5px; z-index: 1;'></div>");	

// Show Tool Tip
//rpeters adjusting the tool tip position to account for being offstage right

function showToolTip(contents, event){
	
	var xOffset = 10;
	var cursor = getCurPosition(event);
	//windows current width 
	if(document.all)
	{
		var winWidth = document.body.offsetWidth;
	}
	else
	{
		var winWidth = window.innerWidth;
	}
	//or ie
	//add content to the tooltip so the browser can calculate the client area
	document.getElementById('tooltip').innerHTML = contents;
	//grab that client area
	var elWidth = document.getElementById('tooltip').clientWidth;
	var calcPosition = (cursor.x+xOffset) + elWidth;
	
	//will we be offscreen???
	if(calcPosition >= winWidth)
	{
		//fix the cursor.x by using the elWidth
		cursor.x -= (elWidth);
	}
	
	document.getElementById('tooltip').style.left = (cursor.x +xOffset ) +'px';
	document.getElementById('tooltip').style.top = (cursor.y +10 ) +'px';
	document.getElementById('tooltip').style.visibility = "visible";
		
	return;
}

// Hide Tool Tip
function hideToolTip(){

	document.getElementById('tooltip').innerHTML = "";
	document.getElementById('tooltip').style.visibility = "hidden";
	return;
	
}


//gets position of cursor
function getCurPosition(e) 
{
    e = e || window.event;
    var cursor = {x:0, y:0};
    if (e.pageX || e.pageY) {
        cursor.x = e.pageX;
        cursor.y = e.pageY;
    } 
    else {
        var de = document.documentElement;
        var b = document.body;
        cursor.x = e.clientX + 
            (de.scrollLeft || b.scrollLeft) - (de.clientLeft || 0);
        cursor.y = e.clientY + 
            (de.scrollTop || b.scrollTop) - (de.clientTop || 0);
    }
    return cursor;
}

