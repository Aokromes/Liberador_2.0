<?php
session_start();

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

$config     = require __DIR__ . '/config.php';
$zoneNames  = require __DIR__ . '/zones.php';
$mapNames   = require __DIR__ . '/maps.php';
$langs      = require __DIR__ . '/langs.php';

// Selección de idioma sin relogear
if (isset($_GET['lang']) && isset($langs[$_GET['lang']])) {
    $_SESSION['lang'] = $_GET['lang'];
}
$lang = $langs[$_SESSION['lang'] ?? 'es'];

// Conexión BD auth
$db = new PDO(
    "mysql:host={$config['auth']['host']};dbname={$config['auth']['dbname']}",
    $config['auth']['user'],
    $config['auth']['pass'],
    $config['pdo_options']
);

// Logout
if (isset($_POST['logout'])) {
    unset($_SESSION['account']);
    unset($_SESSION['characters']);
}

// Si ya está logeado, no volver a pedir login
$account    = $_SESSION['account'] ?? null;
$characters = $_SESSION['characters'] ?? [];

// SRP6v2
function verifySRP6v2($username, $password, $salt_bin, $verifier_bin)
{
    $g = gmp_init(7);
    $N = gmp_init("894B645E89E1535BBDAD5B8B290650530801B18EBFBF5E8FAB3C82872A3E9BB7", 16);

    $h1 = sha1(strtoupper($username . ":" . $password), true);
    $h2 = sha1($salt_bin . $h1, true);

    $h2_num = gmp_import($h2, 1, GMP_LSW_FIRST);
    $v      = gmp_powm($g, $h2_num, $N);
    if ($v === false) return false;

    $v_bin = gmp_export($v, 1, GMP_LSW_FIRST);
    $v_bin = str_pad($v_bin, 32, chr(0), STR_PAD_RIGHT);

    return hash_equals($verifier_bin, $v_bin);
}

// Tiempo jugado
function formatPlaytime($seconds)
{
    $minutes = floor($seconds / 60);
    $hours   = floor($minutes / 60);
    $days    = floor($hours / 24);
    $years   = floor($days / 365);

    $minutes = $minutes % 60;
    $hours   = $hours % 24;
    $days    = $days % 365;

    $result = "";
    if ($years > 0) $result .= $years . " año" . ($years > 1 ? "s" : "") . ", ";
    if ($days > 0)  $result .= $days . " día" . ($days > 1 ? "s" : "") . ", ";

    return $result . $hours . "h " . $minutes . "m";
}

// Iconos
$raceIconPath  = "icons/races/";
$classIconPath = "icons/class/";

$raceIcons = [
    1=>"human",2=>"orc",3=>"dwarf",4=>"nightelf",5=>"undead",
    6=>"tauren",7=>"gnome",8=>"troll",10=>"bloodelf",11=>"draenei"
];

$classIcons = [
    1=>"warrior.png",2=>"paladin.png",3=>"hunter.png",4=>"rogue.png",
    5=>"priest.png",6=>"deathknight.png",7=>"shaman.png",
    8=>"mage.png",9=>"warlock.png",11=>"druid.png"
];

$message = "";

