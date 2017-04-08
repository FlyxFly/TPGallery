var Crawler=require("crawler");
var url=require("url");

var c=new Crawler({
	maxConnections:1,
	rateLimit:2000,
	callback:function(err,res,done){
		if(err){
			console.log(err);
		}else{
			var $=res.$;
			console.log($(".item").text())
		};
	}
})

c.queue({
	uri:'http://www.staxus.com',
	proxy:'http://127.0.0.1:19891'
});
