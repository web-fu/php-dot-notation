<?php

declare(strict_types=1);

namespace WebFu\Tests\Fixture;

trait TestTrait
{
    public string $publicTrait;
    protected string $protectedTrait;
    private string $privateTrait;

    public function publicTrait(): void
    {
    }

    protected function protectedTrait(): void
    {
    }

    private function privateTrait(): void
    {
    }
}