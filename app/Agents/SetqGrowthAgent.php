<?php

namespace App\Agents;

class SetqGrowthAgent extends BaseSetqAgent
{
    protected string $promptFile = '03-setq-growth.md';
    protected string $name       = 'setq-growth';
    // Content / outreach / lead-gen → CRM + Sales corpus
    protected ?string $bookDomain = 'front_office';
}
