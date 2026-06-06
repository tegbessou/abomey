<?php

declare(strict_types=1);

namespace App\Tarot\UI\Form;

final class RecordClassicDealFormData
{
    public ?string $takerId = null;
    public ?string $contract = null;
    public ?int $bouts = null;
    public ?int $pointsScored = null;
    public ?string $petitAuBout = 'none';
    public ?string $chelem = 'none';

    /** @var list<array{announcerId: string, size: string}> */
    public array $poignees = [];

    /** @var list<array{announcerId: string, type: string}> */
    public array $miseres = [];
}
