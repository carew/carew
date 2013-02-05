<?php

namespace Carew\Event;

final class Events
{
    /**
     * Dispatch for each document during the document creation.
     */
    const DOCUMENT = 'carew.document';

    /**
     * Dispatched with all documents after creation
     */
    const DOCUMENTS = 'carew.documents';

    /**
     * Dispatched for each index during the document creation
     */
    const INDEX = 'carew.index';

    /**
     * Dispatched with all indexes after creation
     */
    const INDEXES = 'carew.indexes';

    /**
     * Dispatched for each index during the document creation
     */
    const TAG = 'carew.tag';

    /**
     * Dispatched with all tags after creation
     */
    const TAGS = 'carew.tags';
}
