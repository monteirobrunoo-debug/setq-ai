<?php

namespace App\Agents;

class SetqInsightsAgent extends BaseSetqAgent
{
    protected string $promptFile = '04-setq-insights.md';
    protected string $name       = 'setq-insights';
    // KPIs / dashboards / forecasting → Finance + analytics from back office
    protected ?string $bookDomain = 'back_office';
}
