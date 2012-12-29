<?php

namespace Carew\EventSubscriber\Metadata;

use Carew\EventSubscriber\EventSubscriber;

class Optimization extends EventSubscriber
{
    public function onPostProcess($event)
    {
        $subject  = $event->getSubject();
        $file     = $subject['file'];
        $metadata = $subject['metadata'];

        list($year, $month, $day, $slug) = explode('-', $file->getBasename('.md'), 4);

        $date = "$year-$month-$day";

        $metadata = array_replace(array(
            'date'        => new \DateTime($date),
            'date_string' => $date,
        ), $metadata);

        $path = "$year/$month/$day/$slug.html";

        $subject['metadata'] = $metadata;
        $subject['path']     = $path;

        $event->setSubject($subject);
    }

    public function onPageProcess($event)
    {
        $subject = $event->getSubject();
        $file    = $subject['file'];

        $slug = $file->getBasename('.md');

        $subject['path'] = $file->getRelativePath()
            ? sprintf('%s/%s.html', $file->getRelativePath(), $slug)
            : $slug.'.html';

        $event->setSubject($subject);
    }

    public function onApiProcess($event)
    {
        $subject = $event->getSubject();
        $file = $subject['file'];

        $subject['path'] = sprintf('api/%s',$file->getRelativePathname());

        $event->setSubject($subject);
    }

    public static function getPriority()
    {
        return 500;
    }
}
