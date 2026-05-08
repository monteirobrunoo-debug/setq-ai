<?php

namespace App\Agents;

class SetqOperationsAgent extends BaseSetqAgent
{
    protected string $promptFile = '02-setq-operations.md';
    protected string $name       = 'setq-operations';
    // Invoicing / projects / suppliers → Finance + Procurement + Supply Chain
    protected ?string $bookDomain = 'back_office';
}
