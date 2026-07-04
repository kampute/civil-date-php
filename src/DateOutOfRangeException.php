<?php

declare(strict_types=1);

namespace Kampute\CivilDate;

use OutOfRangeException;

/**
 * Exception thrown when a date cannot be represented in the requested calendar.
 */
class DateOutOfRangeException extends OutOfRangeException
{
}
