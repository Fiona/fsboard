<?php
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
 * BBCode parser
 *
 * @author Fiona Burrows <fiona@fsboard.com>
 * @version 1.0
 * @package FSBoard
 * @subpackage Main
 */


// -----------------------------------------------------------------------------


// Check script entry
if(!defined("FSBOARD"))
	die("Script has not been initialised correctly! (FSBOARD not defined)");


/**
 * That includes all of the functions for parsing BBCode
 * or stripping HTML making safe for viewing.
 */
class parser
{

    var $do_strip_html = true;
    var $do_bbcode = true;
    var $do_custom_bbcode = true;
    var $do_emoticons = true;
    var $do_nl2br = true;
    var $do_word_filter = true;

	var $tag_cache = false;
	var $emoticon_cache = false;

	var $quotes_closed_tags = 0;
	var $quotes_open_tags = 0;        

	var $saved_code_tags = array();

	
	/*
	 * Constructor
	 */
   function parser($html = true, $bbcode = true, $custom_bbcode = true, $emoticons = true, $nl2br = true, $word_filter = true)
   {
    
        $this -> do_strip_html = $html;
        $this -> do_bbcode = $bbcode;
        $this -> do_custom_bbcode = $custom_bbcode;
        $this -> do_emoticons = $emoticons;
        $this -> do_nl2br = $nl2br;
        $this -> do_word_filter = $word_filter;
    
    }


	/*
	 * Rejiggs all the options for the parser
	 */
    function options($html = true, $bbcode = true, $custom_bbcode = true, $emoticons = true, $nl2br = true, $word_filter = true)
    {
    
        $this -> do_strip_html = $html;
        $this -> do_bbcode = $bbcode;
        $this -> do_custom_bbcode = $custom_bbcode;
        $this -> do_emoticons = $emoticons;
        $this -> do_nl2br = $nl2br;
        $this -> do_word_filter = $word_filter;
    
    }    


	/*
	 * Pass it a bunch of text and it will kick back
	 * the parsed text.
	 * 
	 * @var string $text The string that needs to be parsed
	 * @return string The same text but parsed
	 */
    function do_parser($text)
    {
            
		$this -> saved_code_tags = array();
                    
		// First get the tags and pretty pictures
		$this -> tag_cache();
		$this -> emoticon_cache();
                                
		// Trim whitespace
		$text = trim($text);
                
		// sort out code tags
		if($this -> do_bbcode)
			$text = $this -> remove_code_tags($text);

		// Remove html
		if($this -> do_strip_html)
            $text = _htmlspecialchars($text);
                
        // Make urls clickable  
        $text = $this -> handle_urls($text);
                
		// TODO: Word filter

		// Do BBCode parse
        if($this -> do_bbcode)
            $text = $this -> do_bbcode_parse($text);

		// Do emoticons
        if($this -> do_emoticons)
            $text = $this -> do_emoticon_parse($text);

		// Replace new lines with linebreaks
        if($this -> do_nl2br)
        {
			$text = nl2br($text);
			$text = str_replace("\t", "&nbsp; &nbsp; ", $text);
        }
        
		// Put code tags back
        if($this -> do_bbcode && count($this -> saved_code_tags) > 0)
			$text = $this -> return_code_tags($text);

        return $text;

    }


	/*
	 * Does the initial removing of code tags
	 */        
	function remove_code_tags($text)
	{

		$text = preg_replace('#\[code(.*?)\](\r\n|\n|\r)?(.+?)\[/code\]#ies', '\$this -> handle_remove_code_tags(\'\\3\', \'\\1\')', $text);
		return $text;
                
	}        


