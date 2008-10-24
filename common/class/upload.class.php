<?php
/**
 * This is a helper class for uploading files
 *
 */

class upload
{
	
	/**
	 * Enter description here...
	 *
	 * @var unknown_type
	 */
	var $destination_path = "";
	
	/**
	 * Enter description here...
	 *
	 * @var unknown_type
	 */
	var $overwrite_existing = False;
	
	/**
	 * Enter description here...
	 *
	 * @var unknown_type
	 */
	var $allowed_extensions = array();
	
	/**
	 * Enter description here...
	 *
	 * @var unknown_type
	 */
	var $allowed_dimensions = array("width" => 0, "height" => 0);
	
	/**
	 * Enter description here...
	 *
	 * @var unknown_type
	 */
	var $max_filesize = false;
	
	/**
	 * Enter description here...
	 *
	 * @var unknown_type
	 */
	var $is_image = false;
	
	/**
	 * Enter description here...
	 *
	 * @var unknown_type
	 */
	var $name_to_upload = "";
	
	var $uploaded_name = "";
	var $real_name = "";
	var $filesize = 0;
	var $mimetype = "";
	var $extension = "";
	
	var $width = 0;
	var $height = 0;
	
	
	
	/**
	 * Enter description here...
	 *
	 * @param unknown_type $is_image
	 * @return upload
	 */
	function upload($is_image = false)
	{
		
		$this -> is_image = $is_image;
		
	}
	
	
	/**
	 * Enter description here...
	 *
	 * @param unknown_type $file_info
	 */
	function retrieve_file_information($file_info)
	{

		$this -> uploaded_name = $file_info['tmp_name'];
		$this -> real_name = $file_info['name'];
		$this -> filesize = $file_info['size'];
		$this -> mimetype = (!$file_info['type']) ? "application/octetstream" : $file_info['type'];
		$this -> extension = $this -> get_extension($this -> real_name);
		
		if(!$this -> extension)
			return "Uploaded file has no extension.";
		
		if($this -> is_image)
		{

			if(($imginfo = $this -> is_image()) !== false)
			{
				
				// Dimensions
				$this -> width = $imginfo[0];
				$this -> height = $imginfo[1];
				
				// Get real extensions for type
				$allowed_image_exts = array(
					IMAGETYPE_GIF => array("gif"),
					IMAGETYPE_JPEG => array("jpg", "jpeg"),
					IMAGETYPE_PNG => array("png"),
					IMAGETYPE_SWF => array("swf"),
					IMAGETYPE_PSD => array("psd"),
					IMAGETYPE_BMP => array("bmp"),					
					IMAGETYPE_WBMP => array("wbmp"),
					IMAGETYPE_TIFF_II => array("tif", "tiff"),
					IMAGETYPE_TIFF_MM => array("tif", "tiff"),
					IMAGETYPE_IFF => array("iff"),
					IMAGETYPE_JB2 => array("jpg", "jpeg"),
					IMAGETYPE_JPC => array("jpg", "jpeg"),
					IMAGETYPE_JP2 => array("jpg", "jpeg"),
					IMAGETYPE_JPX => array("jpg", "jpeg"),
					IMAGETYPE_SWC => array("swc")
				);

				$no = True;
				
				foreach($allowed_image_exts as $exts)
					if(in_array($this -> extension, $exts))
						$no = False;
						
				if($no)
					return "Image format is not supported";
				
			}
			else
				return "Uploaded file is not an image.";
				
		}
		
		return true;
		
	}
	
	
	/**
	 * Enter description here...
	 *
	 * @param unknown_type $form_name
	 */
	function check_upload_from_form($form_name)
	{

		if(!$_FILES[$form_name]['tmp_name'] || !$_FILES[$form_name]['name'])
			return "No file was uploaded.";
		
		if(!is_uploaded_file($_FILES[$form_name]['tmp_name']))
			return "Upload was not recognised as an uploaded file."; 
		
		if(isset($_FILES[$form_name]['error']))
			if(($error = $this -> get_file_upload_error($_FILES[$form_name]['error'])) !== true)
				return $error;

		if(isset($_FILES[$form_name]['size']) && $_FILES[$form_name]['size'] == 0)
			return "An empty file was uploaded.";

		if(($error = $this -> retrieve_file_information($_FILES[$form_name])) !== true)
			return $error;

		if(($error = $this -> file_checks()) !== true)
			return $error;
			
		return True;
		
	}
	
	
	/**
	 * Enter description here...
	 *
	 * @param unknown_type $form_name
	 */
	function complete_upload_from_form()
	{
			
		$dest_name = ($this -> name_to_upload != "") ? $this -> name_to_upload : $this -> real_name;

		if($this -> overwrite_existing == False && file_exists($this -> destination_path.$dest_name))
			return "Destination filename already exists.";
		
		if(!move_uploaded_file($this -> uploaded_name, $this -> destination_path.$dest_name))
			return "File could not be moved to the correct folder. Please inform an administrator.";
			
		return True;
		
	}
	
