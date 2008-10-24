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
*       Table and Form class    *
*       Started by Fiona        *
*       26th Dec 2005           *
*********************************
*       Last edit by Fiona      *
*       26th Feb 2006           *
*********************************

This script includes a couple of classes for
easily generating forms and tables without
all the needless HTML.
*/




// ----------------------------------------------------------------------------------------------------------------------


// Check script entry
if (!defined("FSBOARD")) die("Script has not been initialised correctly! (FSBOARD not defined)");


class form_generate
{

        var $form_dir = "#";
        var $started = false;
        var $ignore_started = false;

        // ------------------------------        
        // Spit out the start <form> tag
        // ------------------------------        
        function start_form($name = "myform", $dir = "#", $method = "post", $file = false, $blank = false, $top = false)
        {
        
                if(!$this -> started || $this -> ignore_started)
                {
                        $this -> form_dir = $dir;
                        $this -> started = true;
                        
				$enctype = ($file) ? "enctype=\"multipart/form-data\"" : "";
				
				if($blank)
					$target = "target=\"_blank\"";
				elseif($top)
					$target = "target=\"_top\"";
				else
					$target = "";
                                
				return "\n<form ".$enctype." ".$target." action=\"".$dir."\" method=\"".$method."\" name=\"".$name."\">";
				        
			}

        }


        // ------------------------------        
        // One-line text input
        // ------------------------------        
        function input_text($name, $value, $class="inputtext", $width = "90%")
        {

                if($this -> started || $this -> ignore_started)
                {

			$title = (defined("DEVELOPER")) ? " title = \"".$name."\" " : ""; 
			
                        return '<input type="text" class="'.$class.'" style="width : '.$width.';" name="'.$name.'" value="'._htmlspecialchars($value).'"'.$title.'>';

                }
                
        }                  


        // ------------------------------        
        // Password input ***********
        // ------------------------------        
        function input_password($name, $value, $class="inputtext", $width = "90%")
        {

                if($this -> started || $this -> ignore_started)
                {

			$title = (defined("DEVELOPER")) ? " title = \"".$name."\" " : ""; 
			
                        return '<input type="password" class="'.$class.'" style="width : '.$width.';" name="'.$name.'" value="'.$value.'"'.$title.'>';

                }
                        
        }                  


        // ------------------------------        
        // Similar to one-line except for ints.
        // ------------------------------        
        function input_int($name, $value, $class="inputtext", $width = "5")
        {

                $value = intval($value);
                
                if($this -> started || $this -> ignore_started)
                {

						$title = (defined("DEVELOPER")) ? " title = \"".$name."\" " : ""; 
			
                        return '<input type="text" class="'.$class.'" size="'.$width.'" name="'.$name.'" value="'._htmlspecialchars($value).'"'.$title.'>';
                
                }
                        
        }                  


        // ------------------------------       
        // Multi lined input
        // ------------------------------        
        function input_textbox($name, $value, $rows = 3, $class="inputtext", $width = "90%")
        {
        
                if($this -> started || $this -> ignore_started)
                {

			$title = (defined("DEVELOPER")) ? " title = \"".$name."\" " : ""; 
			
                        return '<textarea class="'.$class.'" rows="'.$rows.'" style="width : '.$width.';" name="'.$name.'"'.$title.'>'.$value.'</textarea>';

                }
                        
        }                  


        // ------------------------------        
        // Browse for file
        // ------------------------------        
        function input_file($name, $class="inputtext", $width = "40")
        {

                if($this -> started || $this -> ignore_started)
                {

			$title = (defined("DEVELOPER")) ? " title = \"".$name."\" " : ""; 
			
                        return '<input type="file" class="'.$class.'" size="'.$width.'" name="'.$name.'"'.$title.'>';
                
                }
                        
        }                  


