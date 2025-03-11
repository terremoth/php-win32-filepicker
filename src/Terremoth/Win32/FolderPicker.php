<?php

namespace Terremoth\Win32;

use FFI;
use FFI\CData;

class FolderPicker
{
    private FFI $ffi;
    private int $flags;
    private string $title;

    /**
     * @param string $title
     * @SuppressWarnings(PHPMD.StaticAccess)
     */
    public function __construct(string $title = 'Selecione uma pasta')
    {
        $this->title = $title;

        $this->flags = 0x00000001 | 0x00000004 | 0x00000010 | 0x00000040 | 0x00000100;

        $this->ffi = FFI::cdef("
            typedef unsigned short wchar_t;
            typedef unsigned int UINT;
            typedef unsigned long ULONG;
            typedef int BOOL;
            typedef const wchar_t* LPCWSTR;
            typedef wchar_t* LPWSTR;
            typedef void* HWND;
            typedef void* LPVOID;
            typedef void* ITEMIDLIST;
            typedef ITEMIDLIST* LPITEMIDLIST;
            typedef long LPARAM;
            
            typedef struct _browseinfoW {
                HWND hwndOwner;
                LPITEMIDLIST pidlRoot;
                LPWSTR pszDisplayName;
                LPCWSTR lpszTitle;
                UINT ulFlags;
                int (*lpfn)(HWND, UINT, LPARAM, LPARAM);
                LPARAM lParam;
                int iImage;
            } BROWSEINFOW;
            
            LPITEMIDLIST SHBrowseForFolderW(BROWSEINFOW *lpbi);
            BOOL SHGetPathFromIDListW(LPITEMIDLIST pidl, LPWSTR pszPath);
            //void CoTaskMemFree(LPVOID pv);
        ", "shell32.dll");
    }

    /**
     *
     * @param int $flags
     * @return static
     */
    public function setFlags(int $flags): static
    {
        $this->flags = $flags;
        return $this;
    }

    /**
     * @param string $title
     * @return static
     */
    public function setTitle(string $title): static
    {
        $this->title = $title;
        return $this;
    }

    /**
     * @SuppressWarnings(PHPMD.StaticAccess)
     * @psalm-suppress UndefinedPropertyAssignment
     * @psalm-suppress InvalidArgument
     * @psalm-suppress MixedArgumentTypeCoercion
     * @noinspection PhpUndefinedMethodInspection
     * @noinspection PhpUndefinedFieldInspection
     * @return string|null
     */
    public function open(): ?string
    {
        $maxPath = 260;
        $displayName = $this->ffi->new("wchar_t[$maxPath]");
        for ($i = 0; $i < $maxPath; $i++) {
            $displayName[$i] = 0;
        }

        $browseInfo = $this->ffi->new("BROWSEINFOW");
        $browseInfo->hwndOwner = null;
        $browseInfo->pidlRoot = null;
        $browseInfo->pszDisplayName = null;
        $wideTitle = $this->createWideString($this->title);
        $wideTitleAddr = FFI::addr($wideTitle);
        $browseInfo->lpszTitle = $this->ffi->cast("LPCWSTR", $wideTitleAddr);
        $browseInfo->ulFlags = $this->flags;
        $browseInfo->lpfn = null;
        $browseInfo->lParam = 0;
        $browseInfo->iImage = 0;

        $pidl = $this->ffi->SHBrowseForFolderW(FFI::addr($browseInfo));
        if ($pidl === null) {
            return null;
        }

        // Aloca buffer para receber o caminho da pasta
        $pathBuffer = $this->ffi->new("wchar_t[$maxPath]");
        for ($i = 0; $i < $maxPath; $i++) {
            $pathBuffer[$i] = 0;
        }

        /** @psalm-suppress MixedArgumentTypeCoercion */
        $result = $this->ffi->SHGetPathFromIDListW($pidl, $pathBuffer);
        if (!$result) {
            return null;
        }

        return $this->wideStringToString($pathBuffer);
    }

    /**
     * @param string $str
     * @return CData
     */
    private function createWideString(string $str): CData
    {
        $wideStr = iconv('UTF-8', 'UTF-16LE', $str . "\0");
        $len = strlen($wideStr) / 2;
        $buffer = $this->ffi->new("wchar_t[$len]");
        for ($i = 0; $i < strlen($wideStr); $i += 2) {
            $buffer[$i / 2] = ord($wideStr[$i]) | (ord($wideStr[$i + 1]) << 8);
        }
        return $buffer;
    }

    /**
     * @psalm-suppress UndefinedPropertyAssignment
     * @psalm-suppress InvalidArgument
     * @psalm-suppress MixedArgumentTypeCoercion
     * @param CData $buffer
     * @return string
     */
    private function wideStringToString(CData $buffer): string
    {
        $str = '';
        $count = 0;

        while (true) {
            $char = $buffer[$count];

            if ($char === 0) {
                break;
            }

            $str .= pack('v', $char);
            $count++;
        }

        return iconv('UTF-16LE', 'UTF-8', $str);
    }
}
