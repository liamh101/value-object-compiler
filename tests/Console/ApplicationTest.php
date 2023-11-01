<?php

namespace Console;

use LiamH\Valueobjectgenerator\Console\Application;
use LiamH\Valueobjectgenerator\Console\Command\CompileFromJson;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Command\Command;

class ApplicationTest extends TestCase
{
    public function testApplicationBoot(): void
    {
        $application = new Application();

        self::assertInstanceOf(CompileFromJson::class, $application->find( 'generate:json'));
    }
}