        // ------------------------------        
        // A checkbox
        // ------------------------------        
        function input_checkbox($name, $value, $class="inputtext", $selected = false, $on_click = "")
        {

                if($this -> started || $this -> ignore_started)
                {

			$title = (defined("DEVELOPER")) ? " title = \"".$name."\" " : ""; 

                        if($on_click != "")
                                $on_click = " onclick=\"".$on_click."\" ";
                                
                        // selected
                        if($selected)
                                return '<input type="checkbox" class="'.$class.'" name="'.$name.'" value="'.$value.'" checked="checked" '.$on_click.$title.'>';
                        // Not selected
                        else    
                                return '<input type="checkbox" class="'.$class.'" name="'.$name.'" value="'.$value.'" '.$on_click.$title.'>';
                
                }
                
        } 
        

        // ------------------------------        
        // Multiple checkboxes
        // ------------------------------        
        function input_checkbox_list($name, $selected_array, $list_array, $class="inputtext")
        {

                if($this -> started || $this -> ignore_started)
                {

                        if(count($list_array) < 0)
                                return false;
                        
                        $return = "";
                        
                        foreach($list_array as $key => $val)
                        {
                                
                                $return .= $this -> input_checkbox($name."[".$key."]", "1", $class, $selected_array[$key])." ".$val;
                                $return .= "<br />";
                                
                        }
                
                        return $return;
                
                }
                
        } 


        // ------------------------------        
        // Produces Yes/No radios. 1 = yes 0 = no
        // ------------------------------        
        function input_yesno($name, $value, $class="inputtext")
        {

                global $lang;

                if($this -> started || $this -> ignore_started)
                {

			$title = (defined("DEVELOPER")) ? " title = \"".$name."\" " : ""; 
                        
                        // "Yes" selected
                        if($value == '1')
                                return '<label for="'.$name.'_yes">'.$lang['yes'].'</label> <input type="radio" class="'.$class.'" id="'.$name.'_yes" name="'.$name.'" value="1" checked="checked"'.$title.'> '.
                                	'<label for="'.$name.'_no">'.$lang['no'].'</label> <input type="radio" class="'.$class.'" id="'.$name.'_no" name="'.$name.'" value="0">';
                        // "No" selected
                        else    
                                return '<label for="'.$name.'_yes">'.$lang['yes'].'</label> <input type="radio" class="'.$class.'" id="'.$name.'_yes" name="'.$name.'" value="1"> '.
                                	'<label for="'.$name.'_no">'.$lang['no'].'</label> <input type="radio" class="'.$class.'" id="'.$name.'_no" name="'.$name.'" value="0" checked="checked"'.$title.'>';
                
                }
                
        } 


        // ------------------------------        
        // Takes two arrays for text and values to generate a dropdown box
        // ------------------------------        
        function input_dropdown($name, $value, $dropdown_values_array, $dropdown_text, $class="inputtext", $width = "90%", $extra = "")
        {
        
                if($this -> started || $this -> ignore_started)
                {
        
						$title = (defined("DEVELOPER")) ? " title = \"".$name."\" " : ""; 

                        $dropdown = "<select class=\"".$class."\" style=\"width : ".$width.";\" name=\"".$name."\" ".$extra.$title.">";
                        
                        $a = 0;
                        
                        foreach($dropdown_values_array as $dropdown_value)
                        {
                        
                                $dropdown_value = trim($dropdown_value);
                                
                                if(check_equals($dropdown_value, $dropdown_text[$a], ""))
                                	continue;                                	
                                
                                if($value == $dropdown_value)
                                        $selected = " selected";
                                else
                                        $selected = "";                                
                                
                                $dropdown .= "<option value=\"".$dropdown_value."\"".$selected.">".$dropdown_text[$a]."</option>";
                                $a ++;
                                
                        }
        
                        $dropdown .= "</select>";
                        
                        return($dropdown);
                        
                }
                
        }                              


