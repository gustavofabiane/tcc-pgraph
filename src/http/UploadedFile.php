<?php

namespace Framework\Http;

use Psr\Http\Message\UploadedFileInterface;

/**
 * Value object representing a file uploaded through an HTTP request.
 *
 * Instances of this interface are considered immutable; all methods that
 * might change state MUST be implemented such that they retain the internal
 * state of the current instance and return an instance that contains the
 * changed state.
 */
class UploadedFile implements UploadedFileInterface
{
    /**
     * Uploaded file error code
     *
     * @var int
     */
    protected $error = UPLOAD_ERR_OK;

    /**
     * Uploaded file size
     *
     * @var int
     */
    protected $size;

    /**
     * The uploaded file original name
     *
     * @var string
     */
    protected $clientFilename;

    /**
     * Uploaded file media type provided by the client
     *
     * @var string
     */
    protected $clientMediaType;

    /**
     * Uploaded file temporary name
     *
     * @var string
     */
    protected $tempName;

    /**
     * Uploaded file stream
     *
     * @var \Psr\Http\Message\StreamInterface
     */
    protected $stream;

    /**
     * If the enviroment of the application is a standard SAPI server
     *
     * @var bool
     */
    protected $sapiEnviroment = true;

    /**
     * If the file has been already moved from temporary location
     *
     * @var bool
     */
    protected $isMoved = false;

    /**
     * Creates a new instance representating an uploaded file
     *
     * @param string $file
     * @param string $clientFilename
     * @param string $clientMediaType
     * @param string $size
     * @param int $error
     * @param bool $sapiEnviroment
     */
    public function __construct(
        string $tempName,
        ?string $clientFilename = null,
        ?string $clientMediaType = null,
        ?int $size = 0,
        int $error = UPLOAD_ERR_OK,
        bool $sapiEnviroment = true
    ) {
        $this->tempName = $tempName;
        $this->size = $size;
        $this->error = $error;
        $this->sapiEnviroment = $sapiEnviroment;
        $this->clientFilename = $clientFilename;
        $this->clientMediaType = $clientMediaType;
    }

    /**
     * Retrieve a stream representing the uploaded file.
     *
     * This method MUST return a StreamInterface instance, representing the
     * uploaded file. The purpose of this method is to allow utilizing native PHP
     * stream functionality to manipulate the file upload, such as
     * stream_copy_to_stream() (though the result will need to be decorated in a
     * native PHP stream wrapper to work with such functions).
     *
     * If the moveTo() method has been called previously, this method MUST raise
     * an exception.
     *
     * @return StreamInterface Stream representation of the uploaded file.
     * @throws \RuntimeException in cases when no stream is available or can be
     *     created.
     */
    public function getStream()
    {
        if ($this->isMoved) {
            throw new \RuntimeException('Cannot get uploaded file as stream');
        }

        if (!$this->stream) {
            $this->stream = new Stream($this->tempName, 'r');;
        }
        
        return $this->stream;
    }

    /**
     * Move the uploaded file to a new location.
     *
     * Use this method as an alternative to move_uploaded_file(). This method is
     * guaranteed to work in both SAPI and non-SAPI environments.
     * Implementations must determine which environment they are in, and use the
     * appropriate method (move_uploaded_file(), rename(), or a stream
     * operation) to perform the operation.
     *
     * $targetPath may be an absolute path, or a relative path. If it is a
     * relative path, resolution should be the same as used by PHP's rename()
     * function.
     *
     * The original file or stream MUST be removed on completion.
     *
     * If this method is called more than once, any subsequent calls MUST raise
     * an exception.
     *
     * When used in an SAPI environment where $_FILES is populated, when writing
     * files via moveTo(), is_uploaded_file() and move_uploaded_file() SHOULD be
     * used to ensure permissions and upload status are verified correctly.
     *
     * If you wish to move to a stream, use getStream(), as SAPI operations
     * cannot guarantee writing to stream destinations.
     *
     * @see http://php.net/is_uploaded_file
     * @see http://php.net/move_uploaded_file
     * @param string $targetPath Path to which to move the uploaded file.
     * @throws \InvalidArgumentException if the $targetPath specified is invalid.
     * @throws \RuntimeException on any error during the move operation, or on
     *     the second or subsequent call to the method.
     */
    public function moveTo($targetPath)
    {
        if ($this->isMoved) {
            throw new \RuntimeException('The file has been moved previously');
        }

        $isStream = strpos($targetPath, '://') !== false;
        $pathDirectory = dirname($targetPath);
        if (!is_string($targetPath) || 
            !$isStream && 
            is_dir($pathDirectory) && !is_writable($pathDirectory)
        ) {
            throw new \InvalidArgumentException('Invalid move target path');
        }
        
        if ($this->error !== UPLOAD_ERR_OK) {
            throw new \RuntimeException('Cannot move file due to upload error: ' . $this->error);
        }

        if ($this->sapiEnviroment) {
            if (is_uploaded_file($this->tempName)) {
                throw new \RuntimeException('File in ' . $this->tempName . ' is not an uploaded file');
            }
            if (move_uploaded_file($this->tempName, $targetPath) === false) {
                throw new \RuntimeException('It was not possible to move the uploaded file');
            }
        } else {
            $destinationStream = new Stream($targetPath, 'w+');
            $uploadedFileStream = $this->getStream();
            
            $uploadedFileStream->rewind();
            while (!$uploadedFileStream->eof()) {
                $destinationStream->write($uploadedFileStream->read(4096));
            }

            $destinationStream->close();
        }
        
        $this->isMoved = true;
    }
    
