<?php

function connectAuthDB(array $config)
{
    return new PDO(
        "mysql:host={$config['auth']['host']};dbname={$config['auth']['dbname']}",
        $config['auth']['user'],
        $config['auth']['pass'],
        $config['pdo_options']
    );
}

function connectCharactersDB(array $config)
{
    return new PDO(
        "mysql:host={$config['characters']['host']};dbname={$config['characters']['dbname']}",
        $config['characters']['user'],
        $config['characters']['pass'],
        $config['pdo_options']
    );
}

function loginAccount(string $username, string $password, PDO $db, array $config)
{
    $stmt = $db->prepare("
        SELECT id, salt, verifier, last_ip, email, joindate
        FROM account
        WHERE username = UPPER(?)
    ");
    $stmt->execute([$username]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$row) {
        return ['success' => false, 'message' => "Usuario incorrecto"];
    }

    if (!verifySRP6v2($username, $password, $row['salt'], $row['verifier'])) {
        return ['success' => false, 'message' => "Contraseña incorrecta"];
    }

    $_SESSION['account'] = [
        'id'       => $row['id'],
        'username' => $username,
        'email'    => $row['email'],
        'last_ip'  => $row['last_ip'],
        'joindate' => $row['joindate']
    ];

    $charsDb = connectCharactersDB($config);

    $stmt = $charsDb->prepare("
        SELECT guid, name, race, class, gender, level, online,
               map, zone, totaltime, logout_time
        FROM characters
        WHERE account = ?
    ");
    $stmt->execute([$row['id']]);
    $_SESSION['characters'] = $stmt->fetchAll(PDO::FETCH_ASSOC);

    return ['success' => true];
}

function verifySRP6v2($username, $password, $salt_bin, $verifier_bin)
{
    $g = gmp_init(7);
    $N = gmp_init("894B645E89E1535BBDAD5B8B290650530801B18EBFBF5E8FAB3C82872A3E9BB7", 16);

    $h1 = sha1(strtoupper($username . ":" . $password), true);
    $h2 = sha1($salt_bin . $h1, true);

    $h2_num = gmp_import($h2, 1, GMP_LSW_FIRST);
    $v = gmp_powm($g, $h2_num, $N);

    if ($v === false) return false;

    $v_bin = gmp_export($v, 1, GMP_LSW_FIRST);
    $v_bin = str_pad($v_bin, 32, chr(0), STR_PAD_RIGHT);

    return hash_equals($verifier_bin, $v_bin);
}

function searchByEmail(string $email, PDO $db)
{
    $stmt = $db->prepare("
        SELECT id, username, email, joindate, last_ip
        FROM account
        WHERE email = ?
    ");
    $stmt->execute([$email]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function raceIcon(int $race, int $gender)
{
    $races = [
        1=>"human",2=>"orc",3=>"dwarf",4=>"nightelf",5=>"undead",
        6=>"tauren",7=>"gnome",8=>"troll",10=>"bloodelf",11=>"draenei"
    ];

    return ($races[$race] ?? "human") . "_" . ($gender ? "female" : "male");
}

function classIcon(int $class)
{
    $icons = [
        1=>"warrior.png",2=>"paladin.png",3=>"hunter.png",4=>"rogue.png",
        5=>"priest.png",6=>"deathknight.png",7=>"shaman.png",
        8=>"mage.png",9=>"warlock.png",11=>"druid.png"
    ];

    return $icons[$class] ?? "warrior.png";
}

function mapName(int $id, array $mapNames)
{
    return $mapNames[$id] ?? "Mapa desconocido ($id)";
}

function zoneName(int $id, array $zoneNames)
{
    return $zoneNames[$id] ?? "Zona desconocida ($id)";
}

function formatPlaytime(int $seconds)
{
    $minutes = floor($seconds / 60);
    $hours   = floor($minutes / 60);
    $days    = floor($hours / 24);
    $years   = floor($days / 365);

    $minutes %= 60;
    $hours   %= 24;
    $days    %= 365;

    $result = "";
    if ($years > 0) $result .= "$years años, ";
    if ($days > 0)  $result .= "$days días, ";

    return $result . "{$hours}h {$minutes}m";
}

