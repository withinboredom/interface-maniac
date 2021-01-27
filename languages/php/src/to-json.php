<?php

require_once __DIR__.'/../vendor/autoload.php';

$input = file_get_contents('php://stdin');

$parser = (new \PhpParser\ParserFactory())->create(\PhpParser\ParserFactory::PREFER_PHP7);

$ast   = $parser->parse($input);
$json  = [
    'methods' => [],
];
$using = [];

function get_dot_name(?string $name): ?string
{
    if ($name === null) {
        return null;
    }

    return str_replace('\\', '.', $name);
}

function get_full_name(?array $parts): ?string
{
    global $using;
    if ($parts === null || $parts[0] === null) {
        return null;
    }
    if (count($parts) === 1) {
        return '\\'.$parts[0];
    }
    $name = implode('\\', $parts);
    if (isset($using[$name])) {
        return '\\'.$using[$name];
    }

    return $name;
}

function without_dot(string $name ) {
    return str_starts_with($name, '.') ? substr($name, 1) : $name;
}

$traverser = new \PhpParser\NodeTraverser();
$traverser->addVisitor(
    new class extends \PhpParser\NodeVisitorAbstract {
        public function leaveNode(\PhpParser\Node $node)
        {
            global $json, $using;
            if ($node instanceof \PhpParser\Node\Stmt\Namespace_) {
                $using['root']     = without_dot(get_dot_name(get_full_name($node->name->parts)));
                $json['namespace'] = without_dot(get_dot_name(get_full_name($node->name->parts)));
            }
            if ($node instanceof \PhpParser\Node\Stmt\Use_) {
                foreach ($node->uses as $use) {
                    $name         = get_dot_name(get_full_name([$use->alias])) ?? get_dot_name(
                            get_full_name([end($use->name->parts)])
                        );
                    $using[$name] = without_dot(get_dot_name(get_full_name($use->name->parts)));
                }
            }
            if ($node instanceof \PhpParser\Node\Stmt\Interface_) {
                $json['name'] = $node->name->name;
                foreach ($node->getMethods() as $method) {
                    $name       = $method->name->name;
                    $parameters = [];
                    foreach ($method->getParams() as $param) {
                        $parameters[] = [
                            'name'         => $param->var->name,
                            'type'         => $param->type->name ?? get_dot_name(get_full_name($param->type?->parts)),
                            'defaultValue' => $param->default ? json_encode($param->default?->value) : null,
                        ];
                    }
                    $return_type       = $method->getReturnType()->name ?? get_dot_name(
                            get_full_name($method->getReturnType()?->parts)
                        );
                    $json['methods'][] = [
                        'name'       => $name,
                        'parameters' => $parameters,
                        'returnType' => $return_type,
                    ];
                }
            }

            return $node;
        }
    }
);
$traverser->traverse($ast);

array_walk_recursive(
    $json,
    function (&$elem, $key) {
        global $using;;
        if (isset($using[$elem])) {
            $elem = $using[$elem];
        }

        if(str_starts_with($elem, '.')) {
            $elem = $using['root'].$elem;
        }
    }
);

echo json_encode($json, JSON_PRETTY_PRINT);
