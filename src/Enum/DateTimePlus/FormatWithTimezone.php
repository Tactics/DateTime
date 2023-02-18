<?php

namespace Tactics\DateTime\Enum\DateTimePlus;

use DateTimeInterface;

/**
 * FormatWithTimezone
 *
 * Creating a DateTime is something else than displaying
 * a DateTime, so they require different formats.
 *
 * For creating a DateTime you NEED date, time and timezone.
 *
 * By design, these formats always need to have a Date, Time and Timezone section.
 */
enum FormatWithTimezone: string
{
    /** ex: 2022-01-01T12:00:00+00:00 */
    case ATOM = 'Y-m-d\TH:i:sP';

    /** ex: 'Saturday, 01-Jan-2022 12:00:00 GMT+0000'; */
    case COOKIE = 'l, d-M-Y H:i:s T';
}
