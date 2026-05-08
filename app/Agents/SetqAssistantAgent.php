<?php

namespace App\Agents;

class SetqAssistantAgent extends BaseSetqAgent
{
    protected string $promptFile = '01-setq-assistant.md';
    protected string $name       = 'setq-assistant';
    // Inbox / calendar / CRM updates → benefits from CRM + Sales books
    protected ?string $bookDomain = 'front_office';
}
