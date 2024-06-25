<?php

require 'src/NanoChart.php';

$items = [
    'Monday' => 10,
    'Tuesday' => 20,
    'Wednesday' => 30
];

$palette = [
    'hsl(355, 90%, 65%)',
    'hsl(37, 100%, 70%)',
    'hsl(140, 70%, 65%)'
];

$nanoChart = new NanoChart($counts);
$nanoChart->setSize(250);
$nanoChart->setDirection('row');
$nanoChart->setStyle('doughnut');
$nanoChart->setPalette($palette);
$chart = $nanoChart->build();

echo $chart;
