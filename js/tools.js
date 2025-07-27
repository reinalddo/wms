function base_url(){var url = window.location.href; var arr = url.split("/"); return arr[0] + "//" + arr[2];}
function core_get(url,data){$.ajax({type:"POST",url:url,data:data,success:core_response,dataType:"json"});}
function core_response(data){if(typeof data.data!='undefined'){$.each(data.data,function(index,value){window[index]=value;});}if(typeof data.exec!='undefined'){core_execute(data.exec);}}
function core_execute(exec){for(var k in exec){eval(exec[k]);}}
$.fn.gform = function() { var o = {}; var a = this.serializeArray(); $.each(a, function() {if (o[this.name] !== undefined) {if (!o[this.name].push) {o[this.name] = [o[this.name]];} o[this.name].push(this.value || ''); } else { o[this.name] = this.value || ''; } }); return o; };
$.fn.reset=function(){$(this).each(function(){this.reset()})}

