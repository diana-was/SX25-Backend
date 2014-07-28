function clearMe(){

document.getElementById("s").value = "";
}
function toggleLayer( whichLayer )
{
  var elem, vis;
  if( document.getElementById ) 
    elem = document.getElementById( whichLayer );
  else if( document.all ) 
      elem = document.all[whichLayer];
  else if( document.layers ) 
    elem = document.layers[whichLayer];
  vis = elem.style;

  if(vis.display==''&&elem.offsetWidth!=undefined&&elem.offsetHeight!=undefined)    vis.display = (elem.offsetWidth!=0&&elem.offsetHeight!=0)?'block':'none';  vis.display = (vis.display==''||vis.display=='block')?'none':'block';
}

function HideThisLayer ( whichLayer ){

  var elem, vis;
  if( document.getElementById ) 
    elem = document.getElementById( whichLayer );
  else if( document.all ) 
      elem = document.all[whichLayer];
  else if( document.layers ) 
    elem = document.layers[whichLayer];
  vis = elem.style;
  vis.display = "none";
}



		function CheckCallBackForm()
		{
 
			var title = document.getElementById("title");
			var short = document.getElementById("short"); 
 

			/*if(email.value.indexOf('@') == -1 || email.value.indexOf('.') == -1)
			{
				alert('Please enter a valid email address.');
				email.focus();
				email.select();				 
				return false;
			}*/

			if(title.value == '')
			{
				alert('Please enter a valid title for your listing.');
				title.focus();
				return false;
			}
			if(short.value == '')
			{
				alert('Please enter a valid short description');
				short.focus();
				return false;
			}
 					

			toggleLayer('formbox');
			toggleLayer('loadingbox');
			// Everything is OK
			return true;
		}
 