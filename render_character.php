<?php
session_start();

require_once __DIR__ . "/config.php";
require_once __DIR__ . "/includes/functions.php";
require_once __DIR__ . "/langs.php";
require_once __DIR__ . "/maps.php";                 // $mapNames
require_once __DIR__ . "/zones.php";
require_once __DIR__ . "/assets/inc/map_images.php"; // $map_data (imágenes)
require_once __DIR__ . "/assets/inc/map_data.php";   // get_player_position()

/* ============================
   CARGAR IDIOMA
   ============================ */
$lang = $langs[$_SESSION['lang']] ?? $langs['es'];

/* ============================
   VALIDAR SESIÓN
   ============================ */
if (!isset($_SESSION['account'], $_SESSION['characters'])) {
    http_response_code(403);
    exit("No session");
}

$guid = isset($_GET['guid']) ? (int)$_GET['guid'] : 0;
if (!$guid) exit("Invalid GUID");

/* ============================
   BUSCAR PERSONAJE EN SESIÓN
   ============================ */
$char = null;
foreach ($_SESSION['characters'] as $c) {
    if ((int)$c['guid'] === $guid) {
        $char = $c;
        break;
    }
}

if (!$char) exit("Character not found");

/* ============================
   DATOS BÁSICOS
   ============================ */
$mapId  = $char['map'];
$zoneId = $char['zone'];

$mapName  = $mapNames[$mapId]  ?? "Mapa desconocido ($mapId)";
$zoneName = $zoneNames[$zoneId] ?? "Zona desconocida ($zoneId)";

/* ============================
   COLORES DE CLASE
   ============================ */
$classColors = [
    1  => "#C79C6E",
    2  => "#F58CBA",
    3  => "#ABD473",
    4  => "#FFF569",
    5  => "#FFFFFF",
    6  => "#C41F3B",
    7  => "#0070DE",
    8  => "#69CCF0",
    9  => "#9482C9",
    11 => "#FF7D0A",
];

/* ============================
   ICONOS
   ============================ */
$raceIconFile  = raceIcon($char['race'], $char['gender']);
$classIconFile = classIcon($char['class']);

$raceIconPath  = "/tools/liberador/assets/icons/races/"  . $raceIconFile;
$classIconPath = "/tools/liberador/assets/icons/classes/" . $classIconFile;

$faction = in_array($char['race'], [1,3,4,7,11]) ? "alliance" : "horde";
$factionIcon = "/tools/liberador/assets/icons/factions/" . $faction . ".png";

/* ============================
   TRADUCCIONES
   ============================ */
$raceText  = $lang[raceName($char['race'])]  ?? $char['race'];
$classText = $lang[className($char['class'])] ?? $char['class'];

$genderText = ($char['gender'] == 0) ? $lang['male'] : $lang['female'];
$onlineText = $char['online'] ? $lang['yes'] : $lang['no'];

/* ============================
   DINERO
   ============================ */
$money = $char['money'];
$gold  = floor($money / 10000);
$silver = floor(($money % 10000) / 100);
$copper = $money % 100;

/* ============================
   POSICIÓN Y MAPA
   ============================ */
$posX = $char['position_x'];
$posY = $char['position_y'];

// Posición exacta según MiniManager (en píxeles del mapa original)
$pos = get_player_position($posX, $posY, $mapId);
$px  = $pos['x'];
$py  = $pos['y'];

// Imagen base según mapId
$baseImage = $map_data[$mapId]['image'] ?? "azeroth.jpg";

// Ajuste especial para Silvermoon / Draenei (map 530 pero en EK/Kalimdor)
if ($mapId == 530) {
    $x = $posX;
    $y = $posY;

    // Blood Elf zones (Eversong / Ghostlands / Silvermoon) → Azeroth
    if ($y < -1000 && $y > -10000 && $x > 5000) {
        $baseImage = "azeroth.jpg";
    }
    // Draenei zones (Azuremyst / Bloodmyst) → Kalimdor
    else if ($y < -7000 && $x < 0) {
        $baseImage = "azeroth.jpg";
    }
}

