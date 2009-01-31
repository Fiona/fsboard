$(document).ready(function()
{

	$(".logged_in_as_msg").click(function()
	{
	
		$(".logo_header img").slideToggle();
		return false;
	
	});


	// Debug information
	$("div.debug_level_2_wrapper a[rel=explain]").click(
		function()
		{
			$(this).next("table.explain_table").slideToggle();
			return false;
		}
	);
	$("div.debug_level_2_wrapper table.explain_table").hide();

});