<?php

namespace LiamH\Valueobjectgenerator\Console;

use LiamH\Valueobjectgenerator\Console\Command\CompileFromJson;
use LiamH\Valueobjectgenerator\Factory\JsonGeneratorCommandFactory;
use Symfony\Component\Console\Application as BaseApplication;

class Application extends BaseApplication
{
    private const VERSION = '0.1.0';

    public function __construct()
    {
        parent::__construct('Value Object Compiler', self::VERSION);

        $this->add(new CompileFromJson(null, new JsonGeneratorCommandFactory()));
    }
}
