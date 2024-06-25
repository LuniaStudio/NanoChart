<?php

/**
 * Include the NanoChart class in your script.
 **/

require 'src/NanoChart.php';

/**
 * Create an associative array of items and amounts.
 **/

$items = [
    'Monday' => 10,
    'Tuesday' => 20,
    'Wednesday' => 30
];

/**
 * 1. Basic usage.
 **/

$nanoChart = new NanoChart($items);
$html = $nanoChart->build();

/**
 * 2. Set your own colour hue value and NanoChart will create a palette.
 **/

$nanoChart = new NanoChart($items);
$nanoChart->setHue(150);
$html = $nanoChart->build();

/**
 * 3. Pass in your own colour palette.
 **/

$palette = [
    'hsl(355, 90%, 65%)',
    'hsl(37, 100%, 70%)',
    'hsl(140, 70%, 65%)'
];

$nanoChart = new NanoChart($items);
$nanoChart->setPalette($palette);
$html = $nanoChart->build();

/**
 * 4. Set the size of the chart in pixels, excluding the legend.
 **/

$nanoChart = new NanoChart($items);
$nanoChart->setSize(250);
$html = $nanoChart->build();

/**
 * 5. Place the legend to the right or below the chart.
 **/

$nanoChart = new NanoChart($items);
$nanoChart->setDirection('row');
$html = $nanoChart->build();

/**
 * 6. Change the chart from the default 'pie' to 'doughnut'.
 **/

$nanoChart = new NanoChart($items);
$nanoChart->setStyle('doughnut');
$html = $nanoChart->build();

/**
 * 7. Set the slice origin point to the top, right, bottom or left side of the chart.
 **/

$nanoChart = new NanoChart($items);
$nanoChart->setStartPosition('top');
$html = $nanoChart->build();
