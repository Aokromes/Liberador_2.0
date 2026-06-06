<?php

$map_data = array(
    0 => array( // Azeroth
        'image'  => 'azeroth.jpg',
        'x_min'  => -17066.666,
        'x_max'  =>  17066.666,
        'y_min'  => -17066.666,
        'y_max'  =>  17066.666,
    ),
    1 => array( // Kalimdor
        'image'  => 'kalimdor.jpg',
        'x_min'  => -17066.666,
        'x_max'  =>  17066.666,
        'y_min'  => -17066.666,
        'y_max'  =>  17066.666,
    ),
    530 => array( // Outland
        'image'  => 'outland.jpg',
        'x_min'  => -17066.666,
        'x_max'  =>  17066.666,
        'y_min'  => -17066.666,
        'y_max'  =>  17066.666,
    ),
    571 => array( // Northrend
        'image'  => 'northrend.jpg',
        'x_min'  => -17066.666,
        'x_max'  =>  17066.666,
        'y_min'  => -17066.666,
        'y_max'  =>  17066.666,
    ),
);

function worldToMap($mapId, $x, $y, $map_data, $img_width, $img_height)
{
    if (!isset($map_data[$mapId])) {
        return array(0, 0);
    }

    $d = $map_data[$mapId];

    $px = ($x - $d['x_min']) / ($d['x_max'] - $d['x_min']) * $img_width;
    $py = (1 - ($y - $d['y_min']) / ($d['y_max'] - $d['y_min'])) * $img_height;

    return array($px, $py);
}