        // ------------------------------        
        // Datesses for like birtthdays and stuff god im drunk
        // ------------------------------        
        function input_date_input($name, $value, $no_time = false, $is_birthday = false)
        {
                //haha i wronte functin then

                global $lang;

                if($this -> started || $this -> ignore_started)
                {
        
                        // **************************
                        // Build dropdowns for the month
                        // **************************
                        $month_text = array(" ", $lang['january'], $lang['february'], $lang['march'], $lang['april'], $lang['may'], $lang['june'],
                         $lang['july'], $lang['august'], $lang['september'], $lang['october'], $lang['november'], $lang['december']);
                        
                        for($a = 0; $a <= 12; $a++)
                                $month_values[] = $a;
                        
        
                        // **************************
                        // Birthdays need different values returning
                        // **************************
                        if($is_birthday)
                        {
                                $year = $value[2];
                                $month = $value[1];
                                $day = $value[0];
        
                                $return = "<table cellpadding=\"0\" cellspacing=\"2\" border=\"0\"><tr>".
                                "<td>".$lang['day']."<br />".$this -> input_int("birthday_day", $day)."</td>".
                                "<td>".$lang['month']."<br />".$this -> input_dropdown("birthday_month", $month, $month_values, $month_text, "inputtext", "auto")."</td>".
                                "<td>".$lang['year']."<br />".$this -> input_int("birthday_year", $year)."</td>".
                                "</tr></table>";
        
                        }
                        // **************************
                        // Non birthday date entries
                        // **************************
                        else
                        {
                                // Get the individual values
                                $year   = ($value > 0) ? return_formatted_date("Y", $value) : "";
                                $month  = ($value > 0) ? return_formatted_date("n", $value) : "";
                                $day    = ($value > 0) ? return_formatted_date("j", $value) : "";
                                $hour   = ($value > 0) ? return_formatted_date("H", $value) : "";
                                $minute = ($value > 0) ? return_formatted_date("i", $value) : "";
        
                                // Build the first few inputs
                                $return = "<table cellpadding=\"0\" cellspacing=\"2\" border=\"0\"><tr>".
                                "<td>".$lang['day']."<br />".$this -> input_int($name."[day]", $day)."</td>".
                                "<td>".$lang['month']."<br />".$this -> input_dropdown($name."[month]", $month, $month_values, $month_text, "inputtext", "auto")."</td>".
                                "<td>".$lang['year']."<br />".$this -> input_int($name."[year]", $year)."</td>";
                                
                                // If we want hour and minute
                                if(!$no_time)
                                        $return .= "<td>".$lang['hour']."<br />".$this -> input_int($name."[hour]", $hour)."</td>".
                                                "<td>".$lang['minute']."<br />".$this -> input_int($name."[minute]", $minute)."</td>";
        
                                // Done
                                $return .= "</tr></table>";
        
                        }
                       
                        // chuck it out
                        return $return;
                        
                }
                
        }
        
        
        // ------------------------------        
        // Hidden form element
        // ------------------------------        
        function hidden($name, $value)
        {

                if($this -> started || $this -> ignore_started)
                        return '<input type="hidden" name="'.$name.'" value="'.$value.'">';
        
        }                  


        // ------------------------------        
        // The almighty submit button
        // ------------------------------        
        function submit($name, $value, $class="submitbutton", $extra = "")
        {

                if($this -> started || $this -> ignore_started)
                        return '<input type="submit" class="'.$class.'" name="'.$name.'" value="'.$value.'" '.$extra.'>';
        
        }              
            
            
        // ------------------------------        
        // Javascript was being weird so I use this in some places
        // I don't *do* Javascript. Hate hate hate hate...
        // ------------------------------        
        function button($name, $value, $class="submitbutton", $extra = "")
        {

                if($this -> started || $this -> ignore_started)
                        return '<input type="button" class="'.$class.'" name="'.$name.'" value="'.$value.'" '.$extra.'>';
        
        }              
            

        // ------------------------------        
        // Like a submit but it resets forms!11
        // ------------------------------        
        function reset($name, $value, $class="submitbutton")
        {

                if($this -> started || $this -> ignore_started)
                        return '<input type="reset" class="'.$class.'" name="'.$name.'" value="'.$value.'">';
        
        }                  



        // ------------------------------        
        // Stop it
        // ------------------------------        
        function end_form()
        {
        
                if($this -> started || $this -> ignore_started)
                {
                        $this -> started = false;
                        return "</form>";
                }
        
        }
                
}


