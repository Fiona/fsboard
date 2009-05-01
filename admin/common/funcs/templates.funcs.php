<?php
/* 
--------------------------------------------------------------------------
FSBoard - Free, open-source message board system.
Copyright (C) 2006 Fiona Burrows (fiona@fsboard.net)

FSBoard is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation; either version 2 of the License, or
(at your option) any later version.

FSBoard is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program; if not, write to the Free Software
Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA 
--------------------------------------------------------------------------

*********************************
*       FSBoard                 *
*       by Fiona 2006           *
*********************************
*          FUNCTIONS            *
*       Admin Templates         *
*       Started by Fiona        *
*       03rd Nov 2005           *
*********************************
*       Last edit by Fiona      *
*       01st Feb 2008           *
*********************************

*/




// ----------------------------------------------------------------------------------------------------------------------



// Check script entry
if (!defined("FSBOARD")) die("Script has not been initialised correctly! (FSBOARD not defined)");



//***********************************************
// Builds the template files by taking them from the DB
// Will build one file if $class_name is defined
// Will not write any files if $write_files is false
//***********************************************
function build_template_files($set_id, $class_name = false, $write_files = true)
{

        global $db;

        // If only one class
        if($class_name)
        {

                // start the php page
                $output_php = "<"."?php \n//FSBOARD GENERATED TEMPLATE FILE \n//DO NOT EDIT DIRECTLY \n\nclass ".$class_name." { \n";
        
                // Get templates
                $templates_query = $db -> query("select set_id,id,name,function_name,text,parameters from ".$db -> table_prefix."templates where set_id='".$set_id."' and class_name='".$class_name."' order by class_name,name");
        
                // Go through them all
                while($template_array = $db -> fetch_array($templates_query))
                {
                        
                        $output_php .= return_function_call($template_array);                        
                
                }
                
                // End the php
                $output_php .= "} \n?".">";
                
                // Check if this is writing files or returning
                if($write_files)
                {

                        // Write the file
                        $fh = fopen(ROOT."templates/template_id".$set_id."/".$class_name.".php", "w");
                        fwrite($fh, $output_php);
                        fclose($fh);

						@chmod(ROOT."templates/template_id".$set_id."/".$class_name.".php", 0777);
                        
                
                }
                else
                        return $output_php;
                        
        }
        else // All the classes
        {

                // Get templates
                $templates_query = $db -> basic_select("templates", "id,name,class_name,set_id,function_name,text,parameters", "set_id='".$set_id."'", "class_name,name"); 

                $a = 0;               
                $output_php = "";
                $num_rows = $db -> num_rows();


                // Go through 'em all
                while($template_array = $db -> fetch_array($templates_query))
                {

                    $a ++;

                    if($a == 1)
	                    $current_class_name = $template_array['class_name'];

                    // Check if we want to write the file
                    if($current_class_name != $template_array['class_name'])
                    {

                        if($current_class_name == $template_array['class_name'])
                            $output_php .= return_function_call($template_array);                        
                        
                        $final_php = return_file_contents($current_class_name, $output_php);

                        if($write_files)
                        {

                            // Write the file
                            $fh = fopen(ROOT."templates/template_id".$template_array['set_id']."/".$current_class_name.".php", "w");
                            fwrite($fh, $final_php);
                            fclose($fh);
                            
                        }
                        
                        $output_php = return_function_call($template_array);                        
                            
                    }
                    else
                        $output_php .= return_function_call($template_array);                        

                    $current_class_name = $template_array['class_name'];
                    
                }                        

                if($write_files)
                {

                    // Write the file
                    $fh = fopen(ROOT."templates/template_id".$set_id."/".$current_class_name.".php", "w");
                    fwrite($fh, return_file_contents($current_class_name, $output_php));
                    fclose($fh);
                    
                }
                
        }

}



/**
 * Chucks back the contents for a single file based on information
 * 
 * @param array $class_name Name of the class.
 * @param array $php_code The function calls for this template set
 * @return string The formatted file
 */
