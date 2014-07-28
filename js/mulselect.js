var request = false;
/*@cc_on @*/
/*@if (@_jscript_version >= 5)
try {
request = new ActiveXObject("Msxml2.XMLHTTP");
} catch (e) {
try {
request = new ActiveXObject("Microsoft.XMLHTTP");
} catch (e2) {
request = false;
}
}
@end @*/
if (!request && typeof XMLHttpRequest != 'undefined') {
request = new XMLHttpRequest();
}

function fillSelect(profile_id) {
var url = "p2a_ajax.php?profile_id=" + escape(profile_id);

request.open("GET", url, true);
request.onreadystatechange = go;
request.send(null);
}

function go() {
if (request.readyState == 4) {
if (request.status == 200) {
var response = request.responseText;
var list=document.getElementById("account_id");
var cities=response.split('|');
var x=document.createElement('option');
   var y=document.createTextNode('Please Select');
   x.appendChild(y);
   x.value = '';
   list.appendChild(x);
for (i=1; i<cities.length; i++) {
	var citiesdetail=cities[i].split('#');
   var x=document.createElement('option');
   var y=document.createTextNode(citiesdetail[1]);
   x.appendChild(y);
   x.value = citiesdetail[0];
   list.appendChild(x);
   }
  }
 }
}

function initCs() {
var profile_id=document.getElementById('profile_id');
profile_id.onchange=function() {
 if(this.value!="") {
  var list=document.getElementById("account_id");
  while (list.childNodes[0]) {
list.removeChild(list.childNodes[0])
}
  fillSelect(this.value);
  }
 }
 fillSelect(profile_id.value);
}