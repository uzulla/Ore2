<?php
include 'vendor/autoload.php';

$t = new \Ore2\Template([
    'template_dir' => realpath(__DIR__ . '/template')
]);

$params = [
    "name" => 'uzulla',
    "list" => [1,2,3]
];

$code = $t->parse('index.twig');
file_put_contents('code.php', $code);
$html = $t->execute($code, $params);
file_put_contents('out.html', $html);
