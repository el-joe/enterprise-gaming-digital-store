<?php

namespace App\Domains\Suppliers\Exceptions;

/**
 * Fetching codes after a successful purchase failed. Thrown without the response body, which may
 * hold card numbers. The fetch is a safe read and is retried on its own; it never leads to a new
 * order.
 */
class DeliverableFetchFailed extends SupplierRequestFailed {}
