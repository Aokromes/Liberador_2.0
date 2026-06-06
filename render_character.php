<?php
session_start();

require_once __DIR__ . "/config.php";
require_once __DIR__ . "/includes/functions.php";
require_once __DIR__ . "/langs.php";
require_once __DIR__ . "/maps.php";
require_once __DIR__ . "/zones.php";
require_once __DIR__ . "/assets/inc/map_data.php";

/* ============================
   CARGAR IDIOMA
   ============================ */
$lang = $langs[$_SESSION['lang']];

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
   MAPA INTERACTIVO
   ============================ */
$posX = $char['position_x'];
$posY = $char['position_y'];

$px = 0;
$py = 0;

if (isset($map_data[$mapId])) {
    list($px, $py) = worldToMap($mapId, $posX, $posY, $map_data, 1024, 768);
}
?>

<div class="char-render">

    <h2 style="color: <?php echo $classColors[$char['class']] ?? "#FFFFFF"; ?>">
        <?php echo htmlspecialchars($char['name']); ?> (<?php echo (int)$char['level']; ?>)
        <strong>Hermandad:</strong> <?php echo $char['guild_name'] ?: 'Sin hermandad'; ?>
    </h2>

    <div style="display:flex; gap:15px; align-items:center; margin-bottom:10px;">
        <img src="<?php echo $raceIconPath; ?>" width="64">
        <img src="<?php echo $classIconPath; ?>" width="64">
        <img src="<?php echo $factionIcon; ?>" width="64">
    </div>

    <p><strong><?php echo $lang['race']; ?>:</strong> <?php echo $raceText; ?></p>
    <p><strong><?php echo $lang['class']; ?>:</strong> <?php echo $classText; ?></p>
    <p><strong><?php echo $lang['gender']; ?>:</strong> <?php echo $genderText; ?></p>
    <p><strong>Dinero:</strong> <?php echo "{$gold}g {$silver}s {$copper}c"; ?></p>
    <p><strong>Mapa:</strong> <?php echo htmlspecialchars($mapName); ?></p>
    <p><strong>Zona:</strong> <?php echo htmlspecialchars($zoneName); ?></p>
    <p><strong>Coordenadas:</strong>
        X: <?php echo $posX; ?>,
        Y: <?php echo $posY; ?>,
        Z: <?php echo $char['position_z']; ?>
    </p>

    <p><strong><?php echo $lang['online']; ?>:</strong> <?php echo $onlineText; ?></p>

</div>

<?php if (isset($map_data[$mapId])): ?>

<div id="mapWrapper" style="width:1024px; height:768px; position:relative; overflow:hidden;">

    <div id="mapInner"
         data-px="<?php echo $px / 1024; ?>"
         data-py="<?php echo $py / 768; ?>"
         style="
            width:100%;
            height:100%;
            background-image:url('/tools/liberador/assets/img/<?php echo $map_data[$mapId]['image']; ?>');
            background-size:contain;
            background-repeat:no-repeat;
            background-position:0 0;
            cursor:grab;
            position:relative;
         ">

        <div id="mapMarker" style="
            position:absolute;
            width:14px;
            height:14px;
            background:red;
            border-radius:50%;
            border:2px solid white;
        "></div>

    </div>

</div>

<?php else: ?>

<p>Mapa no soportado por MiniManager.</p>

<?php endif; ?>

