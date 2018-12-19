<?php

/**
 * Create map from images
 */
function makeMapFromImages() {
	$dirfiles = array_diff(scandir(g('base') . 'map/img/'), array('..', '.'));
	foreach($dirfiles as $file) {
		createMapFromImage($file);
	}	
}

/**
 * Create map from image
 */
function createMapFromImage($fpath) {
	$tileFile = str_replace('.png', '', $fpath);
	$terrainFile =  $tileFile . '_terrain';
	$tranFile = $tileFile . '_trans';
	$tiles = [];
	$terrain = [];
	$transitions = [];

	/** loading image **/
	$fpath = g('base') . 'map/img/' . $fpath;
	$size = getimagesize( $fpath); 
	if(!$size) return FALSE;
	$sizex = $size[0] - 1;
	$sizey = $size[1] - 1;
	
	switch($size['mime']) {
		case 'image/png': $img = imagecreatefrompng($fpath); break;
		case 'image/gif': $img = imagecreatefromgif($fpath); break;
		case 'image/jpeg': $img = imagecreatefromjpeg($fpath); break;
	}
	$x = $size[0];
	$y = $size[1]; $i=0;
	
	for($j = 0; $j < $y; $j++) {
		$tiles[$j] = [];
		$terrain[$j] = [];		
		$transitions[$j] = [];
		for($i = 0 ; $i < $x; $i++) {
			$tilex = $i;	
			$rgb = imagecolorat($img, $i, $j); 
			$cols = imagecolorsforindex($img, $rgb);
			$r = $cols['red'];
			$g = $cols['green'];
			$b = $cols['blue'];
			$rgb = array($r,$g,$b);
			/** assign terrain and tile */
			$_terrain = findClosestTerrain($rgb);
			$terrain[$j][] = g('terrain')[$_terrain];
			$tiles[$j][] = setTileByTerrain($_terrain);
			$transitions[$j][] = -1;
		}
	}	
	/*
	for($j = 0; $j < $y; $j++) {
		for($i = 0 ; $i < $x; $i++) {
			$transitions = getTransitions([
				'x' => $i, 
				'y' => $j,
				'map' => $terrain
			], $transitions);
		}
	} */

	$transitions = getTransitions($terrain);

	saveCsv($terrainFile, $terrain);
	saveCsv($tileFile, $tiles);
 	saveCsv($tranFile, $transitions); 
	saveTiledJson($tileFile, [$tiles, $transitions]);
	//die();
}
/**
 * Find closest terrain
 */
function findClosestTerrain($rgb) {
	$diff = 255 * 3;
	$result = 'grassland';

	foreach(g('terrainRGB') as  $terrain => $colors) {
		$trgb = $colors;//list($r, $g, $b) = sscanf($colors, "%02x%02x%02x");
		$r = abs($rgb[0] - $trgb[0]);
		$g = abs($rgb[1] - $trgb[1]);
		$b = abs($rgb[2] - $trgb[2]);
		$_diff = $r + $g + $b;
		if($_diff < $diff) {
			$diff = $_diff;
			$result = $terrain;
		}
	} 
	return $result;	 
}


/* save formats */

function saveTiledJson($outputFile, $layers) {
	$data = [	
		'height' =>  g('tilesPerChunk'),
		'width' =>  g('tilesPerChunk'),
		"nextobjectid" => 1,
		"orientation" => "orthogonal",
		"renderorder" => "right-down",
		"tiledversion" => "1.0.3",
		"tileheight" =>  g('tileSize'),
		"tilewidth" =>  g('tileSize'),
		"tilesets" => [[		
			"columns"=> g('tilesPerRow'),
			"firstgid"=>1,
			"image"=>"gfx\\terrain.png",
			"imageheight"=> array_sum(g('filesToInclude')) * g('tileSize'),
			"imagewidth"=>g('tilesPerRow') * g('tileSize') ,
			"margin"=>0,
			"name"=>"terrain",
			"spacing"=>0,
			"tilecount"=> g('tilesPerRow'),
			"tileheight"=> g('tileSize'),
			"tilewidth"=> g('tileSize')
		
		]],
		"type"=>"map",
		"version" => 1,
		"id" => (int)$outputFile,
		'layers' => [],	
	];

	foreach($layers as $tiles) { 
		$data['layers'][] = [
			'data' => array2string($tiles),
			"height"=> g('tilesPerChunk'),
			"name"=>"ground",
			"opacity"=>1,
			"type"=>"tilelayer",
			"visible"=>true,
			"width"=> g('tilesPerChunk'),
			"x"=>0,
			"y"=>0
		];
	}

	saveJson($outputFile, $data);
}

function saveJson($outputFile, $tiles) {
	file_put_contents(g('base') . 'map/json/' . $outputFile . '.json', json_encode($tiles));
}

function saveCsv($outputFile, $tiles) {	
	$output = '';
	foreach($tiles as $row) {
		$output .= implode(',', $row) . PHP_EOL;
	}	echo g('base') . 'map/csv/' . $outputFile . '.csv';
	file_put_contents(g('base') . 'map/csv/' . $outputFile . '.csv', $output);
}


function array2string($data) {
	$result = [];
	foreach($data as $row) {
		$result[] = implode(',',$row);
	}
	return explode(',', implode(',', $result));// . ']';
}