$mapImage = "/tools/liberador/assets/img/" . $baseImage;

/* ============================
   TAMAÑOS REALES DE LOS MAPAS
   ============================ */
$mapSizes = [
    "azeroth.jpg"   => [1002, 668],
    "azeroth.jpg"  => [1002, 668],
    "outland.jpg"   => [1002, 668],
    "northrend.jpg" => [966, 732],
];

list($mw, $mh) = $mapSizes[$baseImage] ?? [1002, 668];

// Normalizar coordenadas (0–1) para el JS
$px_norm = $mw > 0 ? $px / $mw : 0;
$py_norm = $mh > 0 ? $py / $mh : 0;

// Clamp por seguridad
$px_norm = max(0, min(1, $px_norm));
$py_norm = max(0, min(1, $py_norm));
?>

<div class="char-render">

    <h2 style="color: <?php echo $classColors[$char['class']] ?? "#FFFFFF"; ?>">
        <?php echo htmlspecialchars($char['name']); ?> (<?php echo (int)$char['level']; ?>)
        <strong>Hermandad:</strong> <?php echo $char['guild_name'] ?: 'Sin hermandad'; ?>
    </h2>

    <div style="display:flex; gap:15px; align-items:center; margin-bottom:10px;">
        <img src="<?php echo $raceIconPath; ?>" width="64" alt="Race">
        <img src="<?php echo $classIconPath; ?>" width="64" alt="Class">
        <img src="<?php echo $factionIcon; ?>" width="64" alt="Faction">
    </div>

    <p><strong><?php echo $lang['race']; ?>:</strong> <?php echo $raceText; ?></p>
    <p><strong><?php echo $lang['class']; ?>:</strong> <?php echo $classText; ?></p>
    <p><strong><?php echo $lang['gender']; ?>:</strong> <?php echo $genderText; ?></p>
    <p><strong><?php echo $lang['money']; ?>:</strong> <?php echo "{$gold}g {$silver}s {$copper}c"; ?></p>
    <p><strong><?php echo $lang['map']; ?>:</strong> <?php echo htmlspecialchars($mapName); ?></p>
    <p><strong><?php echo $lang['zone']; ?>:</strong> <?php echo htmlspecialchars($zoneName); ?></p>
    <p><strong>Coordenadas:</strong>
        X: <?php echo $posX; ?>,
        Y: <?php echo $posY; ?>,
        Z: <?php echo $char['position_z']; ?>
    </p>

    <p><strong><?php echo $lang['online']; ?>:</strong> <?php echo $onlineText; ?></p>

</div>

<?php if (!empty($baseImage)): ?>

<div id="mapWrapper" style="
    width: 100%;
    max-width: 1024px;
    aspect-ratio: 4 / 3;
    position: relative;
    overflow: hidden;
">

    <div id="mapInner" style="
        width: 100%;
        height: 100%;
        position: absolute;
        top: 0;
        left: 0;
        transform-origin: 0 0;
        cursor: grab;
    ">
        <img id="mapImage"
             src="<?php echo $mapImage; ?>"
             style="width:100%; height:100%; display:block;"
             alt="Map">

        <!-- Punto rojo: coordenadas NORMALIZADAS (0–1) para map.js -->
        <div id="mapMarker"
             data-px="<?php echo htmlspecialchars($px_norm, ENT_QUOTES, 'UTF-8'); ?>"
             data-py="<?php echo htmlspecialchars($py_norm, ENT_QUOTES, 'UTF-8'); ?>"
             style="
                position:absolute;
                width:14px;
                height:14px;
                background:red;
                border-radius:50%;
                border:2px solid white;
                left:0;
                top:0;
             ">
        </div>
    </div>

</div>

<?php else: ?>

<p>Mapa no soportado.</p>

<?php endif; ?>