	/**
	 * Enter description here...
	 *
	 * @param unknown_type $error_code
	 */
	function get_file_upload_error($error_code)
	{
	
		switch($error_code)
		{
			
			case 0: // UPLOAD_ERR_OK
				return true;
				
			case 1: // UPLOAD_ERR_INI_SIZE
				return "PHP 'upload_max_filesize' restriction was exceeded.";
				
			case 2: // UPLOAD_ERR_FORM_SIZE
				return "The file exceeded the filesize limit.";
				
			case 3: // UPLOAD_ERR_PARTIAL
				return "The file was only partially uploaded. Please try again.";
								
			case 4: // UPLOAD_ERR_NO_FILE
				return "No file was uploaded. Please try again.";
								
			case 6: // UPLOAD_ERR_NO_TMP_DIR
				return "Temporary directory was not found. Please check your PHP install.";
				
			case 7: // UPLOAD_ERR_CANT_WRITE
				return "There was an error writing to the disk.";
								
			default: // Sometimes PHP fails to upload and doesn't return an error code
					// This has sometimes been attributed to max_post_size being wrong.
				return "An unknown error occured. Please contact the site administrators.";
			
		}
		
	}
	
	
	/**
	 * Enter description here...
	 *
	 */
	function file_checks()
	{
		
		// Filesize
		if($this -> max_filesize && ($this -> filesize > ($this -> max_filesize*1024)) || $this -> filesize == 0)
			return "Filesize is invalid, files cannot be over ".$this -> max_filesize."KB";
		
		// Filename
		if(preg_match("/[\:\\/\?\*\"\<\>\|]/", $this -> real_name))
			return "Filename contains one or more invalid characters.";
		
		// Extension
		if(count($this -> allowed_extensions) > 0)
			if(!in_array($this -> extension, $this -> allowed_extensions))
				return "The file uploaded has a disallowed extension.";
		
		// Image dimensions
		if($this -> is_image && ($this -> allowed_dimensions['width'] || $this -> allowed_dimensions['height']))
		{
			
			if($this -> width > $this -> allowed_dimensions['width'] ||
				$this -> height > $this -> allowed_dimensions['height'])
			{
				return "The uploaded image is too large. The maximum dimensions are ".
					$this -> allowed_dimensions['width']."x".$this -> allowed_dimensions['height'];
			}
				
		}
				
		if(!file_exists($this -> destination_path) || !is_writable($this -> destination_path))
			return "Destination path for upload does not exist or is not writable";
		
		return True;
		
	}
	
	
	/**
	 * Enter description here...
	 *
	 * @param unknown_type $filename
	 */
	function get_extension($filename)
	{

		$position = strrpos($filename, '.');

		if($position === false)
			return false;
		else
			return substr($filename, $position+1);
					
	}
	
	
	/**
	 * Enter description here...
	 *
	 */
	function is_image()
	{
		
		if(strpos($this -> mimetype, "image") !== false)
		{
			if(($info = getimagesize($this -> uploaded_name)) === false)
				return false;
			else
				return $info;
		}
		
		return false;
		
	}
	
}


//  if __name__ == '__main__':
// 		#oh wait....

?>