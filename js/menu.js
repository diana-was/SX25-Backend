
//$Id: menu.js,v 1.1 2007/03/22 21:31:18 cburck Exp $
//
// menu functions
//

function showit(id)
{
	var menuobj = document.getElementById("describe");
	var thecontent = (id == -1)?("&nbsp;"):(submenu[id]);
		
	if (document.getElementById || document.all)
	{
		menuobj.innerHTML = thecontent;
	}
	else if (document.layers)
	{
		menuobj.document.write(thecontent);
		menuobj.document.close();
	}
}

	
function mainOut(id)
{
	if (typeof(mainSelectTimeout) != 'undefined')
		clearTimeout(mainSelectTimeout);
			
	if (typeof(resetTimeout) != 'undefined')
		clearTimeout(resetTimeout);
    
	if (typeof(id) == 'undefined')
		id = -1;
			
	resetTimeout = setTimeout("showit(" + id + ");", 2750);
	return false;
}


function mainOver(id)
{
	if (typeof(resetTimeout) != 'undefined')
		clearTimeout(resetTimeout);
							
	if (typeof(mainSelectTimeout) != 'undefined') 
		clearTimeout(mainSelectTimeout);
		
	if (typeof(id) == 'undefined')
		id = -1;
    							
	mainSelectTimeout = setTimeout("showit(" + id + ");", 225);
	return false;
}


function subOut(id)
{
	if (typeof(resetTimeout) != 'undefined')
		clearTimeout(resetTimeout);
		
	if (typeof(id) == 'undefined')
		id = -1;
			
	resetTimeout = setTimeout("showit(" + id + ");", 2750);
	return false;
}


function subOver()
{
	if (typeof(resetTimeout) != 'undefined')
		clearTimeout(resetTimeout);
			
	return false;
}
	

function clearAll()
{
	if (typeof(resetTimeout) != 'undefined')
		clearTimeout(resetTimeout);
			
	if (typeof(mainSelectTimeout) != 'undefined') 
		clearTimeout(mainSelectTimeout);
}
