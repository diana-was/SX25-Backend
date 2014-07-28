function fieldSelected(){
	var fields = document.getElementById('fields');
	var option = fields.getElementsByTagName('option');
	this.selected = false;
	if (option[0].selected != true){
		option[0].selected = true;
		this.selected = true;
	}
}