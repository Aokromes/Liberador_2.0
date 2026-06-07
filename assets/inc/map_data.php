<?php

//
// map_data.php — versión exacta del MiniManager
// Convierte coordenadas del mundo (X,Y,mapId) en coordenadas de mapa (px,py)
//

function get_player_position($x, $y, $m)
{
    $pos = ['x' => 0, 'y' => 0];
    $where_530 = 0;

    // Redondeo igual que el MiniManager
    $x = round($x);
    $y = round($y);

    // -------------------------------
    // MAPA 530 — CASOS ESPECIALES
    // -------------------------------
    if ($m == 530) {

        // Blood Elf (Eversong / Silvermoon)
        if ($y < -1000 && $y > -10000 && $x > 5000) {
            $x = $x - 10349;
            $y = $y + 6357;
            $where_530 = 1;
        }
        // Draenei (Azuremyst / Bloodmyst)
        else if ($y < -7000 && $x < 0) {
            $x = $x + 3961;
            $y = $y + 13931;
            $where_530 = 2;
        }
        // Outland normal
        else {
            $x = $x - 3070;
            $y = $y - 1265;
            $where_530 = 3;
        }
    }

    // -------------------------------
    // MAPA 609 — Acherus
    // -------------------------------
    if ($m == 609) {
        $x = $x - 2355;
        $y = $y + 5662;
    }

    // -------------------------------
    // ESCALADO POR CONTINENTE
    // -------------------------------
    if ($where_530 == 3) { // Outland
        $xpos = round($x * 0.051446);
        $ypos = round($y * 0.051446);
    }
    else if ($m == 571) { // Northrend
        $xpos = round($x * 0.047055);
        $ypos = round($y * 0.047055);
    }
    else { // Azeroth (EK, Kalimdor, Silvermoon, Draenei)
        $xpos = round($x * 0.025140);
        $ypos = round($y * 0.025140);
    }

    // -------------------------------
    // PROYECCIÓN FINAL POR MAPA
    // -------------------------------
    switch ($m) {

        case 530:
            if ($where_530 == 1) { // Silvermoon
                $pos['x'] = 858 - $ypos;
                $pos['y'] = 84  - $xpos;
            }
            else if ($where_530 == 2) { // Draenei
                $pos['x'] = 103 - $ypos;
                $pos['y'] = 261 - $xpos;
            }
            else { // Outland
                $pos['x'] = 684 - $ypos;
                $pos['y'] = 229 - $xpos;
            }
            break;

        case 571: // Northrend
            $pos['x'] = 515 - $ypos;
            $pos['y'] = 644 - $xpos;
            break;

        case 609: // Acherus
            $pos['x'] = 896 - $ypos;
            $pos['y'] = 232 - $xpos;
            break;

        case 1: // Kalimdor
            $pos['x'] = 194 - $ypos;
            $pos['y'] = 398 - $xpos;
            break;

        case 0: // Eastern Kingdoms
            $pos['x'] = 752 - $ypos;
            $pos['y'] = 291 - $xpos;
            break;

        default: // fallback
            $pos['x'] = 194 - $ypos;
            $pos['y'] = 398 - $xpos;
    }

    return $pos;
}

?>

