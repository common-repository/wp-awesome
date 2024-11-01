<?php
namespace WPA\Module\Fluent\Backend;

/**
 * Represents a data store backend. Backends are responsible for providing an
 * Iterator that returns data from a backend, according to Query.
 */
interface BackendInterface extends IteratorAggregate {

    public function __construct(WPA\Module\Fluent\Query $query);
    
}