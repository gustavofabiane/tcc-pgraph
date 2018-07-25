<?php

namespace Framework\Tests\Http;

use PHPUnit\Framework\TestCase;
use Framework\Http\UploadedFile;

class UploadedFileTest extends TestCase
{
    /**
     * Uploads from $_FILES superglobal simulation
     *
     * @return array
     */
    protected function mockNativeUploadedFiles()
    {
        return [
            'field1' => [
                'name' => 'test.php',
                'type' => 'text/php',
                'error' => 0,
                'size' => 1024,
                'tmp_name' => '/tmp/file/1234'
            ],
            'field2' => [
                'name' => ['1.png', '2.jpg'],
                'type' => ['image/png', 'image/jpg'],
                'error' => [0, 1],
                'size' => [123124123, 123123123],
                'tmp_name' => ['/tmp/file/3212', '/tmp/file/123123']
            ],
            'field3' => [
                'name' => [
                    'field31' => 'test.html',
                    'field32' => ['1.pdf', '2.pdf']
                ],
                'type' => [
                    'field31' => 'text/html',
                    'field32' => ['application/pdf', 'application/pdf']
                ],
                'error' => [
                    'field31' => 3,
                    'field32' => [2, 0]
                ],
                'size' => [
                    'field31' => 100000,
                    'field32' => [987456321, 98745321]
                ],
                'tmp_name' => [
                    'field31' => '/tmp/file/3212232',
                    'field32' => ['/tmp/file/asdre123', '/tmp/file/rTor34211']
                ],
            ]
        ];
    }

    /**
     * A PSR-7 uploaded files structered as expected when filtering 
     * the result of $this->mockNativeUploadedFiles()
     *
     * @see mockNativeUploadedFiles()
     * @return array
     */
    protected function expectedFilteredFiles()
    {
        return [
            'field1' => new UploadedFile('/tmp/file/1234', 'test.php', 'text/php', 1024, 0),
            'field2' => [
                0 => new UploadedFile('/tmp/file/3212', '1.png', 'image/png', 123124123, 0),
                1 => new UploadedFile('/tmp/file/123123', '2.jpg', 'image/jpg', 123123123, 1)
            ],
            'field3' => [
                'field31' => new UploadedFile('/tmp/file/3212232', 'test.html', 'text/html', 100000, 3),
                'field32' => [
                    0 => new UploadedFile('/tmp/file/asdre123', '1.pdf', 'application/pdf', 987456321, 2),
                    1 => new UploadedFile('/tmp/file/rTor34211', '2.pdf', 'application/pdf', 98745321, 0)
                ]
            ]
        ];
    }

    /**
     * Tests if the provided expected PSR-7 structure 
     * passes in the validation method.
     *
     * @return void
     */
    public function testExceptedUploadedFilesStructure()
    {
        $this->assertSame(
            true, 
            UploadedFile::validUploadedFilesTree($this->expectedFilteredFiles())
        );
    }
    
    /**
     * Tests if the implementation correctly filter an $_FILES 
     * structure to the expected by the PSR-7 specification.
     *
     * @return void
     */
    public function testFilterNativeFilesGlobalStructure()
    {
        $files = $this->mockNativeUploadedFiles();
        $filteredStructure = UploadedFile::filterNativeUploadedFiles($files);
        
        $this->assertEquals($this->expectedFilteredFiles(), $filteredStructure);
        $this->assertSame(true, UploadedFile::validUploadedFilesTree($filteredStructure));
    }
}