// ******************************************************************************
// ------------------------------------------------------------------------------
// ******************************************************************************


class table_generate
{

        var $started = false;
        var $colspan = 0;
        

        // ------------------------------        
        // Start up a table going
        // ------------------------------        
        function start_table($class = "", $style = "", $align = "center", $width="95%")
        {
        
                if(!$this -> started)
                {
                        $this -> started = true;

                        $css_html = ($class) ? 'class = "'.$class.'"' : "";
                        $style_html = ($style) ? 'style = "'.$style.'"' : "";
                        $align_html = ($align) ? 'align = "'.$align.'"' : "";
                                
                        return "<table ".$css_html." ".$style_html." border=0 width=".$width." ".$align_html.">\n";        
                }

        }


        // ------------------------------        
        // Takes a multi-dimensional array and creates a row with it.
        // ------------------------------        
        function add_row($td_array, $class = "", $style = "", $extra = "")
        {
        
                if($this -> started)
                {

                        if(!is_array($td_array))
                                      return false;
                
                        $html = "<tr>\n";
                        
                        $count = count($td_array);

                        $this -> colspan = $count;

                        for($a = 0; $a < $count ; $a++ )
                        {
                        
                                $css_html = ($class) ? 'class = "'.$class.'"' : "";
                                $style_html = ($style) ? 'style = "'.$style.'"' : "";

                                if(is_array($td_array[$a]))
                                {

                                     if(count($td_array[$a]) == 2)
                                        $html .= "<td ".$css_html." ".$extra." ".$style_html." width=\"".$td_array[$a][1]."\"><p>".$td_array[$a][0]."</p></td>\n";
                                     elseif(count($td_array[$a]) == 3)
                                        $html .= "<td ".$css_html." ".$extra." ".$style_html." width=\"".$td_array[$a][1]."\" align=\"".$td_array[$a][2]."\"><p>".$td_array[$a][0]."</p></td>\n";

                                }
                                else
                                     $html .= "<td ".$css_html." ".$extra." ".$style_html." ><p>".$td_array[$a]."</p></td>\n";

                        }
                        
                        $html .= "</tr>\n";
                        
                        return $html;

                }
                
        }


        // ------------------------------  
        // Just makes a one cell row.
        // ------------------------------          
        function add_basic_row($td_info, $class = "", $style = "", $align = "center", $width="95%", $colspan = "", $extra = "")
        {
        
                if($this -> started)
                {

                        if($colspan > 0)
                            $colspan_html = " colspan = ".$colspan;
                        elseif($this->colspan > 0)
                        	$colspan_html = " colspan = ".$this->colspan;
						else                                       
							$colspan_html = "";
                                        
                        $html = "<tr>\n";
                        
                        $css_html = ($class) ? 'class = "'.$class.'"' : "";
                        $style_html = ($style) ? 'style = "'.$style.'"' : "";

                        $html .= "<td ".$css_html." ".$extra." ".$style_html." ".$colspan_html." align=\"".$align."\"><p>".$td_info."</p></td>\n";
                        
                        $html .= "</tr>\n";
                        
                        return $html;

                }
                
        }
        

        // ------------------------------
        // A submit/reset row for ending a form
        // ------------------------------
        function add_submit_row($form_pointer, $name = "submit", $value = "", $colspan = "", $reset = true)
        {

                global $lang;
                
                // Work out colspan
                if($colspan == "")
                        $colspan = $this -> colspan;

                if($colspan > 0)
                        $colspan_html = " colspan = ".$colspan;
                else
                {
                        if($this->colspan > 0)
                                $colspan_html = " colspan = ".$this->colspan;
                }
                        
                // Button name..
                $_value = ($value == "") ? $lang['form_submit'] : $value;
                
                // whatever.
                $html = "<tr>\n<td class=\"strip3\" align=\"center\" ".$colspan_html.">".
                        $form_pointer -> submit($name, $_value, "submitbutton").
                        " ".
                        (($reset) ? ($form_pointer -> reset("reset", $lang['form_reset'], "submitbutton")) : "").
                        "</td>\n</tr>\n";

                // Check it back
                return $html;
                        
        }
        
