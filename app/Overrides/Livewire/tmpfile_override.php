<?php

namespace Livewire\Features\SupportFileUploads;

if (!function_exists('Livewire\Features\SupportFileUploads\tmpfile')) {
    /**
     * Implementazione alternativa della funzione tmpfile() che utilizza tempnam() e fopen()
     * per risolvere il problema "Call to undefined function Livewire\Features\SupportFileUploads\tmpfile()"
     *
     * @return resource|false
     */
    function tmpfile() {
        $tmpfname = tempnam(sys_get_temp_dir(), '');
        return fopen($tmpfname, "w+");
    }
}