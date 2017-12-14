<?php

	require_once(dirname(__DIR__) . '/includes/app_config.php');
	
	/*
	require_once(dirname(__DIR__) . '/classes/BasicDataObject.class.php');
	require_once(dirname(__DIR__) . '/classes/CoupsmartDynamoDB.class.php');
	require_once(dirname(__DIR__) . '/classes/Common.class.php');
	*/
	
	class CacheImage extends BasicDataObject
	{
		var $original_image;
		var $width;
		var $height;
		var $resized_image;
		var $created;
		
		// Gets resized image from S3 if it is already cached in the cache_images table; returns null otherwise
		public static function get_resized_image_from_cache($original_image, $width, $height)
		{
			$resized_image = null;
			$where_clause = "original_image = '".Database::mysqli_real_escape_string($original_image)."' and width = '".Database::mysqli_real_escape_string($width)."' and height='".Database::mysqli_real_escape_string($height)."'";
			
			$cache_image = new CacheImage();
			$cache_image->Select($where_clause);
			if(!empty($cache_image->id))
			{ 
				// Check to see if the file physically exists on S3 and has a size greater than 0;
				$file_header_content = Common::get_header_content("http://uploads.coupsmart.com/".$cache_image->resized_image);
				// error_log("file_header_content in get_resized_image_from_cache(): ".var_export($file_header_content, true));
				// Check for file size; it must be greater than 0
				if(!empty($file_header_content['download_content_length']) && $file_header_content['download_content_length'] > 0)
					$resized_image = $cache_image->resized_image;
			}
			return $resized_image;
		}
		
		// Save/Cache resized image to the cache_images table against the original image and the new width and height
		public static function save_resized_image_to_cache($original_image, $width, $height, $resized_image)
		{
			$cache_image = new CacheImage();

			$cache_image->original_image 	= $original_image;
			$cache_image->width 				= $width;
			$cache_image->height 			= $height;
			$cache_image->resized_image	= $resized_image;
			
			$cache_image->Insert();
		}
		
		public static function get_resized_image_from_cache_dynamo_db($original_image, $width, $height, $bucket_name = "uploads.coupsmart.com")
		{
			global $upload_bucket;
			$resized_image = "resized_" . $width . "_" . $height . "_" . $original_image;
			
			$file_header_content = Common::get_header_content(dirname(__DIR__) . '/' . $upload_bucket . "/" . $resized_image);
						//error_log("file_header_content in get_resized_image_from_cache(): ".var_export($file_header_content, true));
						
			// Check for file size; it must be greater than 0
			if(!empty($file_header_content['download_content_length']) && $file_header_content['download_content_length'] > 0){
				return $resized_image;
			}	  
			return false;
		}
		
		public static function getImg($original_image, $width, $height, $bucket_name = "uploads.coupsmart.com")
		{

			require_once(__DIR__ . '/CoupsmartS3.class.php');
			//error_log("original image " . var_export($original_image, true));
			if (!empty($original_image)) {
				//error_log('the image was not empty');
				$resized_image = self::get_resized_image_from_cache_dynamo_db($original_image, $width, $height, $bucket_name);
				if(empty($resized_image))
				{
					//error_log("the resized image was empty: ");
					$CS3 = new CoupsmartS3();
					// connect to S3 cloud
					$CS3->s3_connect();
					
					// Get original image from S3
					$response = $CS3->get_image_file($original_image, $bucket_name);
					//error_log("response: " .var_export($response,true));
					$file_path = dirname(__DIR__) ."/images/downloaded/$original_image";
					//error_log("file path:" . var_export($original_image,true));

					// Generate unqiue file name for resized image
					$resized_image = md5(uniqid());
					$arr_img = explode(".", $original_image);
					$ext = $arr_img[count($arr_img) - 1];
					
					while (file_exists(dirname(__DIR__)."/images/downloaded/$resized_image.$ext"))
						$resized_image .= rand(10, 99);
					$original_image_valid = true;
					if (file_exists($file_path) && filesize($file_path) > 0) {
						try {
							$image = new Imagick($file_path);
							//error_log("trying to create a new Imagick file " . var_export($image, true));
						} catch (Exception $e) {
							error_log('could not open ' . $file_path . ': ' . $e->getMessage());
							$original_image_valid = false;
						}
					}
					// added is_object constraint since we were getting a fatal error
					// that $image was referring to a non-object
					
					if ($original_image_valid && is_object($image)) {
						// Save resized image to disk
						
						// $image->scaleImage($width, $height, true);
						$resized_dimensions = Common::scaleProportional($image, $width, $height);
						$image->resizeImage($resized_dimensions['width'], $resized_dimensions['height'], imagick::FILTER_MITCHELL, 0.9, false);
						// error_log("orignal image was valid " .var_export($resized_image, true));
						$image->writeImage(dirname(__DIR__)."/images/downloaded/$resized_image." . $ext);
					
						// Upload image file to bucket
						$CS3->add_image_file(dirname(__DIR__)."/images/downloaded/$resized_image." . $ext, $bucket_name);
						
						
						// Save resized image name to database
						self::save_resized_image_to_cache_dynamo_db($original_image, $width, $height, "$resized_image.$ext", $bucket_name);
						
						// Destroy the image object
						//$image->destroy();
					
						$resized_image = "$resized_image." . $ext;
					} else {
						$resized_image = null;
					}

					// Delete the resized image and the original image from the /images/downloaded folder
					if(file_exists(dirname(__DIR__)."/images/downloaded/$resized_image"))
						unlink(dirname(__DIR__)."/images/downloaded/$resized_image");

					if(file_exists(dirname(__DIR__)."/images/downloaded/$original_image"))
						unlink(dirname(__DIR__)."/images/downloaded/$original_image");
				}
				//error_log("resized image: " .var_export($resized_image,true));
				return $resized_image;
			} else {
				return false;
			}
		}

		
	}
?>