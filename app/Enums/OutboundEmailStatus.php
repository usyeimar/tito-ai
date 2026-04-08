<?php

namespace App\Enums;

enum OutboundEmailStatus: string
{
    case QUEUED = 'queued';
    case SCHEDULED = 'scheduled';
    case SENT = 'sent';
    case DELIVERED = 'delivered';
    case DELIVERY_DELAYED = 'delivery_delayed';
    case OPENED = 'opened';
    case CLICKED = 'clicked';
    case COMPLAINED = 'complained';
    case BOUNCED = 'bounced';
    case FAILED = 'failed';
    case CANCELED = 'canceled';
}
