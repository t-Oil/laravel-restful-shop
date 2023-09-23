<?php declare(strict_types=1);

namespace App\Enums;

use BenSampo\Enum\Enum;

final class ProviderType extends Enum
{
    const LOCAL = 'local';
    const FACEBOOK = 'facebook';
    const GOOGLE = 'google';
}