    /**
     * Retrieve the file size.
     *
     * Implementations SHOULD return the value stored in the "size" key of
     * the file in the $_FILES array if available, as PHP calculates this based
     * on the actual size transmitted.
     *
     * @return int|null The file size in bytes or null if unknown.
     */
    public function getSize()
    {
        return $this->size ?: null;
    }
    
    /**
     * Retrieve the error associated with the uploaded file.
     *
     * The return value MUST be one of PHP's UPLOAD_ERR_XXX constants.
     *
     * If the file was uploaded successfully, this method MUST return
     * UPLOAD_ERR_OK.
     *
     * Implementations SHOULD return the value stored in the "error" key of
     * the file in the $_FILES array.
     *
     * @see http://php.net/manual/en/features.file-upload.errors.php
     * @return int One of PHP's UPLOAD_ERR_XXX constants.
     */
    public function getError()
    {
        return $this->error;
    }
    
    /**
     * Retrieve the filename sent by the client.
     *
     * Do not trust the value returned by this method. A client could send
     * a malicious filename with the intention to corrupt or hack your
     * application.
     *
     * Implementations SHOULD return the value stored in the "name" key of
     * the file in the $_FILES array.
     *
     * @return string|null The filename sent by the client or null if none
     *     was provided.
     */
    public function getClientFilename()
    {
        return $this->clientFilename ?: null;
    }
    
    /**
     * Retrieve the media type sent by the client.
     *
     * Do not trust the value returned by this method. A client could send
     * a malicious media type with the intention to corrupt or hack your
     * application.
     *
     * Implementations SHOULD return the value stored in the "type" key of
     * the file in the $_FILES array.
     *
     * @return string|null The media type sent by the client or null if none
     *     was provided.
     */
    public function getClientMediaType()
    {
        return $this->clienteMediaType ?: null;
    }

    /**
     * Filters an array with the native PHP uploaded files structure (from $_FILES),
     * and parses it to a valid structure accepted by the PSR-7.
     * 
     * Note: This method is not part of the PSR-7 specification.
     * 
     * @see validUploadedFilesTree()
     * @param array $files
     * @return array
     */
    public static function filterNativeUploadedFiles(array $files): array
    {
        $uploadedFiles = [];

        foreach ($files as $fieldName => $nativeUploadedFile) {
            if (is_array($nativeUploadedFile['tmp_name'])) {
                $normalized = [];
                foreach ($nativeUploadedFile['tmp_name'] as $fileKey => $null) { 
                    $normalized[$fileKey] = [
                        'tmp_name' => $nativeUploadedFile['tmp_name'][$fileKey],
                        'name' => $nativeUploadedFile['name'][$fileKey] ?? null,
                        'type' => $nativeUploadedFile['type'][$fileKey] ?? null,
                        'size' => $nativeUploadedFile['size'][$fileKey] ?? null,
                        'error' => $nativeUploadedFile['error'][$fileKey],
                    ];
                }
                $uploadedFiles[$fieldName] = static::filterNativeUploadedFiles($normalized);
            } else {
                $uploadedFiles[$fieldName] = new static(
                    $nativeUploadedFile['tmp_name'],
                    $nativeUploadedFile['name'] ?? null,
                    $nativeUploadedFile['type'] ?? null,
                    (int) $nativeUploadedFile['size'] ?? null,
                    (int) $nativeUploadedFile['error']
                );
            }
        }

        return $uploadedFiles;
    }

    /**
     * Checks if the given array is a valid UploadedFiles structure.
     * 
     * Note: This method is not part of the PSR-7 specification.
     *
     * @param array $uploadedFiles
     * @return bool
     */
    public static function validUploadedFilesTree(array $uploadedFiles): bool
    {
        foreach ($uploadedFiles as $fieldName => $value) {
            // if the file(s) name identifier is not a string
            if (!is_string($fieldName) && !is_int($fieldName)) {
                return false;
            }
            // if there is an array
            if (is_array($value)) {
                return static::validUploadedFilesTree($value);
            }
            // if the value is not an instance of UploadedFileInterface
            if (! $value instanceof UploadedFileInterface) {
                return false;
            }
        }
        return true;
    }
}
