<?php
require_once('classes/class.map.php');

function setTileByTerrain($terrain) {    
    
    switch($terrain) {
        case 'water':
        case 'swampwater':
        case 'deepwater':
          return setWaterTile($terrain);
        break;
    }

    $offset = getTilesetTerrainOffset($terrain);
    $terrainMapping = g('terrainMapping')[$terrain];

    if(isset($terrainMapping['special'])) {
        $special = (1 == rand(0,g('special')));
        if($special) {
            $special = $terrainMapping['special'];
            $offset += rand($special[0], $special[1]);
            return $offset;
        }
    }

    $terrain = $terrainMapping['terrain'];
    $offset += rand($terrain[0], $terrain[1]);
    return $offset;
}

function setWaterTile($terrain) {
    $offset = getTilesetTerrainOffset('water');
    if($terrain == 'swampwater') {
        $offset += 15;
    }
    if($terrain == 'deepwater') {
        $offset += 30;
    }

    $waterTiles = g('terrainMapping')['water']['terrain'];
    $waterTile = $waterTiles[rand(0,2)];

    return ($offset + $waterTile);
}



function getTilesetTerrainOffset($terrain) {
    if($terrain == 'swampwater') {
        return  getTilesetTerrainOffset('water') +  15;
    }
    if($terrain == 'deepwater') {
        return  getTilesetTerrainOffset('water') +  30;
    }


    $offset = 0;// print_r($terrain);
    foreach(g('filesToInclude') as $_terrain => $_offset) { 
        if($terrain == $_terrain) {  echo $terrain . ' ' . $offset . PHP_EOL; 
            $offset = $offset * 5;
            return $offset;
        }
        $offset += $_offset;
    }    
    return 0;
}

function offsetTest() {
    $terrain = g('terrainOrder');
    foreach($terrain as $ter) {
        echo $ter . ' ' . getTilesetTerrainOffset($ter) . ' ' . (getTilesetTerrainOffset($ter) / 5) . ' ' . setTileByTerrain($ter)  . '<br>' ;
    }

}



/** corners */
function getTransitions($data) {
    $terrain = g('terrainOrder');
    $terrainmapping = g('terrain');
    $map = new Map($data, $terrain, $terrainmapping);
    $map->debug = false;
    $transitions = $map->getTransitionLayer();
    //print_r($transitions);
    for($y = 0; $y < $map->y; $y++ ) {
        for($x = 0; $x < $map->x; $x++) {
            foreach($transitions[$x][$y] as $terrain => $dest) { 
                $transitions[$x][$y] = getTransitionGfx($dest[0], $terrain);
                if($transitions[$x][$y] > 0) print_r($transitions[$x][$y]);
                continue;
            }            
            if(empty($transitions[$x][$y])) {
                $transitions[$x][$y] = -1;
            }
        }
    }  
    print_r($transitions);
    return $transitions;
}

function getTransitionGfx($dest, $terrain) {
    $terrainMapping = g('terrainMapping');  $ter = array_flip(g('terrain'));
    echo $dest . ' ' . $terrainMapping['transitions'][$dest]  . ' ' . $ter[$terrain] .  getTilesetTerrainOffset($ter[$terrain]) . ' ' . (getTilesetTerrainOffset($ter[$terrain]) + $terrainMapping['transitions'][$dest]) . PHP_EOL;
    $pos = getTilesetTerrainOffset($ter[$terrain]) + $terrainMapping['transitions'][$dest] + 1; 
    return $pos;
}
