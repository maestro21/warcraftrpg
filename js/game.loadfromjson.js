
var game = new Phaser.Game(2580, 1000, Phaser.CANVAS, 'phaser-example', { preload: preload, create: create, update: update });

function preload() {

	game.load.tilemap('map', 'level.php', null, Phaser.Tilemap.CSV);
    game.load.image('tiles', 'tiles.png?v=2');

}

var cursors;

function create() {

    map = game.add.tilemap('map', 32, 32);
    map.addTilesetImage('tiles','tiles',32,32);

    //  0 is important
    layer = map.createLayer(0);

    //  Scroll it
    layer.resizeWorld();

    game.physics.startSystem(Phaser.Physics.ARCADE);

    cursors = game.input.keyboard.createCursorKeys();

}

function update() {

    if (cursors.left.isDown)
    {
        game.camera.x--;
    }
    else if (cursors.right.isDown)
    {
        game.camera.x++;
    }

    if (cursors.up.isDown)
    {
        game.camera.y--;
    }
    else if (cursors.down.isDown)
    {
        game.camera.y++;
    }

}
