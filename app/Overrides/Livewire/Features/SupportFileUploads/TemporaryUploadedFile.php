<?php

namespace App\Overrides\Livewire\Features\SupportFileUploads;

use Illuminate\Support\Arr;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\Storage;
use League\MimeTypeDetection\FinfoMimeTypeDetector;
use Livewire\Features\SupportFileUploads\FileUploadConfiguration;

class TemporaryUploadedFile extends UploadedFile
{
    protected $disk;
    protected $storage;
    protected $path;

    public function __construct($path, $disk)
    {
        $this->disk = $disk;
        $this->storage = Storage::disk($this->disk);
        $this->path = FileUploadConfiguration::path($path, false);

        // Fix per il problema "Call to undefined function Livewire\Features\SupportFileUploads\tmpfile()"
        // Sostituiamo tmpfile() con tempnam() e fopen()
        $tmpfname = tempnam(sys_get_temp_dir(), '');
        $tmpFile = fopen($tmpfname, "w");

        parent::__construct(stream_get_meta_data($tmpFile)['uri'], $this->path);

        // While running tests, update the last modified timestamp to the current
        // Carbon timestamp (which respects time traveling), because otherwise
        // cleanupOldUploads() will mess up with the filesystem...
        if (app()->runningUnitTests())
        {
            @touch($this->path(), now()->timestamp);
        }
    }

    public function getPath(): string
    {
        return $this->storage->path(FileUploadConfiguration::directory());
    }

    public function isValid(): bool
    {
        return true;
    }

    public function getSize(): int
    {
        if (app()->runningUnitTests() && str($this->getFilename())->contains('-size=')) {
            return (int) str($this->getFilename())->between('-size=', '.')->__toString();
        }

        return (int) $this->storage->size($this->path);
    }

    public function getMimeType(): string
    {
        if (app()->runningUnitTests() && str($this->getFilename())->contains('-mimeType=')) {
            $escapedMimeType = str($this->getFilename())->between('-mimeType=', '-');

            // MimeTypes contain slashes, but we replaced them with underscores in `SupportTesting\Testable`
            // to ensure the filename is valid, so we now need to revert that.
            return (string) $escapedMimeType->replace('_', '/');
        }

        $mimeType = $this->storage->mimeType($this->path);

        // Flysystem V2.0+ removed guess mimeType from extension support, so it has been re-added back
        // in here to ensure the correct mimeType is returned when using faked files in tests
        if (in_array($mimeType, ['application/octet-stream', 'inode/x-empty', 'application/x-empty'])) {
            $detector = new FinfoMimeTypeDetector();

            $mimeType = $detector->detectMimeTypeFromPath($this->path) ?: 'text/plain';
        }

        return $mimeType;
    }

    public function getFilename(): string
    {
        return $this->getName($this->path);
    }

    public function getRealPath(): string
    {
        return $this->storage->path($this->path);
    }

    public function getPathname(): string
    {
        return $this->getRealPath();
    }

    public function getClientOriginalName(): string
    {
        return $this->extractOriginalNameFromFilePath($this->path);
    }

    public function dimensions()
    {
        // Fix per il problema "Call to undefined function Livewire\Features\SupportFileUploads\tmpfile()"
        // Sostituiamo tmpfile() con tempnam() e fopen()
        $tmpfname = tempnam(sys_get_temp_dir(), '');
        $tmpFile = fopen($tmpfname, "w");
        
        stream_copy_to_stream($this->storage->readStream($this->path), $tmpFile);

        try {
            $dimensions = getimagesize(stream_get_meta_data($tmpFile)['uri']);
        } catch (\Throwable $e) {
            $dimensions = [0, 0];
        }

        return [$dimensions[0], $dimensions[1]];
    }

    public function preview($conversion = '')
    {
        return $this->temporaryUrl();
    }

    public function temporaryUrl()
    {
        return URL::temporarySignedRoute(
            'livewire.preview-upload',
            now()->addMinutes(30),
            ['filename' => $this->getFilename()]
        );
    }

    public function readStream()
    {
        return $this->storage->readStream($this->path);
    }

    public function exists()
    {
        return $this->storage->exists($this->path);
    }

    public function get()
    {
        return $this->storage->get($this->path);
    }

    public function delete()
    {
        return $this->storage->delete($this->path);
    }

    public function storeAs($directory, $name = null, $options = [])
    {
        $options = $this->parseOptions($options);

        $disk = $options['disk'] ?: config('filesystems.default');

        $newPath = trim($directory.'/'.$name, '/');

        Storage::disk($disk)->put(
            $newPath,
            $this->storage->readStream($this->path),
            $options
        );

        return $newPath;
    }

    public function store($directory = '', $options = [])
    {
        $options = $this->parseOptions($options);

        $disk = $options['disk'] ?: config('filesystems.default');

        $newPath = trim($directory.'/'.$this->hashName(), '/');

        Storage::disk($disk)->put(
            $newPath,
            $this->storage->readStream($this->path),
            $options
        );

        return $newPath;
    }

    protected function hashName($path = null)
    {
        $hash = Arr::get(pathinfo($this->getClientOriginalName()), 'filename', '');

        if ($extension = $this->guessExtension()) {
            $hash .= '.'.$extension;
        }

        return $hash;
    }

    protected function getName($path)
    {
        return basename($path);
    }

    protected function extractOriginalNameFromFilePath($path)
    {
        return $this->getName($path);
    }

    protected function parseOptions($options)
    {
        if (is_string($options)) {
            $options = ['disk' => $options];
        }

        return $options;
    }
}