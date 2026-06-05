<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Cargar configuración
$config = require __DIR__ . '/config.php';

// Conexión a la BD characters
try {
    $db = new PDO(
        "mysql:host={$config['characters']['host']};dbname={$config['characters']['dbname']}",
        $config['characters']['user'],
        $config['characters']['pass'],
        $config['pdo_options']
    );
} catch (Throwable $e) {
    die("Error de conexión a la base de datos.");
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || empty($_POST['guid'])) {
    die("Solicitud inválida.");
}

$guid = intval($_POST['guid']);

// 1. Obtener datos del personaje
$stmt = $db->prepare("
    SELECT guid, online
    FROM characters
    WHERE guid = ?
");
$stmt->execute([$guid]);
$char = $stmt->fetch();

if (!$char) {
    die("Personaje no encontrado.");
}

if ($char['online'] == 1) {
    die("El personaje está en línea. Debe estar desconectado para usar Unstuck.");
}

// 2. Obtener homebind
$stmt = $db->prepare("
    SELECT mapId, posX, posY, posZ
    FROM character_homebind
    WHERE guid = ?
");
$stmt->execute([$guid]);
$hb = $stmt->fetch();

if (!$hb) {
    die("No se encontró la posición de homebind.");
}

$map = $hb['mapId'];
$x   = $hb['posX'];
$y   = $hb['posY'];
$z   = $hb['posZ'];

// 3. Teletransportar al personaje
$stmt = $db->prepare("
    UPDATE characters
    SET map = ?, position_x = ?, position_y = ?, position_z = ?
    WHERE guid = ?
");
$stmt->execute([$map, $x, $y, $z, $guid]);

echo "Personaje teletransportado correctamente a su homebind.";
?>

