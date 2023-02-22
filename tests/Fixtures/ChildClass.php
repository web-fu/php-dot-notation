<?php

declare(strict_types=1);

namespace WebFu\Tests\Fixtures;

class ChildClass extends ParentClass
{
    use TestTrait;

    public string $public;
    protected string $protected;
    private string $private;

    public function public(): void
    {
    }

    protected function protected(): void
    {
    }

    private function private(): void
    {
    }
}