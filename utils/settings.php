<?php

$_SETTINGS = [
    'base' => '../',
    'tileSize' => 32,
    'tilesPerChunk' => 100,
    'special' => 15,
    'tilesPerRow' => 5,	


    'corners' => [
        'tl' => [4,1,2],
        'tr' => [2,3,5],
        'br' => [5,7,8],        
        'bl' => [7,6,4],
    ],


    // files and rows
    'filesToInclude' => [
        'coast' => 5,
        'grassland' => 4,
        'wasteland' => 6,
        'jungle' => 6,
        'swamp' => 8,    
        'water' => 9,        
        'westfall' => 4,
        'inferno' => 8,
        'road1' => 3,
        'road2' => 3,
    ],

    'terrain' => [
        'grassland' => 'g',
        'westfall' => 'f',
        'swamp' => 's',
        'jungle'  => 'j',
        'wasteland' => 'a',
        'coast' => 'c',
        'water'  => 'w',
        'deepwater'  => 'o',
        'swampwater'  => 'v',               
        'westfall' => 'f',
        'inferno' => 'i',
        'road1' => 'r',
        'road2' => 'R',
    ],

    // terrain 
    'terrainRGB' => [
        'grassland' => [12, 126, 15],
        'swamp' => [0,68,0],
        'jungle'  => [82,253,82],
        'wasteland' => [106,0,0],
        'coast' => [252,255,0],
        'water'  => [13,73, 204],
        'deepwater'  => [12,0,123],
        'swampwater'  => [82,96, 101],                      
        'westfall' =>  [253,199, 42],
        'inferno' =>  [15,15, 15],
        'road1' =>  [105,80, 3],
        'road2' =>  [150,150, 150],
    ],


    'terrainMapping' => [
        'transitions' => array_flip([
            'tl','t','tr','ibr', 'ibl', 
            'l', 'i', 'r', 'itr', 'itl',
            'bl', 'b', 'br'
        ]),

        'water' => [
            'terrain' => [7,14,15]
        ],    

        'coast' => [
            'terrain' => [14,25]
        ],

        'grassland' => [
            'terrain' => [14,17],
            'special' => [18,20],
        ],

        'wasteland' => [
            'terrain' => [14,16],
            'special' => [17,28],
        ],

        'jungle' => [
            'terrain' => [14,39],
        ],

        'swamp' => [
            'terrain' => [14, 30],
            'special' => [31, 39]
        ],

        'westfall' => [
            'terrain' => [14,17],
            'special' => [18,20],
        ],

        'road1' => [
            'terrain' => [14,15],
        ],

        'road2' => [
            'terrain' => [14,15],
        ],

        'inferno' => [
            'terrain' => [14,39],
        ],
    ],

    /**
     * Order of terrain. First comes top terrain (i.e. snow), last comes last terrain.
     * it is important for transitions
     */
    'terrainOrder' => [
        'R',
        'r',
        'v',
        'w',
        'o',
        'c',
        's',
        'i',
        'j',
        'g',
        'f',
        'a',
        's',
    ],

    'dimensionOrder' => [
        'tl', 't', 'tr',
        'l',       'r',
        'br', 'b', 'br'
    ],

    'dimensions' => [
        'tl' => [ -1, -1],
        't' => [1, -1],
        'tr' => [1, -1],
        'l' => [ -1, 0],
        'r' => [ 1, 0],
        'bl' => [-1, 1],
        'b' => [0,1],
        'br' => [1,1],
        'itr' => [-1,1],
        'itl' => [1,1],
        'ibr' => [-1,-1],
        'ibl' => [1,-1]
    ],

    /**
     *  1 2 3
     *  4   5
     *  6 7 8
     * counting neighbours of current thing
     */
    'dimensions2transition' => [
        'itr' => [6],
        'itl' => [8],
        'ibr' => [1],
        'ibl' => [3],

        'tl' => [1,2,4],
        't' => [2],
        'tr' => [2,3,5],
        'l' => [4],
        'r' => [5],
        'bl' => [4,6,7],
        'b' => [7],
        'br' => [5,7,8],
    ],
];


function g($name) {
    global $_SETTINGS;
    return $_SETTINGS[$name] ?? null;
}