<?php

namespace LiamH\Valueobjectgenerator\Console;

use LiamH\Valueobjectgenerator\Console\Command\GenerateFromJson;
use LiamH\Valueobjectgenerator\Factory\GeneratorCommandFactory;
use Symfony\Component\Console\Application as BaseApplication;

class Application extends BaseApplication
{
    private const VERSION = '0.0.0';

    public function __construct()
    {
        parent::__construct('Value Object Generator', self::VERSION);

        $this->add(new GenerateFromJson(null, new GeneratorCommandFactory()));
    }
}
