<?php

namespace Carew\Event;

final class Events
{
    /**
     * Dispatch for each document during the document creation
     */
    const DOCUMENT = 'carew.document';

    /**
     * Dispatch for each post during the document creation
     */
    const POST = 'carew.post';

    /**
     * Dispatch for each page during the document creation
     */
    const PAGE = 'carew.page';

    /**
     * Dispatch for each api during the document creation
     */
    const API       = 'carew.api';

    /**
     * Dispatch with all documents after creation
     */
    const DOCUMENTS = 'carew.documents';

    /**
     * Dispatch for each index during the document creation
     */
    const INDEX = 'carew.index';

    /**
     * Dispatch with all indexes after creation
     */
    const INDEXES = 'carew.indexes';

    /**
     * Dispatch for each index during the document creation
     */
    const TAG = 'carew.tag';

    /**
     * Dispatch with all tags after creation
     */
    const TAGS = 'carew.tags';
}
