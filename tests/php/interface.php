<?php

namespace Test\OK;

use Somewhere\Test;
use Hi;

interface Example {
    public function a(): int;
    public function b(string $a): string;
    public function c(Example $a): Test;
    public function d(): void;
    public function e();
    public function f($a);
    public function g(string $a = "ok");
    public function h(string $ok);
}