	/*
	 * Regex callback for removing code tags
	 */
	function handle_remove_code_tags($inner_code, $attr)
	{

		global $db;

		// Ugh this bug.
		// Seriously though, preg_replace callbacks with the e modifer do
		// addslashes on everything, (that is, single and double quotes) but
		// when the parameter is evaluated, only one of types of slashes get
		// stripped afterwards.
		// Hence we have to do this hack to get rid of this stupidity. >(
		$inner_code = str_replace('\"', '"', $inner_code);

		// Non Geshi replacements if it dosen't parse it
		$no_geshi_search = array(
			" ",
			"\t",
			"\r\n",
			"\r",
			"\n"
		);

		$no_geshi_replace = array(
			"&nbsp;",
			"&nbsp; &nbsp; ",
			"<br />",
			"<br />",
			"<br />"
		);

		// We use GeSHi
		require_once(ROOT."common/class/geshi.class.php");
                
		$type = "";

		// We have an type
		if($attr)
		{

			if($attr[0] != "=")
				return "[code".$attr."]".$inner_code."[/code]";
                                
			$type = _substr(strtolower($attr), 1);                        

			$geshi = new GeSHi($inner_code, $type);
			$geshi -> line_ending = "<br />";
			$geshi->set_header_type(GESHI_HEADER_DIV);
                        
			$inner_code = $geshi->parse_code();        
                        
			if(isset($geshi -> language_data['LANG_NAME']))
				$type =  $geshi -> language_data['LANG_NAME'];
			else
			{
				$type = _substr($attr, 1);
				$inner_code = str_replace($no_geshi_search, $no_geshi_replace, $inner_code);
			}

		}
		else
			$inner_code = str_replace($no_geshi_search, $no_geshi_replace, _htmlspecialchars(stripslashes($inner_code)));

		// Get the uinque id                
		$id = md5(uniqid(rand(), true));

		// Save it
		$this -> saved_code_tags[$id] = array(
			"code" => $inner_code,
			"type" => $type
		);

		// Kick back the token to be replaced later                 
		return "[[code:".$id."]]";
                
	}

        
	/*
	 * Function that puts the code back unaffected by other parsing.
	 */
	function return_code_tags($text)
	{

		global $template_global, $output, $lang;
                
		foreach($this -> saved_code_tags as $id => $code_array)
		{
			$code_array['type'] = ($code_array['type']) ? $output -> replace_number_tags($lang['bbcode_code_type'], trim($code_array['type'])) : $lang['bbcode_code_default'];
			$text = str_replace("[[code:".$id."]]", trim($template_global -> bbcode_code_box($code_array['code'], $code_array['type'])), $text); 
		}
                
		return $text;
                
	}

        
	/*
	 * Handle URLs that are not in bbcode tags
	 */
	function handle_urls($text)
	{

		$text = ' '.$text;
        
		$text = preg_replace('#([\s\(\)])(https?|ftp|news){1}://([\w\-]+\.([\w\-]+\.)*[\w]+(:[0-9]+)?(/[^"\s\(\)<\[]*)?)#ie', '\'\\1\'.\$this -> bbcode_do_url(\'\\2://\\3\')', $text);
		$text = preg_replace('#([\s\(\)])(www|ftp)\.(([\w\-]+\.)*[\w]+(:[0-9]+)?(/[^"\s\(\)<\[]*)?)#ie', '\'\\1\'.\$this -> bbcode_do_url(\'\\2.\\3\', \'\\2.\\3\')', $text);
        
		return _substr($text, 1);
                
	}
        
                        
	/*
	 * Uses the internal cache to parse bbcode
	 * 
	 * @var string $text The text that needs to be parsed
	 * @return string The parsed text with BBcode replaced
	 */
	function do_bbcode_parse($text)
	{

		if($text === "")
			return "";

		foreach($this -> tag_cache as $tag_name => $tag_array)
		{

			$search2 = "";
			$tag_name = preg_quote($tag_name);
                        
			$modifiers = (isset($tag_array['modifiers'])) ? $tag_array['modifiers'] : "is";
                        
			if(isset($tag_array['option']))
			{
				$search = (isset($tag_array['search'])) ? $tag_array['search'] : '#\['.$tag_name.'\=(.*?)](.+?)\[/'.$tag_name.'\]#'.$modifiers;
                                
				if(isset($tag_array['optional_option']))
					$search2 = (isset($tag_array['search'])) ? $tag_array['search'] : '#\['.$tag_name.'\](.+?)\[/'.$tag_name.'\]#'.$modifiers;
			}
			else
				$search = (isset($tag_array['search'])) ? $tag_array['search'] : '#\['.$tag_name.'\](.+?)\[/'.$tag_name.'\]#'.$modifiers;
                        
			$replace = (isset($tag_array['callback'])) ? $tag_array['callback'] : $tag_array['replace'];
                        
			$text = preg_replace($search, $replace, $text);

			if($search2)
				$text = preg_replace($search2, $replace, $text);

		}
                
		// Send parsed bbcode back
		return $text;
                
	}
        
        
	/*
	 * Handle URL tags
	 */
	function bbcode_do_url($url, $link_text = "")
	{

		// Get rid of JS
		if(stripos($url, "javascript:") !== false)
			if($link_text)
				return "[url=".$url."]".$link_text."[/url]";
			else
				return "[url]".$url."[/url]";

		// Make sure the start is right
		if(!preg_match("#^(http|https|ftp)://#", $url))
			$url = 'http://'.$url;
        
		$url = str_replace('"', "", $url);
                
		// If we have no text me need it
		if(!$link_text)
		{

			// Cut long urls
			if(_strlen($url) > 50)
				$link_text = _substr($url, 0, 30)." &hellip; "._substr($url, -15);
			else
				$link_text = $url;                        

		}

		// Return html
		return "<a href=\"".$url."\">".$link_text."</a>";
                        
	}
                
        
	/*
	 * Handle email tags
	 */
	function bbcode_do_email($mail, $link_text = "")
	{

		// Get rid of JS (paranoia)
		if(stripos($mail, "javascript:") !== false )
			if($link_text)
				return "[email=".$mail."]".$link_text."[/email]";
			else
				return "[email]".$mail."[/email]";

		// Make sure the start is right
		if(!preg_match("#^(mailto):#", $mail))
			$_mail = 'mailto:'.$mail;
		else                
			$_mail = $mail;

		$mail = str_replace('"', "", $mail);
                
		// If we have no text me need it
		if(!$link_text)
			$link_text = $mail;                        

		// Return html
		return "<a href=\"".$_mail."\">".$link_text."</a>";
                        
	}
        
        
	/*
	 * Handle image tags
	 */
	function bbcode_do_img($url)
	{

		// Get rid of JS
		if(stripos($url, "javascript:") !== false )
			return "[img]".$url."[/img]";

		// Make sure the start is right
		if(!preg_match("#^(http|https|ftp)://#", $url))
			$url = 'http://'.$url;
                
		$url = str_replace('"', "", $url);

		// Return html
		return "<img src=\"".$url."\" alt=\"\" />";
                        
	}
        
        
	/*
	 * Uses the internal cache to parse cute emoticons
	 * 
	 * @var string $text The text that needs to be parsed
	 * @return string The parsed text with emoticons replaced
	 */
	function do_emoticon_parse($text)
	{

		if(count($this -> emoticon_cache) > 0)
			foreach($this -> emoticon_cache as $emote)
				$text = preg_replace("/(?<=^|\s|\>)".preg_quote($emote['code'], '#')."(?=$|\s|\>)/m", '<img src="'.ROOT.$emote['image'].'" style="border:none" alt="'.$emote['name'].'" />', $text);

		return $text;
                                
	}
        
        
	/*
	 * Deals with quote tags :)
	 */
	function bbcode_do_quote($text)
	{

		$this -> quotes_closed_tags = 0;
		$this -> quotes_open_tags = 0;
               
		if(stripos($text, "[quote") === false)
			return $text;

		$final_text = trim($text);
                
		$final_text = preg_replace("#\[quote=([^\]]+?)\](\r\n|\n|\r)?#ie", '\$this -> bbcode_do_quote_open(\'\\1\')', $final_text);
		$final_text = preg_replace("#\[quote\](\r\n|\n|\r)?#ie", '\$this -> bbcode_do_quote_open()', $final_text);
		$final_text = preg_replace("#\[/quote\](\r\n|\n|\r)?#ie", '\$this -> bbcode_do_quote_close()', $final_text);

		if($this -> quotes_closed_tags != $this -> quotes_open_tags)
			return $text;
		else
			return $final_text; 
                
	}
        
        
	/*
	 * Used for opening quotes and counting them
	 */
	function bbcode_do_quote_open($attr = "")
	{
                
		global $template_global, $output, $lang;
                                
		$this -> quotes_open_tags++;
                
		$quoter = ($attr) ? $output->replace_number_tags($lang['bbcode_quote_name_posted'], trim($attr)) : $lang['bbcode_quote_default'];

		return trim($template_global -> bbcode_quote_box_open($quoter));
                
	}

        
        
