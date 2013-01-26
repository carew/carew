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

    public function onPageProcess($event)
    {
        $this->process($event);
    }

    public function onPostProcess($event)
    {
        $this->process($event);
    }

    private function process($event)
    {
        $subject = $event->getSubject();

        if ('md' !== $subject->getFile()->getExtension()) {
            return;
        }

        $subject->setBody($this->markdownParser->transformMarkdown($subject->getBody()));
    }

    public static function getPriority()
    {
        return 256;
    }
}
