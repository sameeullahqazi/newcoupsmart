document.write('<img id="trigger_tracking_pixel" style="width:1px; height:1px; visibility: hidden;" />');
function triggerURL(trigger_url)
{
	console.log("trigger_url in triggerURL(): " + trigger_url);
	if(trigger_url != '')
	{
		$('#trigger_tracking_pixel').attr("src", trigger_url);
	}
}