	/*
	 * Used for closing quotes and counting them
	 */
	function bbcode_do_quote_close()
	{

		global $template_global;
                
		$this -> quotes_closed_tags++;
                
		return trim($template_global -> bbcode_quote_box_close());
                
	}        
        
        
	/*
	 * This thing builds an internal cache of bbcode tags
	 */
	function tag_cache()
	{

		global $cache;

		if($this -> tag_cache !== false)
			return;

                
		// **************
		// Quote
		// **************
		$this -> tag_cache['quote'] = array(
			"search" => "#(\[quote\].*\[/quote\])#ies",
			"callback" => '\$this -> bbcode_do_quote(\'\\1\')'
		);                
                
		// **************
		// Basic formatting
		// **************
		$this -> tag_cache['b'] = array(
			"replace" => '<strong>\\1</strong>'
		);
                
		$this -> tag_cache['i'] = array(
			"replace" => '<em>\\1</em>'
		);
                
		$this -> tag_cache['u'] = array(
			"replace" => '<ins>\\1</ins>'
		);
                
		$this -> tag_cache['s'] = array(
			"replace" => '<del>\\1</del>'
		);                

		// **************
		// Stupid formatting
		// **************
		$this -> tag_cache['font'] = array(
			"replace" => '<span style="font-family : \\1;">\\2</span>',
			"option" => true,
		);                

		$this -> tag_cache['size'] = array(
			"replace" => '<span style="font-size : \\1;">\\2</span>',
			"option" => true,
		);                

		$this -> tag_cache['color'] = array(
			"replace" => '<span style="color : \\1;">\\2</span>',
			"option" => true,
		);                

		// **************
		// Super/subscript
		// **************
		$this -> tag_cache['sub'] = array(
			"replace" => '<sub>\\1</sub>'
		);                

		$this -> tag_cache['sup'] = array(
			"replace" => '<sup>\\1</sup>'
		);                

		// **************
		// Special chars
		// **************
		$this -> tag_cache['c'] = array(
			"search" =>  '#\(c\)#i',
			"replace" => '&copy;'
		);
		$this -> tag_cache['tm'] = array(
			"search" =>  '#\(tm\)#i',
			"replace" => '&#153;'
		);
		$this -> tag_cache['r'] = array(
			"search" =>  '#\(r\)#i',
			"replace" => '&reg;'
		);

		// **************
		// URLs
		// **************
		$this -> tag_cache['url'] = array(
			"callback" => '\$this -> bbcode_do_url(\'\\1\', \'\\2\')',
			"modifiers" => "ie",
			"option" => true,
			"optional_option" => true
		);

		// **************
		// E-mails
		// **************
		$this -> tag_cache['email'] = array(
			"callback" => '\$this -> bbcode_do_email(\'\\1\', \'\\2\')',
			"modifiers" => "ie",
			"option" => true,
			"optional_option" => true
		);                
                
		// **************
		// Images
		// **************
		$this -> tag_cache['img'] = array(
			"callback" => '\$this -> bbcode_do_img(\'\\1\')',
			"modifiers" => "ie"
		);                


		// **************
		// Custom bbcode
		// **************
		if(is_array($cache -> cache['custom_bbcode']) && count($cache -> cache['custom_bbcode']) > 0)
		{
			
			foreach($cache -> cache['custom_bbcode'] as $cache_entry)
			{                                

				$cache_entry['replacement'] = trim($cache_entry['replacement']);

				if(!$cache_entry['replacement'])
					continue;
                                
				if($cache_entry['use_param'])
				{
					$option = true;
					$cache_entry['replacement'] = str_ireplace(array("{param}", "{content}"), array("\\1", "\\2"), $cache_entry['replacement']);
				}
				else
				{
					$option = false;
					$cache_entry['replacement'] = str_ireplace("{content}", "\\1", $cache_entry['replacement']);
				}
                                
				$this -> tag_cache[$cache_entry['tag']] = array(
					"replace" => $cache_entry['replacement'],
					"option" => $option                                        
				);        
                
			}
                          
		}
		 
	}


        
        
	/*
	 * Builds an internal cache of emotes
	 */
	function emoticon_cache()
	{

		global $cache;

		if($this -> emoticon_cache !== false)
			return;

		if(is_array($cache -> cache['emoticons']) && count($cache -> cache['emoticons']) > 0)
		{
			
			foreach($cache -> cache['emoticons'] as $cache_entry)
			{                

				$this -> emoticon_cache[] = array(
					"code" => $cache_entry['emoticon_code'],
					"image" => $cache_entry['filename'],
					"name" => $cache_entry['name']
				);

			}

        }
        
	}
        
}

?>
