<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Validation\ValidationException;

class Report extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'client_id',
        'client_name',
        'services',
        'report_date',
        'period_type',
        'period_start',
        'period_end',
        'period_label',
        'status',
        'viewed',
        'document_links',
        'notes',
        'is_copy',
    ];

    protected function casts(): array
    {
        return [
            'services' => 'array',
            'report_date' => 'date',
            'period_start' => 'date',
            'period_end' => 'date',
            'viewed' => 'boolean',
            'document_links' => 'array',
            'is_copy' => 'boolean',
        ];
    }

    protected static function booted(): void
    {
        static::saving(function (Report $report): void {
            if ($report->exists) {
                if ($report->isDirty('client_id')) {
                    throw ValidationException::withMessages([
                        'data.client_id' =>
                            'Non puoi cambiare il cliente di un report già salvato.',
                    ]);
                }

                if ($report->isDirty('client_name')) {
                    throw ValidationException::withMessages([
                        'data.client_id' =>
                            'Il nome cliente conservato nel report non può essere modificato.',
                    ]);
                }
            }

            if ($report->trashed()) {
                return;
            }

            // Le sole modifiche a Stato e Visionato non richiedono
            // un nuovo controllo del contenuto.
            if (
                $report->exists
                && ! $report->isDirty([
                    'client_id',
                    'report_date',
                    'services',
                    'period_type',
                    'period_start',
                    'period_end',
                    'period_label',
                    'document_links',
                    'notes',
                    'deleted_at',
                ])
            ) {
                return;
            }

            $report->ensureNoDuplicateExists();
        });
    }

    private function ensureNoDuplicateExists(): void
    {
        $query = static::query()
            ->where('client_id', $this->client_id);

        if ($this->report_date === null) {
            $query->whereNull('report_date');
        } else {
            $query->whereDate(
                'report_date',
                $this->report_date->format('Y-m-d')
            );
        }

        if ($this->exists) {
            $query->where(
                $this->getKeyName(),
                '!=',
                $this->getKey()
            );
        }

        $content = $this->comparableContent();

        foreach ($query->cursor() as $existingReport) {
            if ($existingReport->comparableContent() === $content) {
                throw ValidationException::withMessages([
                    'data.duplicate_report' =>
                        'Esiste già un report identico per questa data. Modifica almeno un dato prima di salvarlo.',
                ]);
            }
        }
    }

    private function comparableContent(): array
    {
        // L'ordine e il nome storico dei servizi non ne cambiano
        // l'identità: confrontiamo gli ID.
        $serviceIds = array_map(
            fn ($id): int => (int) $id,
            array_column($this->services ?? [], 'id')
        );

        $serviceIds = array_values(array_unique($serviceIds));

        sort($serviceIds, SORT_NUMERIC);

        // Anche l'ordine dei link non rappresenta una differenza.
        $documents = array_values(array_map(
            fn (array $document): array => [
                'label' => trim((string) ($document['label'] ?? '')),
                'url' => trim((string) ($document['url'] ?? '')),
            ],
            $this->document_links ?? []
        ));

        usort(
            $documents,
            fn (array $first, array $second): int =>
                strcmp($first['url'], $second['url'])
                ?: strcmp($first['label'], $second['label'])
        );

        return [
            'period_type' => $this->period_type,

            'period_start' => $this->period_type === 'date_range'
                ? $this->period_start?->format('Y-m-d')
                : null,

            'period_end' => $this->period_type === 'date_range'
                ? $this->period_end?->format('Y-m-d')
                : null,

            'period_label' => $this->period_type === 'text'
                ? trim((string) $this->period_label)
                : null,

            'service_ids' => $serviceIds,
            'document_links' => $documents,
            'notes' => trim((string) $this->notes),
        ];
    }

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class)
            ->withTrashed();
    }
}