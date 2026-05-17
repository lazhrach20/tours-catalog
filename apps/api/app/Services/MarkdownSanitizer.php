<?php

namespace App\Services;

use League\CommonMark\Environment\Environment;
use League\CommonMark\Extension\CommonMark\CommonMarkCoreExtension;
use League\CommonMark\MarkdownConverter;
use Symfony\Component\HtmlSanitizer\HtmlSanitizer;
use Symfony\Component\HtmlSanitizer\HtmlSanitizerConfig;

class MarkdownSanitizer
{
    private MarkdownConverter $converter;

    private HtmlSanitizer $sanitizer;

    public function __construct()
    {
        $environment = new Environment;
        $environment->addExtension(new CommonMarkCoreExtension);

        $this->converter = new MarkdownConverter($environment);

        $config = (new HtmlSanitizerConfig)
            ->allowSafeElements()
            ->allowRelativeLinks()
            ->allowRelativeMedias();

        $this->sanitizer = new HtmlSanitizer($config);
    }

    public function toHtml(string $markdown): string
    {
        $markdown = trim($markdown);
        if ($markdown === '') {
            return '';
        }

        $html = $this->converter->convert($markdown)->getContent();

        return $this->sanitizer->sanitize($html);
    }
}
