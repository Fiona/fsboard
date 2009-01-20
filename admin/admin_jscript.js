/*
 ----------------------------
 FSBoard Javascript ...
 Stuff
 ----------------------------
*/



// Opens a new window for the admin help stuff
/*
REPLACE MEEEE
function open_admin_area_help(page, action, field)
{

        window.open(
                'index.php?m=help&page='+page+'&action='+action+'&field='+field,
                'fsboard_helpwindow',
                'statusbar=no,menubar=no,toolbar=no,scrollbars=yes,resizable=yes,width=550,height=500'
        );        

}
*/

// This shit is going places when I refactor the template stuff
function collapse(id, url)
{
	$("div#tpl_row_"+id).slideToggle();
}


// ----------------------------------------------------------------------------------------
// Jquery redux

$(document).ready(function()
{

	/*
	 ******************
	 * MENU
	 ******************
	 */
	$("div.admin_menu_group div.admin_menu_group_header").click(function(){
			$(this).parent().find("div.admin_menu_link_group").slideToggle();
		});

	$("div.adminmenulink").mouseover(
		function(){
			$(this).addClass("adminmenulinkhover");
		}
	).mouseout(
		function(){
				$(this).removeClass("adminmenulinkhover");
		}
	);

	/*
	 ******************
	 * PLUGINS
	 ******************
	 */

	 /*
	  * asyncronously get the information about a hook
      */
	if($("form[name=plugin_files_form] select[name=hook]").attr("value") != "")	 
		plugin_hook_get_info();

	$("form[name=plugin_files_form] select[name=hook]").change(plugin_hook_get_info);
	
	function plugin_hook_get_info()
	{
	
		var hook_name = $("form[name=plugin_files_form] select[name=hook]").attr("value");
		
		if(hook_name == "")
			return false;
		
		$.ajax({
			url : "index.php",
			data : "m=plugins&m2=hookinfo&hook="+hook_name,
			type : "GET",			
			success : function(html)
			{
   				$("div.plugin_hook_info").html(html);
			}
		});
		
		return false;
	
	}

	 /*
	  * Plugin view dropdown
      */
	$("form[name=plugin_main_form] select").change(function()
	{
		
		plugins_go_to_page(
			$(this).attr("name").split("_")[1],
			$(this).val()
		);
		
	});

	$("form[name=plugin_main_form] input[name=go_button]").click(function()
	{
		
		plugins_go_to_page(
			$(this).parent().find("select").attr("name").split("_")[1],
			$(this).parent().find("select").val()
		);
		
	});


	function plugins_go_to_page(plugin_id, destination)
	{
	
		window.location = "../admin/index.php?m=plugins&id="+plugin_id+"&m2="+destination;
		
	}
	

});