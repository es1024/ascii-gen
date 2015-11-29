<?php
// written by Eric Sheng
// reference implementation for http://codegolf.stackexchange.com/q/49587/29611

if($argc != 4){
	echo 'ascii art generator' . PHP_EOL;
	echo 'Written by Eric Sheng' . PHP_EOL . PHP_EOL;
	echo 'Usage: ' . $argv[0] . ' [input file] [final width] [final height]' . PHP_EOL;
	echo '    input file: PNG image to convert' . PHP_EOL;
	echo '    final width: Number of characters per row in output' . PHP_EOL;
	echo '    final height: Number of rows in output' . PHP_EOL . PHP_EOL;
	exit;
}

read($argv[1], $rawimg, $w, $h);
$tw = (int)$argv[2];
$th = (int)$argv[3];
// resize to 2x target dimensions (using 4 pixels per char)
resize($rawimg, $w, $h, $resized, 2*$tw, 2*$th);
unset($rawimg);
// desaturate
desaturate($resized, 2*$tw, 2*$th, $desaturated);
unset($resized);
// generate and print
for($i = 0; $i < $th; ++$i){
	for($j = 0; $j < $tw; ++$j){
		echo get_ascii_char($desaturated[2*$i][2*$j][0], $desaturated[2*$i][2*$j+1][0], $desaturated[2*$i+1][2*$j][0], $desaturated[2*$i+1][2*$j+1][0]);
	}
	echo PHP_EOL;
}

function read($fname, &$out, &$w, &$h){
	$img = imagecreatefrompng($fname);
	$w = imagesx($img);
	$h = imagesy($img);
	$out = [];

	for($y = 0; $y < $h; ++$y){
		$out[$y] = [];
		for($x = 0; $x < $w; ++$x){
			$rgb = imagecolorat($img, $x, $y);
			$out[$y][$x] = [($rgb >> 16) & 0xFF, ($rgb >> 8) & 0xFF, $rgb & 0xFF];
		}
	}
}
function resize($in, $ow, $oh, &$out, $nw, $nh){
	$out = [];
	for($i = 0; $i < $nh; ++$i){
		$low_i = floor($i * $oh/$nh);
		$high_i = ceil(($i + 1) * $oh/$nh);
		$out[$i] = [];
		for($j = 0; $j < $nw; ++$j){
			$low_j = floor($j * $ow/$nw);
			$high_j = ceil(($j + 1) * $ow/$nw);
			$r = $g = $b = 0;
			$c = 0;
			for($ii = $low_i; $ii < $high_i; ++$ii){
				for($jj = $low_j; $jj < $high_j; ++$jj){
					$r += $in[$ii][$jj][0];
					$g += $in[$ii][$jj][1];
					$b += $in[$ii][$jj][2];
					++$c;
				}
			}
			$out[$i][$j] = [floor($r/$c), floor($g/$c), floor($b/$c)];
			//$out[$i][$j] = [floor(($r+$g+$b)/$c/3),floor(($r+$g+$b)/$c/3),floor(($r+$g+$b)/$c/3)];
		}
	}
}
function desaturate($in, $w, $h, &$out){
	$out = [];
	for($i = 0; $i < $h; ++$i){
		$out[$i] = [];
		for($j = 0; $j < $w; ++$j){
			$c = floor(($in[$i][$j][0]+$in[$i][$j][1]+$in[$i][$j][2])/3);
			$out[$i][$j] = [$c, $c, $c];
		}
	}
}
function ind($a){
	return 3 - floor($a/64);
}
function get_ascii_char($a, $b, $c, $d){
	$e = ind($a); $f = ind($b); $g = ind($c); $h = ind($d);
	$z = floor(($e + $f + $g + $h)/4);
	$c = !!$e + !!$f + !!$g + !!$h;
	if($c == 0){
		return ' ';
	}else if($c == 1){
		if($e || $f)
			if($e >= 2 || $f >= 2)
				return '"';
			else
				return '\'';
		else
			if($g >= 2 || $h >= 2)
				return ',';
			else
				return '.';
	}else if($c == 2){
		if(($e == 0 && $f == 0) || ($g == 0 && $h == 0))
			return '-';
		else if(($e == 0 && $g == 0) || ($f == 0 && $h == 0))
			return ';';
		else if($e == 0 && $h == 0)
			return '/';
		else if($f == 0 && $g == 0)
			return '\\';
	}else if($c == 3){
		if($e == 0) return 'J';
		else if($f == 0) return 'L';
		else if($g == 0) return 7;
		else if($h == 0) return 'P';
	}else{
		if($z == 1) return '*';
		else if($z == 2) return 'C';
		else if($z == 3) return '#';
	}
}
