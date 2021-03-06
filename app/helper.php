<?php

if (!function_exists('get_image_s3_url')) {
	function get_image_s3_url($image = "", $folder = "")
	{
		$result = array();

		if (strpos($image, "https") !== false OR strpos($image, "http") !== false) {
    		$result['real'] = $image;
			$result['thumbnail'] = $image;
		}else {
			$result['real'] = env('S3_URL').$folder.'/'.$image;
			$result['thumbnail'] = env('S3_URL').$folder.'/thumbnail/'.$image;	
		}
			
		return $result;
	}
}

if ( ! function_exists('config_path'))
{
    /**
     * Get the configuration path.
     *
     * @param  string $path
     * @return string
     */
    function config_path($path = '')
    {
        return app()->basePath() . '/config' . ($path ? '/' . $path : $path);
    }
}