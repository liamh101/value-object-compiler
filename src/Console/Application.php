<?php

namespace LiamH\ValueObjectCompiler\Console;

use LiamH\ValueObjectCompiler\Console\Command\CompileFromJson;
use LiamH\ValueObjectCompiler\Console\Command\CompileFromXml;
use LiamH\ValueObjectCompiler\Factory\JsonGeneratorCommandFactory;
use LiamH\ValueObjectCompiler\Factory\XmlGeneratorCommandFactory;
use Symfony\Component\Console\Application as BaseApplication;

class Application extends BaseApplication
{
    private const VERSION = '0.1.0';

    public function __construct()
    {
        parent::__construct('Value Object Compiler', self::VERSION);

        $this->add(new CompileFromJson(null, new JsonGeneratorCommandFactory()));
        $this->add(new CompileFromXml(null, new XmlGeneratorCommandFactory()));
    }
}
