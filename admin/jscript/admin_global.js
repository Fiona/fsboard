/*
--------------------------------------------------------------------------
FSBoard - Free, open-source message board system.
Copyright (C) 2007 Fiona Burrows (fiona@fsboard.net)

FSBoard is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License.
See gpl.txt for a full copy of this license.
--------------------------------------------------------------------------
*/

/**
 * Admin area javascript routines
 *
 * @author Fiona Burrows <fiona@fsboard.com>
 * @version 1.0
 * @package FSBoard
 * @subpackage Admin
 */


// -----------------------------------------------------------------------------


// This shit is going places when I refactor the template stuff
function collapse(id, url)
{
	$("div#tpl_row_"+id).slideToggle();
}


$(document).ready(function()
{

	/*
	 ******************
	 * MENU
	 ******************
	 */
	$("div.admin_menu_group div.admin_menu_group_header").click(
		function()
		{

			var group = $(this).parent().find("div.admin_menu_link_group");
			var row_id = group.attr("id").split("_")[1];

			// We are closed
			if(group.is(":hidden"))
			{
				group.slideDown();
				var ajax_type = "open";
				var image_name = "collapse.gif";
			}
			// We are open
			else
			{
				group.slideUp();
				var ajax_type = "close";
				var image_name = "expand.gif";
			}

			group.parent().find("img.admin_menu_header_button").attr("src", imgdir + image_name);
			$.get(board_url + "/admin/ajax/admin_menu/", { t: ajax_type, id: row_id });

		}
	);

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
	 * MULTI CHECK TABLES
	 ******************
	 */
	$("form table tr.results_table_header input[name=select_all_checkbox]").click(
		function()
		{
			var checkboxes = $(this).parent().parent().parent().find("input:checkbox");
			if($(this).is(':checked'))
				checkboxes.attr("checked", "checked");
			else
				checkboxes.attr("checked", "");
		}
	);

	/*
	 ******************
	 * HELP BUTTON
	 ******************
	 */
	$("span.admin_help a").click(
		function()
		{
			var info = $(this).attr("rel").split("|");

			window.open(
                'index.php?m=help&page='+info[0]+'&action='+info[1]+'&field='+info[2],
                'fsboard_helpwindow',
                'statusbar=no,menubar=no,toolbar=no,scrollbars=yes,resizable=yes,width=550,height=500'
			);        

			return false;
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
	$("form[name=plugin_files_form] select[name=hook]").each(
		function()
		{

			if($(this).attr("value") != "")
				plugin_hook_get_info();

			$(this).change(plugin_hook_get_info);

		}
	);
	
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


	/*
	 ******************
	 * CODEMIRROR
	 ******************
	 */
	$("textarea#css_codebox").each(
		function()
		{
			var editor = CodeMirror.fromTextArea(
				'css_codebox',
				{
					height: "350px",
//        parserfile: ["parsexml.js", "parsecss.js", "tokenizejavascript.js", "parsejavascript.js", "tokenizephp.js", "parsephp.js", "parser3phphtmlmixed.js"],
//        stylesheet: ["css/xmlcolors.css", "css/jscolors.css", "css/csscolors.css", "css/phpcolors.css"],
					path: board_url + "/admin/jscript/codemirror/",
					parserfile: "parsecss.js",
					stylesheet: board_url + "/admin/jscript/codemirror/css/csscolors.css",
//			continuousScanning: 500
				}
			);
		}
	);	

});