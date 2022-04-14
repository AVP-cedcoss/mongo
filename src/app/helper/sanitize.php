<?php

namespace helper;

use Phalcon\Escaper;

class sanitize
{
    /**
     * function sanitize
     * escapes HTML
     *
     * @param [type] $html
     * @return void
     */
    public function html($html)
    {
        $escaper = new Escaper();
        return $escaper->escapeHtml($html);
    }
}