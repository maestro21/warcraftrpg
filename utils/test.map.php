<?php

include('classes/class.map.php');

$terrain = [
    's',
    'g',
];

$data = [
    ['g', 'g', 'g'],
    ['g', 's', 'g'],
    ['g', 'g', 'g'],
];
$map = new Map($data, $terrain);
//saveCsv('map1', $data);

$transitions = $map->getTransitionLayer();

print_r($transitions);

//die();

$data = [
    [ 's', 's', 's', 's', 's', 's' ],  
    [ 's', 'g', 'g', 'g', 'g', 's' ],
    [ 's', 'g', 's', 's', 'g', 's' ],
    [ 's', 'g', 's', 's', 'g', 's' ], 
    [ 's', 'g', 'g', 'g', 'g', 's' ],
    [ 's', 's', 's', 's', 's', 's' ], 
];

//saveCsv('map2', $data);

$map = new Map($data, $terrain);

$transitions = $map->getTransitionLayer();

print_r($transitions);

function saveCsv($outputFile, $tiles) {	
    $output = '';
    $els = [
        's' => 15,
        'g' => 40,
    ];
    print_r($tiles);

	foreach($tiles as $row) {
        $_row = [];
        foreach($row as $el) {
            $_row[] = $els[$el];
        }
		$output .= implode(',', $_row) . PHP_EOL;
	}	
	file_put_contents( $outputFile . '.csv', $output);
}