        // ------------------------------        
        // Finishes off the table. Can throw a submit button on the end.
        // ------------------------------        
        function end_table($submit_text = "", $class = "", $style = "")
        {
        
                if($this -> started)
                {
                
                		$html = "";
                		
                        if($submit_text)
                        {
                        
                                $css_html = ($class) ? 'class = "'.$class.'"' : "";
                                $style_html = ($style) ? 'style = "'.$style.'"' : "";
                                $colspan = ($this -> colspan > 0) ? ' colspan="'.$this->colspan.' "' : ""; 

                                $html .= '<tr><td align="center" '.$colspan.' '.$css_html.' '.$style_html.'><input type="submit" name="submit" class="submitbutton" value="'.$submit_text.'"></td></tr>';

                        }
                        
                        $this -> started = false;
                        return $html."\n </table>\n";
                        
                }
        
        }


        // ------------------------------        
	// Header for the help buttons
        // ------------------------------        
	function add_top_table_header($text, $colspan = 0, $icon = "")
	{

		global $output;
		
		if(!$colspan)
			$colspan = $this -> colspan;
		elseif($colspan > $this -> colspan)
			$this -> colspan = $colspan;
			
		if($icon)
			$icon = "<img src=\"".IMGDIR."/icons/".$icon.".png\" style=\"vertical-align : middle;\" /> ";
		
		if($colspan)
			return $this -> add_basic_row($output -> return_help_button("", true).$icon.$text, "strip1",  "", "left", "100%", $colspan);
		else
			return $this -> add_basic_row($output -> return_help_button("", true).$icon.$text, "strip1",  "", "left");
		
	}
	
	
        // ------------------------------        
	// Lower header for the help buttons
        // ------------------------------        
	function add_secondary_table_header($text, $colspan = 0)
	{

		global $output;
		
		if($colspan)
			return $this -> add_basic_row($output -> return_help_button("", false).$text, "strip2",  "", "left", "100%", $colspan);
		else
			return $this -> add_basic_row($output -> return_help_button("", false).$text, "strip2",  "", "left");
		
	}
	

        // ------------------------------        
        // Simple input row for text
        // ------------------------------        
        function simple_input_row_text($form_pointer, $message = "", $name = "", $value = "", $help_field = "")
        {

		global $output;        	

		$help_field = ($help_field) ? $help_field : $name;
                        
                $return = $this -> add_row(
                                array(
                                        array($message, "50%"),
                                        array($output -> return_help_button($help_field, false).$form_pointer -> input_text($name, $value), "50%")
                                )
                        , "normalcell");
                                
                return $return;
                        
        }
        
        // ------------------------------        
        // Simple input row for password
        // ------------------------------        
        function simple_input_row_password($form_pointer, $message = "", $name = "", $value = "", $help_field = "")
        {

		global $output;        	

		$help_field = ($help_field) ? $help_field : $name;
                        
                $return = $this -> add_row(
                                array(
                                        array($message, "50%"),
                                        array($output -> return_help_button($help_field, false).$form_pointer -> input_password($name, $value), "50%")
                                )
                        , "normalcell");
                                
                return $return;
                        
        }

        // ------------------------------        
        // Simple input row for small numbers
        // ------------------------------        
        function simple_input_row_int($form_pointer, $message = "", $name = "", $value = "", $help_field = "")
        {

		global $output;        	

		$help_field = ($help_field) ? $help_field : $name;
                        
                $return = $this -> add_row(
                                array(
                                        array($message, "50%"),
                                        array($output -> return_help_button($help_field, false).$form_pointer -> input_int($name, $value), "50%")
                                )
                        , "normalcell");
                                
                return $return;
                        
        }

