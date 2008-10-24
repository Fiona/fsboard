/*
 ----------------------------
 FSBoard Javascript Functions
 ----------------------------
 Thanks to Mark Frimston for the cross-browser stuff.
 ----------------------------
*/



// ----------------------------------------------------------------------------------------
var isDHTML =0;
var isLayers =0;
var isAll =0;
var isID =0;
if(document.getElementById){isID=1; isDHTML=1;}
else{
        if(document.all){isAll=1; isDHTML=1;}
        else{
                browserVersion = parseInt(navigator.appVersion);
                if((navigator.appName.indexOf('Netscape') != -1) && (browserVersion == 4)) {isLayers=1; isDHTML=1;}
        }
}

function findDOM(objectID, withStyle){
        if(withStyle == 1){
                if(isID){return (document.getElementById(objectID).style);}
                else{
                        if(isAll){return (document.all[objectID].style);}
                        else{
                                if(isLayers){return (document.layers[objectID]);}
                        };
                }
        }else{
                if(isID){return (document.getElementById(objectID));}
                else{
                        if(isAll){return(document.all[objectID]);}
                        else{
                                if(isLayers){return (document.layers[objectID]);}
                        };
                }
        }
        return false;
}

// ----------------------------------------------------------------------------------------

// Rename the title tag, for the admin area.
function define_parent_title()
{

        if(document.title != "" && typeof(parent.document) != 'undefined')
                parent.document.title = document.title;
                
}                        

// ----------------------------------------------------------------------------------------

// Collapses and expands menu items
function collapse(object, imgDir)
{

        var dom = findDOM("row_" + object, 0);
        var dom2 = findDOM("img_" + object, 0);
        
        if(dom.style.display == 'none')
        {
                dom.style.display = '';
                dom2.src = imgDir + "/collapse.gif";
        }
        else
        {
                dom2.src = imgDir + "/expand.gif";
                dom.style.display = 'none';
        }
        
}

// ----------------------------------------------------------------------------------------


// Opens a new window for the admin help stuff
function open_admin_area_help(page, action, field)
{

        window.open(
                'index.php?m=help&page='+page+'&action='+action+'&field='+field,
                'fsboard_helpwindow',
                'statusbar=no,menubar=no,toolbar=no,scrollbars=yes,resizable=yes,width=550,height=500'
        );        

}

// ----------------------------------------------------------------------------------------
// Jquery redux

$(document).ready(function()
{

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