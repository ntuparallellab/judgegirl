document.write("<script type='text/javascript' src='js/jquery.js'></scr"+"ipt>");
document.write("<script type='text/javascript' src='js/jquery.flot.js'></scr"+"ipt>");

function draw_diagram(data, id){
    var d = [];
    var l = data.length;
    for(var i = 0; i<l; i++)
	if(data[i] > 0)
	    d.push([i-0.5,data[i]]);
    $.plot($("#"+id), [{
	data: d,
	bars: {show:true, steps:true, fill:true}
    }]);
}
