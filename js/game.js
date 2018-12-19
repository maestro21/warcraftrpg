/**
 * Created by Jerome Renaux (jerome.renaux@gmail.com) on 07-02-18.
 */
var Game = {};

var tileSize = 32;
var chunkSize = 100;

Game.preload = function(){
    Game.scene = this; // Handy reference to the scene (alternative to `this` binding)
    // We will be loading files on the fly, so we need to listen to events triggered when
    // a file (a tilemap, more specifically) is added to the cache
    this.cache.tilemap.events.on('add',function(cache,key){
        Game.displayChunk(key);
    });
    this.load.image('terrain', 'gfx/terrain.png');
    this.load.image('footman', 'gfx/footman.png');
};

Game.create = function(){
    // Handles the clicks on the map to make the character move
    this.input.on('pointerup',Game.handleClick);

    Game.maps = {}; // Maps chunk id's to the corresponding tilemaps; used to be able to destroy them
    Game.displayedChunks = []; // List of the id's of the chunks currently displayed
 
    Game.chunkWidth = chunkSize;
    Game.chunkHeight = chunkSize;
    Game.nbChunksHorizontal = 6;
    Game.nbChunksVertical = 6;
    Game.lastChunkID = (Game.nbChunksHorizontal*Game.nbChunksVertical);

    Game.camera = this.cameras.main;
    var worldWidth = Game.nbChunksHorizontal*Game.chunkWidth; // width of the world in tiles
    var worldHeight = Game.nbChunksVertical*Game.chunkHeight; // height of the world in tiles
    Game.camera.setBounds(0, 0, worldWidth*32, worldHeight*32);

    var footman = this.add.image(tileSize*10,tileSize*10,'footman');
    footman.setDepth(1); // So that the ground layer of the newly drawn chunks is not drawn on top of our guy
    Game.camera.startFollow(footman);
    Game.player = footman;

    Game.updateEnvironment(); // The core method responsible for displaying/destroying chunks
};

Game.handleClick = function(pointer){
    var x = Game.camera.scrollX + pointer.x;
    var y = Game.camera.scrollY + pointer.y;
    Game.player.setPosition(x,y);
    Game.updateEnvironment();
};

// Determines the ID of the chunk on which the player charachter is based on its coordinates in the world
Game.computeChunkID = function(x,y){
    var tileX = Math.floor(x/tileSize);
    var tileY = Math.floor(y/tileSize);
    var chunkX = Math.floor(tileX/Game.chunkWidth);
    var chunkY = Math.floor(tileY/Game.chunkHeight);
    return (chunkY*Game.nbChunksHorizontal)+chunkX;
};

// Returns the entries in secondArray that are not present in firstArray
Game.findDiffArrayElements = function(firstArray,secondArray){
    return firstArray.filter(function(i) {return secondArray.indexOf(i) < 0;});
};

Game.updateEnvironment = function(){
    var chunkID = Game.computeChunkID(Game.player.x,Game.player.y); console.log(chunkID); 
    var chunks = Game.listAdjacentChunks(chunkID); // List the id's of the chunks surrounding the one we are in
    console.log(chunks);
    var newChunks = Game.findDiffArrayElements(chunks,Game.displayedChunks); // Lists the surrounding chunks that are not displayed yet (and have to be)
    var oldChunks = Game.findDiffArrayElements(Game.displayedChunks,chunks); // Lists the surrounding chunks that are still displayed (and shouldn't anymore)


    newChunks.forEach(function(c){ 
         if (c < 10) c = '0' + c;
        //console.log('loading chunk'+c);

        //Game.scene.load.tilemapCSV('chunk'+c, 'map/csv/map'+c+'.csv?v=1');

        //Game.scene.load.tilemapCSV('trans'+c, 'map/csv/map'+c+'_trans.csv?v=1');
        Game.scene.load.tilemapTiledJSON('chunk'+c, 'map/json/map'+c+'.json');
        c++;
    });
    if(newChunks.length > 0) Game.scene.load.start(); // Needed to trigger loads from outside of preload()

    oldChunks.forEach(function(c){
        console.log('destroying chunk'+c);
        Game.removeChunk(c);
    });
};

Game.displayChunk = function(key){ 
    var map = Game.scene.make.tilemap({ key: key});
    //console.log(map);
    // The first parameter is the name of the tileset in Tiled and the second parameter is the key
    // of the tileset image used when loading the file in preload.
    var tiles = map.addTilesetImage('terrain', 'terrain'); 
    
    // We need to compute the position of the chunk in the world
    var chunkID = parseInt(key.match(/\d+/)[0]); // Extracts the chunk number from file name 
    //var layerName = key.replace(/\d+/,''); 
    var chunkX = (chunkID%Game.nbChunksHorizontal)*Game.chunkWidth;
    var chunkY = Math.floor(chunkID/Game.nbChunksHorizontal)*Game.chunkHeight;
    
    console.log(map.layers.length);
    for(var i = 0; i < map.layers.length; i++) {
        // You can load a layer from the map using the layer name from Tiled, or by using the layer
        // index
        var layer = map.createStaticLayer(i, tiles, chunkX*tileSize, chunkY*tileSize);
        // Trick to automatically give different depths to each layer while avoid having a layer at depth 1 (because depth 1 is for our player character)
        var depth = 2 * i;
       // if(layerName == 'trans') depth--; console.log(depth);
            console.log(depth);
        layer.setDepth(depth);
    }

    Game.maps[chunkID] = map;
    Game.displayedChunks.push(chunkID);
};

Game.removeChunk = function(chunkID){
    Game.maps[chunkID].destroy();
    var idx = Game.displayedChunks.indexOf(chunkID);
    if(idx > -1) Game.displayedChunks.splice(idx,1);
};

// Returns the list of chunks surrounding a specific chunk, taking the world borders into
// account. If you find a smarter way to do it, I'm interested!
Game.listAdjacentChunks = function(chunkID){ 
    var chunks = [];
    var isAtTop = (chunkID < Game.nbChunksHorizontal);
    var isAtBottom = (chunkID > Game.lastChunkID - Game.nbChunksHorizontal);
    var isAtLeft = (chunkID%Game.nbChunksHorizontal == 0);
    var isAtRight = (chunkID%Game.nbChunksHorizontal == Game.nbChunksHorizontal-1);
    chunks.push(chunkID);
    if(!isAtTop) chunks.push(chunkID - Game.nbChunksHorizontal);
    if(!isAtBottom) chunks.push(chunkID + Game.nbChunksHorizontal);
    if(!isAtLeft) chunks.push(chunkID-1);
    if(!isAtRight) chunks.push(chunkID+1);
    if(!isAtTop && !isAtLeft) chunks.push(chunkID-1-Game.nbChunksHorizontal);
    if(!isAtTop && !isAtRight) chunks.push(chunkID+1-Game.nbChunksHorizontal);
    if(!isAtBottom && !isAtLeft) chunks.push(chunkID-1+Game.nbChunksHorizontal);
    if(!isAtBottom && !isAtRight) chunks.push(chunkID+1+Game.nbChunksHorizontal); 
    return chunks;
};