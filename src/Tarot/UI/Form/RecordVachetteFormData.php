<?php

declare(strict_types=1);

namespace App\Tarot\UI\Form;

final class RecordVachetteFormData
{
    /** @var list<string> */
    public array $deadPlayerIds = [];

    /** @var list<?string> */
    public array $ranking = [];
}
