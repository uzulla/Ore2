<?php
include 'vendor/autoload.php';

$t = new \Ore2\Template([
    'template_dir' => realpath(__DIR__ . '/template')
]);

$params = [
    "name" => 'uzulla',
    "list" => [1,2,3]
];

$html = $t->render('index.twig', $params);

echo $html;