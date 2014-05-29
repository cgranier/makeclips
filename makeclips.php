#!/usr/bin/env php
<?php

// makeclips.php
// Version 0.1 - 20140529124700
//
// 1. abrir archivo via command line args
// 2. leer nombre del archivo y time code
// 3. split time code por | 
// 4. para cada time code
// 	generar un comando nuevo para generar cada clip
// 5. guardar todo en un archivo nuevo
// 6. end

// Make sure we can properly deal with line endings in the file

ini_set('auto_detect_line_endings',TRUE);

// Read the command line arguments

if ($argc < 2) {
    echo "\n";
    echo "+------------------------------------------------------------------+\n";
    echo "+ ERROR: Insufficient arguments                                    +\n";
    echo "+ USAGE:                                                           +\n";
    echo "+ ./makeclips.php csv-filename path-to-videos                      +\n";
    echo "+------------------------------------------------------------------+\n\n";
}
else {
	// Load a CSV file with filenames and timecodes
	$filename = $argv[1];
	$path_parts = pathinfo($argv[1]);
	$outpath = $path_parts['dirname'];
	$outfile = $outpath . '/' . $path_parts['filename'] . '.sh';
	$inpath = $argv[2];
	$fo = fopen($outfile, 'w');

	if (($handle = fopen($filename, "r")) !== FALSE) {
		while (($data = fgetcsv($handle, 1000, ",")) != FALSE) {
			// time /Applications/ffmpeg -ss 0 -i /Volumes/Render4/Mi-Gorda-Bella_SPA_YH/y_n_mi-gorda-bella_SPA_0001.mp4 -t 434 -c:v libx264 -b:v 1800k -crf 22 -c:a copy -preset veryfast mi-gorda-bella_1800_veryfast_crf22_1_1.mp4
			fwrite($fo,"date".PHP_EOL);
			$episodeName = $data[0];
			$episodeRoot = explode(".",$episodeName)[0];
			$episodeNumber = substr($episodeRoot,-4);
			$clipCommand = "time /Applications/ffmpeg -ss 0 -i " . $inpath . $episodeName;
			$timeCodes = explode("|",$data[1]);
			$clip = 1;
			$startTime = 0;
			foreach($timeCodes as $break) {
				$timeCode = explode(":",$break);
				$seconds = intval($timeCode[0])*60*60 + intval($timeCode[1])*60 + intval($timeCode[2]);
				$clipCommand = "time /Applications/ffmpeg -ss " . $startTime . " -i " . $inpath . $episodeName . " -t " . ($seconds - $startTime) . " -c:v libx264 -b:v 1800k -crf 22 -c:a copy -preset veryfast " . $filename . "_1800_veryfast_crf22_" . $episodeRoot . "_" . $clip . ".mp4" . PHP_EOL;
				fwrite($fo, $clipCommand);
				$clip++;
				$startTime = $seconds;
			}
			$clipCommand = "time /Applications/ffmpeg -ss " . $startTime . " -i " . $inpath . $episodeName . " -t " . "1000" . " -c:v libx264 -b:v 1800k -crf 22 -c:a copy -preset veryfast " . $filename . "_1800_veryfast_crf22_" . $episodeRoot . "_" . $clip . ".mp4" . PHP_EOL;
			fwrite($fo, $clipCommand);
		}
	}
	fclose($handle);
	fclose($fo);
}


