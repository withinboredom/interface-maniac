<?php

require_once __DIR__.'/../vendor/autoload.php';

['namespace' => $namespace, 'name' => $name, 'methods' => $methods] = json_decode(
    file_get_contents('php://stdin'),
    true
);

$usings = [];

function make_php_name(?string $name, bool $can_use = true): ?string
{
    global $usings;

    if($can_use && str_contains($name, '.')) {
        return $usings[] = make_php_name($name, false);
    }

    return $name ? str_replace('.', '\\', $name) : $name;
}

$file      = new \Nette\PhpGenerator\PhpFile();
$namespace = $file->addNamespace(make_php_name($namespace, false));
$class     = $namespace->addClass($name);
$class->setInterface();

foreach ($methods as $method) {
    $m = $class->addMethod($method['name'])->setReturnType(make_php_name($method['returnType']));
    foreach ($method['parameters'] as $parameter) {
        $p = $m->addParameter($parameter['name'])->setType(make_php_name($parameter['type']));
        if ($parameter['defaultValue'] !== null) {
            $p->setDefaultValue(json_decode($parameter['defaultValue'], true));
        }
    }
}

foreach(array_filter($usings) as $using) {
    $namespace->addUse($using);
}

echo $file;
