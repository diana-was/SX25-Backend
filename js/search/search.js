function createRequestObject() {
    var ro;
    var browser = navigator.appName;
    if(browser == "Microsoft Internet Explorer"){
        ro = new ActiveXObject("Microsoft.XMLHTTP");
    }else{
        ro = new XMLHttpRequest();
    }
    return ro;
}
var http = createRequestObject();
function sndReq(action) {
    http.open('get', action);
    http.onreadystatechange = handleResponse;
    http.send(null);
}
function handleResponse() {
	if(http.readyState == 1)
	{
		document.getElementById('searchResultsDiv').innerHTML = '<div id="search_results"><ul id="article_list" class="search_results_list"><li class="searchHeader"><a>Result</a></li><li class="searchResultRow" style="text-align:center"><img src="/js/search/loader.gif"></li></ul></div>';
	}
    else if(http.readyState == 4){
		document.getElementById('searchResultsDiv').innerHTML = http.responseText;
    }
}
function doSearch() {
	var val = '/' + document.getElementById('engine').value + '/' + document.getElementById('act').value + '/'  + document.getElementById('domain').value + '/' + document.getElementById('q').value + '/' + document.getElementById('type').value + '/' + document.getElementById('used').value; 
	sndReq(val);
	return false;
}
function doSearchPage() {
	var val = '/' + document.getElementById('engine').value + '/' + document.getElementById('act').value + '/'  + document.getElementById('domain').value + '/' + document.getElementById('type').value; 
	sndReq(val);
	return false;
}
function openWindow(url) {
  window.open(url,'popupWindow','resizable=no,scrollbars=yes,toolbar=no,status=no,height=650,width=720');
}
function expandOrClose(arrow_id, list_num, list_name)  {
	var a = document.getElementById(arrow_id);
	if(a.name=='d') { 
		a.src='/js/search/arrowRight.gif';
		a.name='r';
		hideSearchTypes(list_num,list_name);
	}
	else {
		a.src='/js/search/arrowDown.gif';
		a.name='d';
		showSearchTypes(list_num,list_name);
	}
	return false;
}
function hideSearchTypes (i,j) { 
	var theDiv = document.getElementById(j).getElementsByTagName('li');
	for(var d = 1; d < theDiv.length; d++) {
		var theID = 'res-'+i+'-'+d;
		Effect.DropOut(theID);
	}
}	
function showSearchTypes (i,j) { 
	var theDiv = document.getElementById(j).getElementsByTagName('li');
	for(var d = 1; d < theDiv.length; d++) {
		var theID = 'res-'+i+'-'+d;
		
		Effect.Appear(theID);
	}
}