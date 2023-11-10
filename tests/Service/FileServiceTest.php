<?php

namespace Service;

use LiamH\ValueObjectCompiler\Enum\FileExtension;
use LiamH\ValueObjectCompiler\Exception\FileException;
use LiamH\ValueObjectCompiler\Service\FileService;
use LiamH\ValueObjectCompiler\ValueObject\GeneratedFile;
use PHPUnit\Framework\TestCase;

class FileServiceTest extends TestCase
{
    private const TEST_FILE_LOCATION = './build/';

    public function testInvalidOutputDir(): void
    {
        $this->expectException(FileException::class);
        $this->expectExceptionMessage('Invalid output directory provided');

        new FileService('Invalid');
    }

    public function testGetValueObjectFile(): void
    {
        $reflection = new \ReflectionClass(FileService::class);
        $method = $reflection->getMethod('getValueObjectFile');
        $method->setAccessible(true);

        $service = $this->createService();
        $result = $method->invoke($service);

        self::assertSame($this->getSubContents(), $result);
    }

    public function testGetCachedValueObjectFile(): void
    {
        $reflection = new \ReflectionClass(FileService::class);
        $method = $reflection->getMethod('getValueObjectFile');
        $method->setAccessible(true);

        $service = $this->createService();
        $method->invoke($service);
        $result = $method->invoke($service);

        self::assertSame($this->getSubContents(), $result);
    }

    private function getSubContents(): string
    {
        return '<?php

readonly class {{ClassName}}
{

    {{Docblock}}
    public function __construct(
        {{Parameters}}
    ) {
    }

    /**
     * @return self[]
     */
    public static function hydrateMany(array $bulkData): array
    {
        $result = [];

        foreach ($bulkData as $data) {
            $result[] = self::hydrate($data);
        }

        return $result;
    }

    public static function hydrate(array $data): self
    {
        {{HydrationValidation}}
        return new self({{HydrationLogic}});
    }
}';
    }

    public function testWriteContents(): void
    {
        $file = new GeneratedFile('TestFile', 'Hello world!', FileExtension::PHP);

        $service = $this->createService();
        self::assertTrue($service->writeFile($file));
        self::assertSame('Hello world!', file_get_contents('./build/TestFile.php'));
    }

    public function testWriteInvalidContents(): void
    {
        $this->expectException(FileException::class);
        $this->expectExceptionMessage('Cannot create file TestFile');
        $file = new GeneratedFile('TestFile', 'Hello world!', FileExtension::PHP);

        $service = new FileService('./nonexistantFolder/');
        self::assertTrue($service->writeFile($file));
    }

    /**
     * @dataProvider pathProvider
     */
    public function testGetFileNameFromPath(string $path, string $expectedResult): void
    {
        $service = $this->createService();
        self::assertSame($expectedResult, $service->getFileNameFromPath($path));
    }

    public static function pathProvider(): array
    {
        return [
            'string path' => ['helloWorld.json', 'helloWorld'],
            'different extension' => ['helloWorld.xml', 'helloWorld'],
            'single depth' => ['/test/helloWorld.json', 'helloWorld'],
            'multi depth' => ['/testing/test/helloWorld.json', 'helloWorld'],
            'current start depth' => ['./testing/test/helloWorld.json', 'helloWorld'],
        ];
    }

    public function testGetFileContentsValid(): void
    {
        $service = $this->createService();

        $response = $service->getFileContentsFromPath('./tests/TestFiles/testFile.txt');

        self::assertSame('Hello world!', $response);
    }

    public function testGetFileContentsInvalid(): void
    {
        $this->expectException(FileException::class);
        $this->expectExceptionMessage('File ./tests/TestFiles/missingTestFile.txt could not be found.');
        $service = $this->createService();

        $service->getFileContentsFromPath('./tests/TestFiles/missingTestFile.txt');

    }

    public function testPopulateValueObject(): void
    {
        $expected = '<?php

readonly class ClassNameReplacement
{

    Docblock Replacement
    public function __construct(
        ParameterReplacement
    ) {
    }

    /**
     * @return self[]
     */
    public static function hydrateMany(array $bulkData): array
    {
        $result = [];

        foreach ($bulkData as $data) {
            $result[] = self::hydrate($data);
        }

        return $result;
    }

    public static function hydrate(array $data): self
    {
        HydrationValidation
        return new self(HydrationReplacement);
    }
}';

        $service = $this->createService();
        self::assertSame(
            $expected,
            $service->populateValueObjectFile(
                'ClassNameReplacement',
                'Docblock Replacement',
                'ParameterReplacement',
                'HydrationValidation',
                'HydrationReplacement',
            )
        );

    }

    private function createService(): FileService
    {
        return new FileService(self::TEST_FILE_LOCATION);
    }
}