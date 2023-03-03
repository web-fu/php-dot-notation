<?php

declare(strict_types=1);

namespace WebFu\Tests\Fixture;

class ParentClass
{
    public string $publicParent;
    protected string $protectedParent;
    private string $privateParent;

    public function publicParent(): void
    {
    }

    protected function protectedParent(): void
    {
    }

    private function privateParent(): void
    {
    }
}
