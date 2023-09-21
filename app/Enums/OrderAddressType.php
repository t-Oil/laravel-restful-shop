<?php declare(strict_types=1);

namespace App\Enums;

use BenSampo\Enum\Enum;

/**
 * @method static static SHIPPING()
 * @method static static RECEIPT()
 */
final class OrderAddressType extends Enum
{
    const SHIPPING = 'shipping';
    const RECEIPT = 'receipt';
}
