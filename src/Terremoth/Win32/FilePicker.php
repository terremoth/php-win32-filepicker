<?php

namespace Terremoth\Win32;

use FFI;
use FFI\CData;

class FilePicker
{
    private const MAX_FILES = 32768;

    private FFI $ffi;
    private int $fileHandlerFlags;
    private array $extensions;
    private string $defaultExtension = '';
    private bool $allowMultiple = false;

    /**
     * @SuppressWarnings(PHPMD.StaticAccess)
     */
    public function __construct(private readonly string $initialDir = '')
    {
        $this->ffi = FFI::cdef(" 
            typedef unsigned short wchar_t;
            typedef unsigned long DWORD;
            typedef unsigned short WORD;
            typedef void* HWND;
            typedef char* LPCSTR;
            typedef long LPARAM;
            typedef const wchar_t* LPCWSTR;
            typedef int BOOL;
            typedef void* LPVOID;
           
            typedef struct {
                DWORD lStructSize;
                HWND hwndOwner;
                HWND hInstance;
                LPCWSTR lpstrFilter;
                LPCWSTR lpstrCustomFilter;
                DWORD nMaxCustFilter;
                DWORD nFilterIndex;
                LPCWSTR lpstrFile;
                DWORD nMaxFile;
                LPCWSTR lpstrFileTitle;
                DWORD nMaxFileTitle;
                LPCWSTR lpstrInitialDir;
                LPCWSTR lpstrTitle;
                DWORD Flags;
                WORD nFileOffset;
                WORD nFileExtension;
                LPCWSTR lpstrDefExt;
                LPARAM lCustData;
                LPVOID lpfnHook;
                LPCWSTR lpTemplateName;
                void* pvReserved;
                DWORD dwReserved;
                DWORD FlagsEx;
            } OPENFILENAMEW;
            
            BOOL GetOpenFileNameW(OPENFILENAMEW* lpofn);
            DWORD CommDlgExtendedError(void);
        ", "comdlg32.dll");

        $this->fileHandlerFlags = 0x80000; // OFN_EXPLORER
        $this->extensions = [null, "*.*"];
    }

    /**
     * @param array<string> $extensions
     * @return static
     */
    public function addExtensionsFilter(array $extensions): static
    {
        $filterParts = [];

        if (empty($extensions)) {
            return $this;
        }

        $patternParts = array_map(function ($ext) {
            $ext = trim($ext, " .");
            return "*.$ext";
        }, $extensions);

        $pattern = implode(";", $patternParts);
        $displayParts = array_map(function ($ext) {
            return ".$ext";
        }, $extensions);

        $display = implode(", ", $displayParts);
        $filterParts[] = $display;
        $filterParts[] = $pattern;

        array_unshift($this->extensions, ...$filterParts);

        return $this;
    }

    public function filterOnlySelectedExtensions(): static
    {
        if (count($this->extensions) > 2) {
            array_pop($this->extensions);
            array_pop($this->extensions);

            $this->fileHandlerFlags |= 0x1000; // OFN_FILEMUSTEXIST
            $this->fileHandlerFlags |= 0x800;  // OFN_PATHMUSTEXIST
        }

        return $this;
    }

    public function setDefaultExtensionSearch(string $extension): static
    {
        $this->defaultExtension = $extension;
        return $this;
    }

    private function convertExtensionsToWideString(): ?CData
    {
        /** @psalm-suppress MixedArgumentTypeCoercion */
        $filterString = implode("\0", $this->extensions) . "\0\0";
        return $this->createWideString($filterString);
    }

    /**
     * @param CData $fileBuffer
     * @return string
     */
    private function fillBufferString(CData $fileBuffer): string
    {
        $bufferString = '';
        for ($i = 0; $i < self::MAX_FILES; $i++) {
            /** @var int $wchar */
            $wchar = $fileBuffer[$i];
            if ($wchar === 0 && $fileBuffer[$i + 1] === 0) {
                $bufferString .= pack('v', 0);
                break;
            }
            $bufferString .= pack('v', $wchar);
        }

        return $bufferString;
    }

    public function getFilesFromBuffer(CData $fileBuffer): array
    {
        $bufferString = $this->fillBufferString($fileBuffer);
        $utf8String = iconv('UTF-16LE', 'UTF-8', $bufferString);
        $utf8String = rtrim($utf8String, "\0");
        return explode("\0", $utf8String);
    }

    /**
     * @SuppressWarnings(PHPMD.StaticAccess)
     * @psalm-suppress UndefinedPropertyAssignment
     * @psalm-suppress InvalidArgument
     * @noinspection PhpUndefinedMethodInspection
     * @noinspection PhpUndefinedFieldInspection
     */
    public function open(): array
    {
        $fileBuffer = $this->ffi->new("unsigned short[" . self::MAX_FILES . "]");

        for ($i = 0; $i < self::MAX_FILES; $i++) {
            $fileBuffer[$i] = 0;
        }

        $openFile = $this->ffi->new('OPENFILENAMEW');
        $openFile->lStructSize = FFI::sizeof($openFile);
        $openFile->hwndOwner = null;

        $extensions = FFI::addr($this->convertExtensionsToWideString());
        $openFile->lpstrFilter = $this->ffi->cast('LPCWSTR', $extensions);
        $openFile->lpstrFile = $this->ffi->cast('LPCWSTR', $fileBuffer);
        $openFile->nMaxFile = self::MAX_FILES;
        $openFile->Flags = $this->fileHandlerFlags;
        $openFile->lpstrTitle = null;

        if ($this->initialDir) {
            $initialDirBuffer = $this->createWideString($this->initialDir);
            $initialDir = FFI::addr($initialDirBuffer);
            $openFile->lpstrInitialDir = $this->ffi->cast('LPCWSTR', $initialDir);
        }

        if ($this->defaultExtension) {
            $defaultExt = ltrim($this->defaultExtension, ". ");
            $defaultExtBuffer = $this->createWideString($defaultExt);
            $defaultExtAddress = FFI::addr($defaultExtBuffer);
            $openFile->lpstrDefExt = $this->ffi->cast('LPCWSTR', $defaultExtAddress);
        }

        /** @var int $result */
        $result = $this->ffi->GetOpenFileNameW(FFI::addr($openFile));
        if ($result == 0) {
            return [];
        }

        /**
         * @var array<string> $parts
         */
        $parts = $this->getFilesFromBuffer($fileBuffer);
        $multiSelect = $this->allowMultiple;
        $partsCount = count($parts);
        $selectedFiles = [];

        if (!$multiSelect || $partsCount <= 1) {
            $selectedFiles[] = $parts[0];
            return $selectedFiles;
        }

        $basePath = $parts[0];

        for ($i = 1; $i < $partsCount; $i++) {
            if ($parts[$i] !== '') {
                $selectedFiles[] = $basePath . '\\' . $parts[$i];
            }
        }

        return $selectedFiles;
    }

    public function selectMultipleFiles(): static
    {
        $this->allowMultiple = true;
        $this->fileHandlerFlags |= 0x200; // OFN_ALLOWMULTISELECT
        return $this;
    }

    public function createWideString(string $str): ?FFI\CData
    {
        $wideStr = iconv('UTF-8', 'UTF-16LE', $str . "\0");
        $len = strlen($wideStr) / 2;
        $buffer = $this->ffi->new("unsigned short[$len]");

        for ($i = 0; $i < strlen($wideStr); $i += 2) {
            $buffer[$i / 2] = ord($wideStr[$i]) | (ord($wideStr[$i + 1]) << 8);
        }

        return $buffer;
    }
}