function return_file_contents($class_name, $php_code)
{
	
	return "<"."?php
//FSBOARD GENERATED TEMPLATE FILE
//DO NOT EDIT DIRECTLY

if (!defined(\"FSBOARD\")) die(\"Script has not been initialised correctly! (FSBOARD not defined)\");

class ".$class_name."
{

".$php_code."

}

?".">";

}



//***********************************************
// Returns what goes in a function chucked into the cache
//***********************************************
function return_function_call($template_array)
{

        // Play with the IF stuff
        $find = array(
                '#\<IF \"(.*?)\"\>#e',
                '#\<ELSE\>#e',
                '#\<ELSEIF \"(.*?)\"\>#e',
                '#\<ENDIF\>#e',
                '#\<FOR \"(.*?)\"\>#e',
                '#\<ENDFOR\>#e',
                '#\<FOREACH \"(.*?)\"\>#e',
                '#\<ENDFOREACH\>#e',
                '#\<URL \"(.*?)\"\>#e',
                '#\<IMG \"(.*?)\"\>#e'
		);

        $replace = array(
                'handle_opening_if(\'$1\')',
                'handle_else()',
                'handle_else_if(\'$1\')',
                'handle_closing_if()',
                'handle_opening_for(\'$1\')',
                'handle_closing_for()',
                'handle_opening_foreach(\'$1\')',
                'handle_closing_foreach()',
                'handle_url(\'$1\')',
                'handle_img(\'$1\')'
        );

        $template_array['text'] = preg_replace($find, $replace, $template_array['text']);

        // Return the whole function - MESS
        return "
function ".$template_array['function_name']."(".$template_array['parameters'].")
{

	global \$user, \$lang, \$cache, \$GLOBAL_OTHER;

	\$return_this = \"\";
	\$return_this .= <<<END
".$template_array['text']."
END;

	return \$return_this;

}\n\n";

}


//***********************************************
// These return all the stuff for IF statements
// Becase preg_replace hates me as if I killed it's mother
//***********************************************
function handle_opening_if($parameters)
{

        return
'
END;
// If statement!
if('.$parameters.')
{
$return_this .= <<<END
';

}

function handle_else()
{

        return
'
END;
}
else
{
$return_this .= <<<END
';

}

function handle_else_if($parameters)
{

        return
'
END;
}
elseif('.$parameters.')
{
$return_this .= <<<END
';

}

function handle_closing_if()
{

        return
'
END;
}
// End if statement!
$return_this .= <<<END
';

}



//***********************************************
// Similary for for
//***********************************************
function handle_opening_for($parameters)
{

        return
'
END;
// For loop!
for('.$parameters.')
{
$return_this .= <<<END
';

}

function handle_closing_for()
{

        return
'
END;
}
// End for loop!
$return_this .= <<<END
';

}


//***********************************************
// Foreach loops
//***********************************************
function handle_opening_foreach($parameters)
{

        return
'
END;
// Foreach loop!
foreach('.$parameters.')
{
$return_this .= <<<END
';

}

function handle_closing_foreach()
{

        return
'
END;
}
// End foreach loop!
$return_this .= <<<END
';

}



//***********************************************
// URL generation
//***********************************************
function handle_url($path)
{
	
	return
'
END;

$return_this .= l(\''.$path.'\').<<<END
';

}


//***********************************************
// Image path generation
//***********************************************
function handle_img($path)
{
	
	return
'
END;

$return_this .= img(\''.$path.'\').<<<END
';

}





//***********************************************
//***********************************************
// Imports templates from XML
//***********************************************
//***********************************************
function import_templates_xml($xml_contents, $ignore_version = false)
{
        
        global $db;

        // Start parser
        $xml = new xml;

        $xml -> import_root_name = "template_set_file";
        $xml -> import_group_name = "template_set";
        
        // Run parser and check version
        $parse_return = $xml -> import_xml($xml_contents, $ignore_version);

        if($parse_return == "VERSION" && !$ignore_version)
                return "VERSION";
        
        // Nothing?
        if(count($xml -> import_xml_values['template_set']) < 1)
                return true;

        // **********************
        // Go through each set              
        // **********************
        foreach($xml -> import_xml_values['template_set'] as $set)
        {

                // Stick it in! So to speak.               
                $set_insert = array(
                        'name'                  => $set['ATTRS']['name'],
                        'can_change_theme'      => $set['ATTRS']['can_change_theme'],
                        'author'                => $set['ATTRS']['author'],
                        'default_theme'         => $set['ATTRS']['default_theme']
                );
                
                if(!$db -> basic_insert("template_sets", $set_insert))
                        return false;

                // Get the ID
                $set_id = $db -> insert_id();

                // Create directory
                if(!@mkdir(ROOT."templates/template_id".$set_id, 0777))
                	return false;
                	
				@chmod(ROOT."templates/template_id".$set_id, 0777);

                // Log it!
                if(!defined("INSTALL"))
                        log_admin_action("templates", "doimport", "Imported template set: ".trim($set_insert['name']));

                // No templates in this group?
                if(count($set['template']) < 1)
                        continue;


                // **********************
                // Obviously we have templates in this group
                // **********************
                foreach($set['template'] as $id => $template)
                {

                        // Inseeeeeert
                        $template_insert = array(
                                'name'            => $template['ATTRS']['name'],
                                'set_id'          => $set_id,
                                'class_name'      => $template['ATTRS']['class_name'],
                                'function_name'   => $template['ATTRS']['function_name'],
                                'name'            => $template['ATTRS']['name'],
                                'text'            => trim($template['CONTENT']),
                                'parameters'      => trim($set['template_parameters'][$id]['CONTENT'])
                        );

                       if(!$db -> basic_insert("templates", $template_insert))
                                return false;                       
                        
                }

                // **********************
                // Build the new files
                // **********************
                build_template_files($set_id);
                                        
        }
        
        return true;
        
}

?>
