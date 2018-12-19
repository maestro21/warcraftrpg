<?php

/**
 * Helper class for map
 */
class map {

    var $debug = true;

    var $roundmap = false;

    var $startWithZero = true;

    var $x;
    var $y;

    var $map = [];

    var $transitions = null;

    var $directionOrder = [
        'tl', 't', 'tr',
        'l',       'r',
        'br', 'b', 'br'
    ];

    var $directions = [
        'tl' => [ -1, -1],
        't' => [0, -1],
        'tr' => [1, -1],
        'l' => [ -1, 0],
        'r' => [ 1, 0],
        'bl' => [-1, 1],
        'b' => [0,1],
        'br' => [1,1],
    ];


    var $corners = [ 
        'tl' => ['l', 'tl', 't', 'ibr'], 
        'tr' => ['t', 'tr', 'r', 'ibl'] ,
        'br' => ['r', 'br', 'b', 'itl'] , 
        'bl' => ['b', 'bl', 'l', 'itr'] 
    ];

    var $terrainOrder = null;

    function setMap($data = []) {
        if(is_array($data)) {
            $this->map = $data;
            $this->y = count($data);
            $this->x = count($data[0]);       
        }
    }

    function __construct($data = null, $terrainOrder = null, $terrainMapping = []) { 
        if(is_array($data)) {
            $this->setMap($data);
        }
        $this->terrainOrder = $terrainOrder;
        $this->terrainMapping = $terrainMapping;
    }


    function getTile($x, $y) {
        if($y < 0 || $y > $this->y) return null;
        
        if($x > $this->x || $x < 0) {
			if(!$this->roundmap) return null;
			if($x < 0) $x = $this->x - $x;
			if($x >= $this->x) $x = $x - $this->x;
		}
        return $this->map[$x][$y] ?? null;
    }

    /**
     * Get neighbour tile
     *
     * @param $x
     * @param $y
     * @param $direction
     * @return array | null - raw tile data
     */
    function getNeightbourTile($x,$y, $direction) { 
       list($x, $y) = $this->getDirectionXy($x,$y, $direction);
       return $this->getTile($x,$y);
    }

    function addNeighbourTransitionData($x,$y, $direction, $data) {
        list($x, $y) = $this->getDirectionXy($x,$y, $direction);
        $this->transitions[$x][$y] = $data;
    }

    function getDirectionXy($x,$y, $direction){
        $xy = $this->directions[$direction] ?? null; 
        if(!$xy) return [$x, $y];

        $y = $y + $xy[1];
        $x = $x + $xy[0];
        [$x, $y];
    }

    function replaceSidesWithCorners($v) {
        foreach($this->corners as $corner) {
            $ic = $corner[3];
            if(in_array($ic,$v)) {
                return [$ic];
            }
        }
        return $v;
    }
    
    function getTransitions($x, $y) {
        foreach($this->corners as $corner => $cornerData) { 
            //$this->debug("[%s,%s] Corner %s has following directions:%s", [$x, $y, $corner, implode(',', $cornerData)]);
            $this->getCornerTransitions($x, $y, $cornerData);
        }

        foreach($this->transitions[$y][$x] as $k => $v) {
            $v = array_unique($v);
            $v = $this->replaceSidesWithCorners($v);    
            $this->transitions[$y][$x][$k] = $v;
        }
    }

    function getCornerTransitions($x, $y, $cornerData) {        
        $transitions =  $this->transitions[$x][$y];
        $tile = $this->getTile($x,$y);
        $left = $this->getNeightbourTile($x,$y, $cornerData[0] ?? null);
        $corner = $this->getNeightbourTile($x,$y, $cornerData[1] ?? null);
        $right = $this->getNeightbourTile($x,$y, $cornerData[2] ?? null);
        //$this->debug( 'Tile:%s %s:%s %s:%s %s:%s', [$tile, $cornerData[0], $left, $cornerData[1], $corner, $cornerData[2], $right]);
        $comp =  [
            $this->compareTiles($tile, $left),
            $this->compareTiles($tile, $corner),
            $this->compareTiles($tile, $right)
        ];
        $_comp = (int)$comp[0] . (int)$comp[1] . (int)$comp[2];
        $this->debug($_comp);
        switch($_comp) {
            case '100': $this->addNeighbourTransitionData($x, $y, $tile, $cornerData[0]); break;
            case '010': $this->addNeighbourTransitionData($x, $y, $tile, $cornerData[1]); break;
            case '001': $this->addNeighbourTransitionData($x, $y, $tile, $cornerData[2]); break;
            case '001': $this->addNeighbourTransitionData($x, $y, $tile, $cornerData[3]); break;
            break;

        }
        
        /*
        if($comp[0] && $comp[2] && $left == $right) {
            if(!isset($transitions[$left])) $transitions[$left] = [];
            $transitions[$left][] = $cornerData[3];
        } else {
            if($comp[0]) {
                if(!isset($transitions[$left])) $transitions[$left] = [];
                $transitions[$left][] = $cornerData[0];
            }
            if($comp[2]) {
                if(!isset($transitions[$right])) $transitions[$right] = [];
                $transitions[$right][] = $cornerData[2];
            }
        }
        //$this->debug();
        // inverse corner
        if($comp[1] && $corner != $left && $corner != $right) {
            if(!isset($transitions[$corner])) $transitions[$corner] = [];
            $transitions[$corner][] = $cornerData[1];
        } */
        if($_comp != '000') {
            //echo $x . ' ' . $y;
            //print_r($transitions);
        }
        //$this->transitions[$x][$y] = $transitions;
    }
    
    function compareTiles($tile, $neigbourTile) { //if($tile != $neigbourTile) $this->debug('%s %s', [$tile, $neigbourTile];
        if($tile === null || $neigbourTile === null) return false;
        return (array_search($tile, $this->terrainOrder) < array_search($neigbourTile, $this->terrainOrder));
    }


    function getTransitionLayer() {
        if(!$this->transitions) {
            $this->transitions = [];
            for($y = 0; $y < $this->y; $y++) {
                $this->transitions[$y] = [];
                for($x = 0; $x < $this->x; $x++) {
                    $this->transitions[$y][$x] = [];
                } 
            }

            for($y = 0; $y < $this->y; $y++) {
                for($x = 0; $x < $this->x; $x++) {
                   $this->getTransitions($x,$y);
                } 
            }
        }
        return $this->transitions;     
    }

    function debug($text = '', $args = []) {
        if($this->debug) {
            vprintf($text  . PHP_EOL, $args);
        }
    }
}