<?php

namespace Carew\EventSubscriber\Body;

use Carew\EventSubscriber\EventSubscriber;
use dflydev\markdown\MarkdownExtraParser;

class Markdown extends EventSubscriber
{
    private $markdownParser;

    public function __construct($markdownParser = null)
    {
        $this->markdownParser = $markdownParser ?: new MarkdownExtraParser();
    }

    public function onDocumentProcess($event)
    {
        $subject = $event->getSubject();

        $subject->setBody($this->markdownParser->transformMarkdown($subject->getBody()));
    }

    public static function getPriority()
    {
        return 256;
    }
}
