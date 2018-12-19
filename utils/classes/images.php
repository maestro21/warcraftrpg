<?php


/**
 * Work with images
 */

function splitMapIntoTiles() {
	$width = $height = G('tilesPerChunk');

	$fpath = 'map.png';

	$source = @imagecreatefrompng($fpath);
	$source_width = imagesx( $source );
	$source_height = imagesy( $source );

	$i = 0;
	echo "<table>";
	for( $row = 0; $row < $source_height / $height; $row++)
	{
		echo "<tr>";	
		for( $col = 0; $col < $source_width / $width; $col++)
		{	

			$fn = sprintf( g('base') . "map/img/map%02d.png", $i );

			echo "<td><img src='$fn'></td>";

			$im = @imagecreatetruecolor( $width, $height );
			imagecopyresized( $im, $source, 0, 0,
				$col * $width, $row * $height, $width, $height,
				$width, $height );
			imagepng( $im, $fn );
			imagedestroy( $im );
			$i++;
		}
		echo "</tr>";	
	} 
	echo "</table>";
}

function compileTileset() {
	$filesToinclude = g('filesToInclude');
  
	$tilesize = 32;
	$width = 5 * $tilesize;
	$height = array_sum($filesToinclude) * $tilesize;
	$offsetY = 0; 
	$img =  @imagecreatetruecolor( $width, $height );
	imagealphablending($img, false);
	imagesavealpha($img, true);

	foreach($filesToinclude as $file => $rows) {
		$fpath = 'terrain/' . $file. '.png';
		$source = @imagecreatefrompng($fpath);
		$width = imagesx( $source );
		$height = imagesy( $source );
		imagecopyresized( $img, $source, 0, $offsetY,
			0, 0, $width, $height,
			$width, $height );
		imagedestroy( $source );
		$offsetY = $offsetY + ($rows * $tilesize);
	}

	$fn = g('base') . 'gfx/terrain.png';	
	imagepng( $img, $fn );
	echo "<img src='$fn'>";
}