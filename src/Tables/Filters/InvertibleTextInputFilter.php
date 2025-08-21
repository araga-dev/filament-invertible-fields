<?php

namespace Araga\FilamentInvertibleFields\Tables\Filters;

use Closure;
use Araga\FilamentInvertibleFields\Forms\Components\InvertibleTextInput;
use Filament\Forms\Components\Hidden;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\Indicator;
use Illuminate\Database\Eloquent\Builder;

class InvertibleTextInputFilter extends Filter
{
    protected ?string $column = null;

    protected string $includeChipLabel;
    protected string $excludeChipLabel;

    protected ?InvertibleTextInput $input = null;
    protected ?Closure $inputConfigurator = null;

    protected string $listSeparator = ',';

    protected function setUp(): void
    {
        parent::setUp();

        $this->includeChipLabel = __('filament-invertible-fields::messages.only');
        $this->excludeChipLabel = __('filament-invertible-fields::messages.except');

        $this->input = InvertibleTextInput::make('values')
            ->label(fn (): string => $this->getLabel())
            ->listSeparator($this->listSeparator);

        if ($this->inputConfigurator) {
            ($this->inputConfigurator)($this->input);
        }

        $this->schema([
            $this->input,
            Hidden::make('values__exclude')->default(false),
        ]);

        $this->query(function (Builder $query, array $data): Builder {
            $raw = $data['values'] ?? null;
            $values = $this->input?->parseToList($raw) ?? [];

            if (empty($values)) {
                return $query;
            }

            $column  = $this->getColumn();
            $exclude = $this->isExcludeMode($data['values__exclude'] ?? null);

            return $exclude
                ? $query->whereNotIn($column, $values)
                : $query->whereIn($column, $values);
        });

        $this->indicateUsing(function (array $data): ?Indicator {
            $raw = $data['values'] ?? null;
            $values = $this->input?->parseToList($raw) ?? [];

            if (empty($values)) {
                return null;
            }

            $exclude  = $this->isExcludeMode($data['values__exclude'] ?? null);
            $chipLabel = $exclude ? $this->excludeChipLabel : $this->includeChipLabel;

            return Indicator::make("{$chipLabel} {$this->getLabel()}: " . implode(', ', $values))
                ->removeField('values');
        });
    }

    public function listSeparator(string $listSeparator = ','): static
    {
        $this->listSeparator = $listSeparator;

        if ($this->input) {
            $this->input->listSeparator($listSeparator);
        }

        return $this;
    }

    protected function isExcludeMode($checkValue = null): bool
    {
        if ($checkValue !== null && $checkValue === true) {
            return true;
        }

        $paths = [
            'tableFilters.' . $this->getName() . '.values__exclude',
            'tableFilters.' . $this->getName() . '.tableDeferredFilters.' . $this->getName() . '.values__exclude',
        ];

        foreach ($paths as $path) {
            if (data_get($this->getLivewire(), $path) === true) {
                return true;
            }
        }

        return false;
    }

    public function column(string $column): static
    {
        $this->column = $column;
        return $this;
    }

    public function attribute(string $column): static
    {
        return $this->column($column);
    }

    public function configureInput(Closure $callback): static
    {
        $this->inputConfigurator = $callback;

        if ($this->input) {
            $callback($this->input);
        }

        return $this;
    }

    public function includeLabel(string $label): static
    {
        $this->includeChipLabel = $label;
        return $this;
    }

    public function excludeLabel(string $label): static
    {
        $this->excludeChipLabel = $label;
        return $this;
    }

    protected function getColumn(): string
    {
        return $this->column ?? $this->getName();
    }
}