<?php
include('settings.php');
include('classes/images.php');
include('classes/tileset.php');
include('classes/map.php');

//offsetTest();
splitMapIntoTiles();
compileTileset();
makeMapFromImages(); 