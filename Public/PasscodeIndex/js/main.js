(function(){
	$(".right-btn").click(function(){
		postVote($(this).data("id"),"yes");
	})

	$(".cross-btn").click(function(){
		postVote($(this).data("id"),"no");
		
	})
	
	function postVote(id,option){
		$.post(voteAPI,{"v":option,"id":id},function(data){
			result=JSON.parse(data);
			if(result.code==1){
				layer.msg(result.message, {icon: 6});
			}else{
				layer.msg(result.message, {icon: 5});
			}
			
		})
	}
})()