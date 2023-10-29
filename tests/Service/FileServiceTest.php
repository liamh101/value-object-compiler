<?php

namespace Service;

use LiamH\Valueobjectgenerator\Enum\FileExtension;
use LiamH\Valueobjectgenerator\Exception\FileException;
use LiamH\Valueobjectgenerator\Service\FileService;
use LiamH\Valueobjectgenerator\ValueObject\GeneratedFile;
use PHPUnit\Framework\TestCase;

class FileServiceTest extends TestCase
{
    private const TEST_FILE_LOCATION = './build/TestFile';

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
        return new self({{HydrationLogic}});
    }
}';
    }

    public function testWriteContents(): void
    {
        $file = new GeneratedFile(self::TEST_FILE_LOCATION, 'Hello world!', FileExtension::PHP);

        $service = $this->createService();
        self::assertTrue($service->writeFile($file));
        self::assertSame('Hello world!', file_get_contents('./build/TestFile.php'));
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
                'HydrationReplacement'
            )
        );

    }

    private function createService(): FileService
    {
        return new FileService();
    }
}