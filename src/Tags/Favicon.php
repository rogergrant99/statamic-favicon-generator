<?php

namespace rogergrant99\FaviconGenerator\Tags;

use Statamic\Tags\Tags;
use rogergrant99\FaviconGenerator\Blueprints\Favicons;
use Illuminate\Support\Facades\Log;

class Favicon extends Tags
{
    /**
     * The {{ favicon }} tag.
     *
     * @return string|array
     */
    public function index()
    {
        return '<!-- Favicon Generator Tags -->'
            . Favicons::augmentedValues()['html_tags']
            . '<!-- /Favicon Generator Tags -->';
    }
}