<?php
session_start();

require_once __DIR__ . "/config.php";
require_once __DIR__ . "/includes/functions.php";
require_once __DIR__ . "/langs.php";

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
if (!$guid) {
    exit("Invalid GUID");
}

$char = null;
foreach ($_SESSION['characters'] as $c) {
    if ((int)$c['guid'] === $guid) {
        $char = $c;
        break;
    }
}

if (!$char) {
    exit("Character not found");
}

/* ============================
   COLORES DE CLASE (OFICIALES)
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
   ICONOS USANDO TUS FUNCIONES
   ============================ */
$raceIconFile  = raceIcon($char['race'], $char['gender']);
$classIconFile = classIcon($char['class']);

$raceIconPath  = "/tools/liberador/assets/icons/races/"  . $raceIconFile;
$classIconPath = "/tools/liberador/assets/icons/classes/" . $classIconFile;

/* FACCIÓN */
$faction = in_array($char['race'], [1,3,4,7,11]) ? "alliance" : "horde";
$factionIcon = "/tools/liberador/assets/icons/factions/" . $faction . ".png";

/* ============================
   TRADUCCIONES USANDO TUS FUNCIONES
   ============================ */
$raceText  = $lang[raceName($char['race'])]  ?? $char['race'];
$classText = $lang[className($char['class'])] ?? $char['class'];

$genderText = ($char['gender'] == 0) ? $lang['male'] : $lang['female'];
$onlineText = $char['online'] ? $lang['yes'] : $lang['no'];

/* COLOR DE CLASE */
$classColor = $classColors[$char['class']] ?? "#FFFFFF";

?>
<div class="char-render">

    <h2 style="color: <?php echo $classColor; ?>">
        <?php echo htmlspecialchars($char['name']); ?> (<?php echo (int)$char['level']; ?>)
    </h2>

    <div style="display:flex; gap:15px; align-items:center; margin-bottom:10px;">
        <img src="<?php echo $raceIconPath; ?>" width="64">
        <img src="<?php echo $classIconPath; ?>" width="64">
        <img src="<?php echo $factionIcon; ?>" width="64">
    </div>

    <p><strong><?php echo $lang['race']; ?>:</strong> <?php echo $raceText; ?></p>
    <p><strong><?php echo $lang['class']; ?>:</strong> <?php echo $classText; ?></p>
    <p><strong><?php echo $lang['gender']; ?>:</strong> <?php echo $genderText; ?></p>
    <p><strong><?php echo $lang['online']; ?>:</strong> <?php echo $onlineText; ?></p>

</div>

