<?php
session_start();

require_once __DIR__ . "/config.php";
require_once __DIR__ . "/langs.php";
require_once __DIR__ . "/maps.php";
require_once __DIR__ . "/zones.php";
require_once __DIR__ . "/includes/functions.php";

/* ============================
   SELECCIÓN DE IDIOMA
   ============================ */

if (isset($_GET['lang']) && isset($langs[$_GET['lang']])) {
    $_SESSION['lang'] = $_GET['lang'];
}

$currentLangCode = $_SESSION['lang'] ?? 'es';
$lang = $langs[$currentLangCode];

/* ============================
   CONEXIÓN BD AUTH
   ============================ */

$db = connectAuthDB($config);

/* ============================
   LOGOUT
   ============================ */

if (isset($_POST['logout'])) {
    unset($_SESSION['account'], $_SESSION['characters']);
    header("Location: ?lang=" . $currentLangCode);
    exit;
}

/* ============================
   SESIÓN ACTUAL
   ============================ */

$account    = $_SESSION['account']    ?? null;
$characters = $_SESSION['characters'] ?? [];

/* ============================
   LOGIN
   ============================ */

if (!$account && isset($_POST['username'], $_POST['password'])) {
    $loginResult = loginAccount($_POST['username'], $_POST['password'], $db, $config);

    if ($loginResult['success']) {
        header("Location: ?lang=" . $currentLangCode);
        exit;
    } else {
        $message = $loginResult['message'];
    }
}

/* ============================
   BUSCAR CUENTAS POR EMAIL
   ============================ */

$searchResults = [];

if ($account && isset($_POST['search_email'])) {
    $searchResults = searchByEmail($_POST['search_email'], $db);
}

?>
<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8">
<title>Liberador TrinityCore</title>

<link rel="stylesheet" href="assets/css/liberador.css?v=8">
<script src="assets/js/liberador.js?v=8"></script>

</head>
<body>

<!-- FIX: aplicar tema antes del render -->
<script>
(function() {
    const theme = localStorage.getItem("theme") || "dark";
    if (theme === "light") {
        document.documentElement.classList.add("light");
        document.body.classList.add("light");
    }
})();
</script>

<div class="layout">

    <!-- ============================
         PANEL IZQUIERDO
         ============================ -->
    <div class="left-panel">

        <!-- HEADER UNIVERSAL -->
        <div class="global-header">

            <!-- Título solo si hay cuenta -->
            <?php if ($account): ?>
                <h2 class="account-title"><?php echo $lang['account_data']; ?></h2>
            <?php else: ?>
                <h2 class="account-title"></h2>
            <?php endif; ?>

            <div class="header-controls">

                <!-- Botón tema -->
                <button class="theme-toggle" onclick="toggleTheme()">🌓</button>

                <!-- Selector de idioma -->
                <div class="lang-selector header-lang">
                    <div class="lang-current" onclick="toggleLangMenu()">
                        <img src="<?php echo $langs[$currentLangCode]['flag']; ?>" class="lang-flag">
                        <?php echo $langs[$currentLangCode]['name']; ?>
                        <span class="lang-arrow">▼</span>
                    </div>

                    <div id="lang-menu" class="lang-menu">
                        <?php foreach ($langs as $code => $l): ?>
                            <a href="?lang=<?php echo $code; ?>">
                                <img src="<?php echo $l['flag']; ?>" class="lang-flag">
                                <?php echo $l['name']; ?>
                            </a>
                        <?php endforeach; ?>
                    </div>
                </div>

            </div>
        </div>

        <?php if (!$account): ?>

            <?php include __DIR__ . "/includes/login_form.php"; ?>

        <?php else: ?>

            <?php include __DIR__ . "/includes/account_info.php"; ?>

            <h2><?php echo $lang['characters']; ?></h2>

            <ul class="character-list">
            <?php foreach ($characters as $c): ?>

                <?php
                $raceIcon  = "assets/icons/races/" . raceIcon($c['race'], $c['gender']) . "";
                $classIcon = "assets/icons/classes/" . classIcon($c['class']);
                ?>

                <li class="character-item <?php echo $c['online'] ? 'online' : ''; ?>"
                    data-guid="<?php echo $c['guid']; ?>"
                    onclick="loadCharacterRender(<?php echo $c['guid']; ?>)">

                    <div class="char-icons">
                        <img src="<?php echo $raceIcon; ?>" width="40" height="40" alt="">
                        <img src="<?php echo $classIcon; ?>" width="40" height="40" alt="">
                    </div>

                    <div class="char-info <?php echo $c['online'] ? 'online-block' : ''; ?>">
                        <strong><?php echo htmlspecialchars($c['name']); ?></strong>
                        (Nivel <?php echo (int)$c['level']; ?>)
                        <br>
                        <?php echo $lang['map']; ?>:
                        <?php echo mapName((int)$c['map'], $mapNames); ?><br>
                        <?php echo $lang['zone']; ?>:
                        <?php echo zoneName((int)$c['zone'], $zoneNames); ?><br>
                        <?php echo $lang['played']; ?>:
                        <?php echo formatPlaytime((int)$c['totaltime']); ?><br>

                        <?php if (!$c['online']): ?>
                            <form method="POST" action="unstuck.php" style="display:inline;">
                                <input type="hidden" name="guid" value="<?php echo (int)$c['guid']; ?>">
                                <button type="submit"><?php echo $lang['unstuck']; ?></button>
                            </form>
                        <?php else: ?>
                            <strong><?php echo $lang['online']; ?></strong>
                        <?php endif; ?>
                    </div>

                </li>

            <?php endforeach; ?>
            </ul>

        <?php endif; ?>

    </div>

    <!-- ============================
         PANEL DERECHO (RENDER + BÚSQUEDA)
         ============================ -->
    <div id="render-panel" class="right-panel"
         style="display:<?php echo (!empty($searchResults) || isset($_POST['search_email'])) ? 'block' : 'none'; ?>;">

        <div id="render-output" class="render-box">

            <?php if (!empty($searchResults)): ?>

                <h3><?php echo $lang['results']; ?></h3>

                <ul class="search-results">
                    <?php foreach ($searchResults as $r): ?>
                        <li class="search-item">

                            <div class="search-line1">
                                <span class="private">
                                    <strong><?php echo htmlspecialchars($r['username']); ?></strong>
                                    (<?php echo htmlspecialchars($r['email']); ?>)
                                </span>
                            </div>

                            <div class="search-line2">
                                <?php echo htmlspecialchars($r['joindate']); ?>
                                —
                                <span class="private"><?php echo htmlspecialchars($r['last_ip']); ?></span>
                            </div>

                        </li>
                    <?php endforeach; ?>
                </ul>

            <?php elseif (isset($_POST['search_email'])): ?>

                <p><?php echo $lang['no_results']; ?></p>

            <?php endif; ?>

        </div>
    </div>

</div>

</body>
</html>

