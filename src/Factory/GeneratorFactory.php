<?php

namespace LiamH\Valueobjectgenerator\Factory;

use LiamH\Valueobjectgenerator\Generator\FileGenerator;
use LiamH\Valueobjectgenerator\Generator\SourceGenerator;

interface GeneratorFactory
{
    public function createSourceGenerator(): SourceGenerator;
    public function createFileGenerator(): FileGenerator;
}
