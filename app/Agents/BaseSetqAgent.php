<?php

namespace App\Agents;

use GuzzleHttp\Client;

/**
 * BaseSetqAgent — common machinery for the 4 SETQ.AI demo agents.
 *
 * Subclasses only declare:
 *   protected string $promptFile;   // path under prompts/ (e.g. '01-setq-assistant.md')
 *   protected string $name;         // 'setq-assistant'
 *
 * Everything else (HTTP client, streaming, error handling) is shared.
 *
 * Public demo mode: each chat runs against fake fixtures, never real data.
 * The 15-min anonymous sandbox is enforced upstream (middleware), not here.
 */
abstract class BaseSetqAgent implements AgentInterface
{
    protected Client $client;
    protected string $systemPrompt;

    /** path relative to base_path() — defined by subclass */
    protected string $promptFile;
    /** agent slug — defined by subclass */
    protected string $name;

    public function __construct()
    {
        $path = base_path('prompts/' . $this->promptFile);
        $this->systemPrompt = is_file($path)
            ? (string) file_get_contents($path)
            : 'You are a helpful assistant.';

        $this->client = new Client([
            'base_uri'        => config('services.anthropic.base_url', 'https://api.anthropic.com'),
            'timeout'         => 120,
            'connect_timeout' => 10,
            'headers'         => [
                'x-api-key'         => (string) config('services.anthropic.api_key'),
                'anthropic-version' => '2023-06-01',
                'Content-Type'      => 'application/json',
            ],
        ]);
    }

    public function chat(string|array $message, array $history = []): string
    {
        $response = $this->client->post('/v1/messages', [
            'json' => [
                'model'      => config('services.anthropic.model', 'claude-sonnet-4-6'),
                'max_tokens' => 4096,
                'system'     => $this->systemPrompt,
                'messages'   => array_merge($history, [['role' => 'user', 'content' => $message]]),
            ],
        ]);

        $data = json_decode($response->getBody()->getContents(), true);
        return $data['content'][0]['text'] ?? '';
    }

    /**
     * Per-stream usage breakdown — populated as Anthropic SSE arrives so the
     * controller can write a row to `usage_logs` after each turn.
     *
     * Anthropic emits usage info in two events:
     *   • `message_start.message.usage`  → input + cache_read tokens
     *   • `message_delta.usage`          → output_tokens (final count)
     *
     * @var array{input_tokens:int,output_tokens:int,cache_read_tokens:int,model:string}
     */
    public array $lastUsage = [
        'input_tokens'      => 0,
        'output_tokens'     => 0,
        'cache_read_tokens' => 0,
        'model'             => '',
    ];

    public function stream(
        string|array $message,
        array $history,
        callable $onChunk,
        ?callable $heartbeat = null
    ): string {
        $model = config('services.anthropic.model', 'claude-sonnet-4-6');
        $this->lastUsage = ['input_tokens' => 0, 'output_tokens' => 0, 'cache_read_tokens' => 0, 'model' => $model];

        $response = $this->client->post('/v1/messages', [
            'stream' => true,
            'json'   => [
                'model'      => $model,
                'max_tokens' => 4096,
                'system'     => $this->systemPrompt,
                'messages'   => array_merge($history, [['role' => 'user', 'content' => $message]]),
                'stream'     => true,
            ],
        ]);

        $body     = $response->getBody();
        $full     = '';
        $buf      = '';
        $lastBeat = time();

        while (!$body->eof()) {
            // Apanha o caso "Error in input stream" depois de termos resposta.
            try {
                $buf .= $body->read(1024);
            } catch (\Throwable $e) {
                if ($full === '') throw $e;
                break;
            }
            while (($pos = strpos($buf, "\n")) !== false) {
                $line = substr($buf, 0, $pos);
                $buf  = substr($buf, $pos + 1);
                $line = trim($line);
                if (!str_starts_with($line, 'data: ')) continue;

                $json = substr($line, 6);
                $evt  = json_decode($json, true);
                if (!is_array($evt)) continue;

                $type = $evt['type'] ?? '';

                // Anthropic clean end → break antes de qualquer read() final
                if ($type === 'message_stop') break 2;

                // Capture token usage from the events that carry it
                if ($type === 'message_start' && isset($evt['message']['usage'])) {
                    $u = $evt['message']['usage'];
                    $this->lastUsage['input_tokens']      = (int) ($u['input_tokens'] ?? 0);
                    $this->lastUsage['cache_read_tokens'] = (int) ($u['cache_read_input_tokens'] ?? 0);
                    if (isset($evt['message']['model'])) {
                        $this->lastUsage['model'] = (string) $evt['message']['model'];
                    }
                }
                if ($type === 'message_delta' && isset($evt['usage']['output_tokens'])) {
                    $this->lastUsage['output_tokens'] = (int) $evt['usage']['output_tokens'];
                }

                if ($type === 'content_block_delta'
                    && ($evt['delta']['type'] ?? '') === 'text_delta') {
                    $text = $evt['delta']['text'] ?? '';
                    if ($text !== '') {
                        $full .= $text;
                        $onChunk($text);
                    }
                }
            }
            if ($heartbeat && (time() - $lastBeat) >= 5) {
                $heartbeat('streaming');
                $lastBeat = time();
            }
        }
        return $full;
    }

    public function getName(): string  { return $this->name; }
    public function getModel(): string { return config('services.anthropic.model', 'claude-sonnet-4-6'); }
}
