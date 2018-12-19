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
        list($x, $y) = $this->getNeighbourCoords($x,$y, $direction);

        return $this->getTile($x,$y);
    }

    function getNeighbourCoords($x,$y, $direction) {
        $xy = $this->directions[$direction] ?? null; 
        if(!$xy) return null;

        $y = $y + $xy[0];
        $x = $x + $xy[1];

        return [$x, $y];
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
        //$this->debug($_comp);
      // $transitions[$tile] = [];
        
        $data = null;

        // corner
        if($comp[0] && $comp[2] && $left == $right) {     
          $this->putTransition($x,$y,$tile, $cornerData[3], true, $cornerData[1]);
        }
        
        // inverse corner
        if($comp[1] && $corner != $left && $corner != $right) {
            $this->putTransition($x,$y,$tile, $cornerData[1], true);
         }

        // left
        if($comp[0]) {
           $this->putTransition($x,$y,$tile, $cornerData[0]);
        }

        // right
        if($comp[2]) {
           $this->putTransition($x,$y,$tile, $cornerData[2]);
        }
        
        /**
        if($_comp != '000') {
            echo $x . ' ' . $y;
            print_r($transitions);
    } /**/ /*
        if($data) {
            if(!isset($this->transitions[$x][$y][$tile])) $this->transitions[$x][$y][$tile] = [];        
            $this->transitions[$x][$y][$tile][] = $data;
        } */
        // $this->transitions[$x][$y] = $transitions; //da tut nado zamenitj na sosednij tile
    }

    function putTransition($x,$y,$tile,$data, $replace = false, $dir = null) {        
        list($x, $y) = $this->getNeighbourCoords($x,$y, $dir ?? $data);
        if($data) {
            if(!isset($this->transitions[$x][$y][$tile]) || $replace) $this->transitions[$x][$y][$tile] = []; 
            if($replace) $data = $this->invert($data);
            $this->transitions[$x][$y][$tile][] = $data;
        }
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

    function invert($terrain) { //return $terrain;
        $invert = [
            't' => 'b',
            'b' => 't',
            'l' => 'r',
            'r' => 'l',
            'ibr' => 'tl',
            'ibl' => 'tr',
            'itl' => 'br',
            'itr' => 'bl',

            'tl' => 'itl',
            'tr' => 'itr',
            'bl' => 'ibl',
            'br' => 'ibr',

           /* 'tl' => 'br',
            'br' => 'tl',
            'tr' => 'bl',
            'bl' => 'tr',
            'itl' => 'ibr',
            'ibr' => 'itl',
            'itr' => 'ibl',
            'ibl' => 'itr',*/
        ];
        return $invert[$terrain] ?? $terrain;
    }
}