        // ------------------------------        
        // Simple input row for a textbox
        // ------------------------------        
        function simple_input_row_textbox($form_pointer, $message = "", $name = "", $value = "", $rows = 3, $help_field = "")
        {

		global $output;        	

		$help_field = ($help_field) ? $help_field : $name;
                        
                $return = $this -> add_row(
                                array(
                                        array($message, "50%"),
                                        array($output -> return_help_button($help_field, false).$form_pointer -> input_textbox($name, $value, $rows), "50%")
                                )
                        , "normalcell");
                                
                return $return;
                        
        }

        // ------------------------------        
        // Simple input row for a file select
        // ------------------------------        
        function simple_input_row_file($form_pointer, $message = "", $name = "", $help_field = "")
        {

		global $output;        	

		$help_field = ($help_field) ? $help_field : $name;
                        
                $return = $this -> add_row(
                                array(
                                        array($message, "50%"),
                                        array($output -> return_help_button($help_field, false).$form_pointer -> input_file($name), "50%")
                                )
                        , "normalcell");
                                
                return $return;
                        
        }


        // ------------------------------        
        // Simple input row for a single checkbox
        // ------------------------------        
        function simple_input_row_checkbox($form_pointer, $message = "", $name = "", $value = "", $selected = false, $help_field = "")
        {

		global $output;        	

		$help_field = ($help_field) ? $help_field : $name;
                        
                $return = $this -> add_row(
                                array(
                                        array($message, "50%"),
                                        array($output -> return_help_button($help_field, false).$form_pointer -> input_checkbox($name, $value, "inputtext", $selected), "50%")
                                )
                        , "normalcell");
                                
                return $return;
                        
        }
           
        // ------------------------------        
        // Simple input row for multiple checkboxs
        // ------------------------------        
        function simple_input_row_checkbox_list($form_pointer, $message = "", $name = "", $values = "", $list_array = "", $help_field = "")
        {

		global $output;        	

		$help_field = ($help_field) ? $help_field : $name;
                        
                $return = $this -> add_row(
                                array(
                                        array($message, "50%"),
                                        array($output -> return_help_button($help_field, false).$form_pointer -> input_checkbox_list($name, $values, $list_array, "inputtext"), "50%")
                                )
                        , "normalcell");
                                
                return $return;
                        
        }
                
        // ------------------------------        
        // Simple input row for yes/no input
        // ------------------------------        
        function simple_input_row_yesno($form_pointer, $message = "", $name = "", $value = "", $help_field = "")
        {
        	
		global $output;        	

		$help_field = ($help_field) ? $help_field : $name;
                        
                $return = $this -> add_row(
                                array(
                                        array($message, "50%"),
                                        array($output -> return_help_button($help_field, false).$form_pointer -> input_yesno($name, $value), "50%")
                                )
                        , "normalcell");
                                
                return $return;
                        
        }
                
        // ------------------------------        
        // Simple input row for dropdown boxes, don't we loooove these? NO.
        // ------------------------------        
        function simple_input_row_dropdown($form_pointer, $message = "", $name = "", $value = "", $dropdown_values = "", $dropdown_text = "", $help_field = "")
        {

		global $output;        	

		$help_field = ($help_field) ? $help_field : $name;
                        
                $return = $this -> add_row(
                                array(
                                        array($message, "50%"),
                                        array($output -> return_help_button($help_field, false).$form_pointer -> input_dropdown($name, $value, $dropdown_values, $dropdown_text), "50%")
                                )
                        , "normalcell");
                                
                return $return;
                        
        }

        // ------------------------------        
        // Simple input row for date/time
        // ------------------------------        
        function simple_input_row_date($form_pointer, $message = "", $name = "", $value = "", $no_time = false, $is_birthday = false, $help_field = "")
        {

		global $output;        	

		$help_field = ($help_field) ? $help_field : $name;
                        
                $return = $this -> add_row(
                                array(
                                        array($message, "50%"),
                                        array($output -> return_help_button($help_field, false).$form_pointer -> input_date_input($name, $value, $no_time, $is_birthday), "50%")
                                )
                        , "normalcell");
                                
                return $return;
                        
        }

}
?>