// LOGIN (solo si no está logeado)
if (!$account && isset($_POST['username'], $_POST['password'])) {

    $user = $_POST['username'];
    $pass = $_POST['password'];

    $stmt = $db->prepare("
        SELECT id, salt, verifier, last_ip, email, joindate
        FROM account
        WHERE username = UPPER(?)
    ");
    $stmt->execute([$user]);
    $row = $stmt->fetch();

    if (!$row) {
        $message = "Usuario incorrecto";
    } else {
        if (verifySRP6v2($user, $pass, $row['salt'], $row['verifier'])) {

            $_SESSION['account'] = [
                'id'       => $row['id'],
                'username' => $user,
                'email'    => $row['email'],
                'last_ip'  => $row['last_ip'],
                'joindate' => $row['joindate']
            ];

            // Cargar personajes
            $charsDb = new PDO(
                "mysql:host={$config['characters']['host']};dbname={$config['characters']['dbname']}",
                $config['characters']['user'],
                $config['characters']['pass'],
                $config['pdo_options']
            );

            $stmt = $charsDb->prepare("
                SELECT guid, name, race, class, gender, level, online, map, zone, totaltime, logout_time
                FROM characters
                WHERE account = ?
            ");
            $stmt->execute([$row['id']]);
            $_SESSION['characters'] = $stmt->fetchAll();

            header("Location: ?lang=" . ($_SESSION['lang'] ?? 'es'));
            exit;

        } else {
            $message = "Contraseña incorrecta";
        }
    }
}

// Buscador de cuentas por email (NO toca login)
$searchResults = [];
if ($account && isset($_POST['search_email'])) {

    $stmt = $db->prepare("
        SELECT id, username, email, joindate, last_ip
        FROM account
        WHERE email = ?
    ");
    $stmt->execute([$_POST['search_email']]);
    $searchResults = $stmt->fetchAll();
}
?>
<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8">
<title>Liberador TrinityCore</title>

<style>
body { background:#f5f5f5; color:#222; font-family:Arial; }
@media (prefers-color-scheme: dark) {
    body { background:#121212; color:#e0e0e0; }
    input,button { background:#1e1e1e; color:#fff; border:1px solid #444; }
}

/* Datos privados */
.private { filter:blur(6px); transition:0.2s; cursor:pointer; }
.private:hover { filter:blur(0); }

/* Fondo del personaje conectado */
.online {
    background:#c8f7c5 !important;
    border:1px solid #7bd67b !important;
    border-radius:6px;
}

/* Texto del personaje conectado */
.online-block,
.online-block * {
    color:#003300 !important;
    font-weight:bold;
    text-shadow:none !important;
}
</style>

</head>
<body>

<div style="text-align:right; margin-bottom:10px;">
    <form method="GET" style="display:inline;">
        <label for="langSelect">🌐</label>
        <select name="lang" id="langSelect" onchange="this.form.submit()"
                style="padding:4px; font-size:14px;">
            <?php foreach ($langs as $code => $data): ?>
                <option value="<?php echo $code; ?>"
                    <?php echo ($_SESSION['lang'] ?? 'es') === $code ? 'selected' : ''; ?>>
                    <?php echo $data['name']; ?>
                </option>
            <?php endforeach; ?>
        </select>
    </form>
    <img src="<?php echo $langs[$_SESSION['lang'] ?? 'es']['flag']; ?>" 
         alt="flag" width="24" height="24" 
         style="vertical-align:middle; margin-left:6px;">
</div>

<p><?php echo $message; ?></p>

<?php if (!$account): ?>

<form method="POST">
    <label><?php echo $lang['user']; ?>:
        <input type="text" name="username">
    </label><br>

    <label><?php echo $lang['pass']; ?>:
        <input type="password" name="password">
    </label><br>

    <button type="submit"><?php echo $lang['login']; ?></button>
</form>

<?php else: ?>

<h2><?php echo $lang['account_data']; ?></h2>

<?php echo $lang['user']; ?>:
<span class="private"><?php echo htmlspecialchars($account['username']); ?></span><br>

<?php echo $lang['email']; ?>:
<span class="private" onclick="document.getElementById('emailSearchBox').style.display='block';">
    <?php echo htmlspecialchars($account['email']); ?>
</span><br>

<div id="emailSearchBox" style="display:none; margin-top:10px; padding:10px; border:1px solid #666;">
    <h3><?php echo $lang['search_email']; ?></h3>
    <form method="POST">
        <input type="hidden" name="search_email" value="<?php echo htmlspecialchars($account['email']); ?>">
        <button type="submit"><?php echo $lang['search']; ?></button>
    </form>
</div>

<?php echo $lang['last_ip']; ?>:
<span class="private"><?php echo $account['last_ip']; ?></span><br>

<?php echo $lang['register_date']; ?>:
<span class="private"><?php echo $account['joindate']; ?></span><br>

<form method="POST" style="margin-top:10px;">
    <input type="hidden" name="logout" value="1">
    <button type="submit"><?php echo $lang['logout']; ?></button>
</form>

<?php if ($searchResults): ?>
<h3><?php echo $lang['other_accounts']; ?></h3>
<ul>
<?php foreach ($searchResults as $acc): ?>
    <li>
        <strong>ID:</strong> <?php echo $acc['id']; ?> —
        <strong>User:</strong> <?php echo $acc['username']; ?> —
        <strong>Email:</strong> <?php echo $acc['email']; ?> —
        <strong><?php echo $lang['register_date']; ?>:</strong> <?php echo $acc['joindate']; ?> —
        <strong><?php echo $lang['last_ip']; ?>:</strong> <?php echo $acc['last_ip']; ?>
    </li>
<?php endforeach; ?>
</ul>
<hr>
<?php endif; ?>

<h2><?php echo $lang['characters']; ?></h2>
<ul>
<?php foreach ($characters as $c): ?>

<?php
$raceIcon = $raceIconPath . "races_" . $raceIcons[$c['race']] . "_" . ($c['gender'] ? "female" : "male") . ".png";
$classIcon = $classIconPath . $classIcons[$c['class']];
$mapName = $mapNames[$c['map']] ?? "Mapa desconocido ({$c['map']})";
$zoneName = $zoneNames[$c['zone']] ?? "Zona desconocida ({$c['zone']})";
$playtime = formatPlaytime($c['totaltime']);
$logout = $c['logout_time'] ? date("Y-m-d H:i", $c['logout_time']) : "Nunca";
?>

<li class="<?php echo $c['online'] ? 'online' : ''; ?>" 
    style="margin-bottom:12px; padding:10px; display:flex; gap:12px; align-items:flex-start;">

    <div style="width:90px; display:flex; flex-direction:column; gap:4px;">
        <img src="<?php echo $raceIcon; ?>" width="40">
        <img src="<?php echo $classIcon; ?>" width="40">
    </div>

    <div class="<?php echo ($c['online'] ? 'online-block' : ''); ?>" style="line-height:18px;">

        <strong><?php echo $c['name']; ?></strong>
        (Nivel <?php echo $c['level']; ?>) —

        <?php if ($c['online']): ?>
            <strong><?php echo $lang['online']; ?></strong>
        <?php else: ?>
            <span style="color:gray;"><?php echo $lang['offline']; ?></span>
            <form method="POST" action="unstuck.php" style="display:inline;">
                <input type="hidden" name="guid" value="<?php echo $c['guid']; ?>">
                <button type="submit"><?php echo $lang['unstuck']; ?></button>
            </form>
        <?php endif; ?>

        <br>
        <?php echo $lang['map']; ?>: <?php echo $mapName; ?><br>
        <?php echo $lang['zone']; ?>: <?php echo $zoneName; ?><br>
        <?php echo $lang['played']; ?>: <?php echo $playtime; ?><br>
        <?php echo $lang['last_logout']; ?>: <?php echo $logout; ?>
    </div>

</li>

<?php endforeach; ?>
</ul>

<?php endif; ?>

</body>
</html>

