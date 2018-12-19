/**
 * Created by Jerome Renaux (jerome.renaux@gmail.com) on 07-02-18.
 */
var Game = {};

var tileSize = 32;

Game.preload = function(){
    Game.scene = this; 
    this.load.image('terrain', 'terrain.png');  
};

Game.create = function(){
    Game.scene.load.tilemapCSV('map', 'map1.csv?v=1');
    var map = Game.scene.make.tilemap({key:'map'});
    var tiles = map.addTilesetImage('terrain', 'terrain'); 
    console.log(map.layers);
    var layer = map.createStaticLayer("tiles", tiles);
};


/**
 * Created by Jerome Renaux (jerome.renaux@gmail.com) on 07-02-18.
 */
var config = {
    type: Phaser.AUTO,
    width: 1000, 
    height: 600,
    parent: 'game',
    scene: [Game]
};

var game = new Phaser.Game(config);
