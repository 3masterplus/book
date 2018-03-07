<?php

error_reporting(0);
ini_set('display_error', false);
ini_set("max_execution_time", "30000");

$file = $argv[1];
$flat = "on";

// how much detail we want. Larger number means less detail
// (basically, how many bytes/frames to skip processing)
// the lower the number means longer processing time
define("DETAIL", 5);

if (file_exists($file)) {
	/**
	 * PROCESS THE FILE
	 */

	// temporary file name
	$tmpname = tempnam($s, "wav2json_");
	
	// copy from temp upload directory to current
	copy($file, "{$tmpname}_o.mp3");
	
	// support for stereo waveform?
	$stereo = false;
 
	// array of wavs that need to be processed
	$wavs_to_process = array();
	
	/**
	 * convert mp3 to wav using lame decoder
	 * First, resample the original mp3 using as mono (-m m), 16 bit (-b 16), and 8 KHz (--resample 8)
	 * Secondly, convert that resampled mp3 into a wav
	 * We don't necessarily need high quality audio to produce a waveform, doing this process reduces the WAV
	 * to it's simplest form and makes processing significantly faster
	 */
	if ($stereo) {
		// scale right channel down (a scale of 0 does not work)
		exec("/usr/local/bin/lame {$tmpname}_o.mp3 --scale-r 0.1 -m m -S -f -b 16 --resample 8 {$tmpname}.mp3 && /usr/local/bin/lame -S --decode {$tmpname}.mp3 {$tmpname}_l.wav");
		// same as above, left channel
		exec("/usr/local/bin/lame {$tmpname}_o.mp3 --scale-l 0.1 -m m -S -f -b 16 --resample 8 {$tmpname}.mp3 && /usr/local/bin/lame -S --decode {$tmpname}.mp3 {$tmpname}_r.wav");
		$wavs_to_process[] = "{$tmpname}_l.wav";
		$wavs_to_process[] = "{$tmpname}_r.wav";
	} else {
		exec("/usr/local/bin/lame {$tmpname}_o.mp3 -m m -S -f -b 16 --resample 8 {$tmpname}.mp3 && /usr/local/bin/lame -S --decode {$tmpname}.mp3 {$tmpname}.wav");
		$wavs_to_process[] = "{$tmpname}.wav";
	}
		
	// delete temporary files
	unlink("{$tmpname}_o.mp3");
	unlink("{$tmpname}.mp3");
	unlink("{$tmpname}");
	
	// get user vars from form
	$draw_flat = (isset($flat) && $flat == "on") ? true : false;
	
	$json = new stdclass;
	
	// process each wav individually
	for($wav = 1; $wav <= sizeof($wavs_to_process); $wav++) {

		$filename = $wavs_to_process[$wav - 1];
		
		if($wav == 1) {
			$json->left = array();
			$array =& $json->left;
		} else {
			$json->right = array();
			$array =& $json->right;
		}
		
		/**
		 * Below as posted by "zvoneM" on
		 * http://forums.devshed.com/php-development-5/reading-16-bit-wav-file-318740.html
		 * as findValues() defined above
		 * Translated from Croation to English - July 11, 2011
		 */
		$handle = fopen($filename, "r");
		// wav file header retrieval
		$heading[] = fread($handle, 4);
		$heading[] = bin2hex(fread($handle, 4));
		$heading[] = fread($handle, 4);
		$heading[] = fread($handle, 4);
		$heading[] = bin2hex(fread($handle, 4));
		$heading[] = bin2hex(fread($handle, 2));
		$heading[] = bin2hex(fread($handle, 2));
		$heading[] = bin2hex(fread($handle, 4));
		$heading[] = bin2hex(fread($handle, 4));
		$heading[] = bin2hex(fread($handle, 2));
		$heading[] = bin2hex(fread($handle, 2));
		$heading[] = fread($handle, 4);
		$heading[] = bin2hex(fread($handle, 4));
		
		// wav bitrate 
		$peek = hexdec(substr($heading[10], 0, 2));
		$byte = $peek / 8;
		
		// checking whether a mono or stereo wav
		$channel = hexdec(substr($heading[6], 0, 2));
		
		$ratio = ($channel == 2 ? 40 : 80);
		
		// start putting together the initial canvas
		// $data_size = (size_of_file - header_bytes_read) / skipped_bytes + 1
		$data_size = floor((filesize($filename) - 44) / ($ratio + $byte) + 1);
		$data_point = 0;

		while(!feof($handle) && $data_point < $data_size){
			if ($data_point++ % DETAIL == 0) {
				$bytes = array();
				
				// get number of bytes depending on bitrate
				for ($i = 0; $i < $byte; $i++)
					$bytes[$i] = fgetc($handle);
				
				switch($byte){
					// get value for 8-bit wav
					case 1:
						$data = findValues($bytes[0], $bytes[1]);
						break;
					// get value for 16-bit wav
					case 2:
						if(ord($bytes[1]) & 128)
							$temp = 0;
						else
							$temp = 128;
						$temp = chr((ord($bytes[1]) & 127) + $temp);
						$data = floor(findValues($bytes[0], $temp) / 256);
						break;
				}
				
				// skip bytes for memory optimization
				fseek($handle, $ratio, SEEK_CUR);
				
				// draw this data point
				// relative value based on height of image being generated
				// data values can range between 0 and 255
				$v = ($data / 255);
				
				$array[] = (float)number_format(abs(0.5 - $v), 4);
				
			} else {
				// skip this one due to lack of detail
				fseek($handle, $ratio + $byte, SEEK_CUR);
			}
		}
		
		// close and cleanup
		fclose($handle);

		// delete the processed wav file
		unlink($filename);
		
	}
	
	header("Content-Type: application/json");
	echo json_encode($json);
}

/**
 * GENERAL FUNCTIONS
 */
function findValues($byte1, $byte2){
	$byte1 = hexdec(bin2hex($byte1));                        
	$byte2 = hexdec(bin2hex($byte2));                        
	return ($byte1 + ($byte2*256));
}
