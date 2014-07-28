function pre_search(){
    s = $('#s_bg_blue').val();
    search();
}

function articles(){
    $.getJSON('http://webezines.net/article/getarticle.php?keyword='+s+'&domain={DOMAIN}&showtype=11&format=json&callback=?', function(data11){       
        if(null!=data11 && data11!=''){
		   var count=1;	
                   for(x in data11){ 			
			var ahtml = '<div class="cat1_img"><a href="#" onclick="oneArticle('+data11[x].id+'); return false;"><img src="{RESULTS_IMG}" height="100px" /></a></div><h3>'+data11[x].title+'</h3><p>'+data11[x].summary+' <a href="/result.php?Keywords='+data11[x].keyword+'" class="readmore"> [Read More] </a></p>';
                	 $('#article_'+count).html(ahtml);
				  count++;
		} 
                var h2 = $('#article_2').html(); 
                var h3 = $('#article_3').html(); 
                if(h2=='')  $('#article_2').html(ahtml);
                if(h3=='')  $('#article_3').html(ahtml);
	}
   });
}


function savecomment()
{
	 var author=$('#author').val();
	 var title=$('#title').val();
	 var comment=$('#comment_content').val();
	 if(author==''||title==''||comment==''){
		 $('#reminder').html('Please fill all required information in form.');
	 }else{
                var fields = $('#commentform :input').serialize();
		$.getJSON('http://webezines.net/article/comment_from_parked.php?format=json&callback=?', fields, function(data){
			window.location = '{TITLE_LINK}/result.php?Keywords={TITLE}';

		});
	 }
}
	

function getfullarticle() {
    $('#full_article2').html(full); 
    $('#morebutton').hide();      
}


function contactus()
{
	 var name=$('#mname').val();
	 var email=$('#memail').val();
	 var subject=$('#msubject').val();
	 var message=$('#mmessage').val();
	 if(name==''||email==''||subject==''||message==''){
		 $('#reminder').html('Please fill all required information in form.');
	 }else{
	 	 var fields = $('#contactform :input').serialize();
		$.getJSON('http://webezines.net/article/comment_from_parked.php?format=json&callback=?', fields, function(data){
			window.location = '{TITLE_LINK}/result.php?Keywords={KEYWORDS}';
		});
	 }
}