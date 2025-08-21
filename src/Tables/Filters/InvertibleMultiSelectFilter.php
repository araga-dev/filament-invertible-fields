<?php

namespace Araga\FilamentInvertibleFields\Tables\Filters;

use Closure;
use Araga\FilamentInvertibleFields\Forms\Components\InvertibleMultiSelect;
use Filament\Forms\Components\Hidden;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\Indicator;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Arr;

class InvertibleMultiSelectFilter extends Filter
{
    protected ?string $column = null;
    protected array|Arrayable|Closure $options = [];

    protected string $includeChipLabel;
    protected string $excludeChipLabel;

    protected ?InvertibleMultiSelect $select = null;
    protected ?Closure $selectConfigurator = null;

    protected function setUp(): void
    {
        parent::setUp();

        $this->includeChipLabel = __('filament-invertible-fields::messages.only');
        $this->excludeChipLabel = __('filament-invertible-fields::messages.except');

        $this->select = InvertibleMultiSelect::make('values')
            ->label(fn (): string => $this->getLabel())
            ->options($this->resolveOptions());

        if ($this->selectConfigurator) {
            ($this->selectConfigurator)($this->select);
        }

        $this->schema([
            $this->select,
            Hidden::make('values__exclude')->default(false),
        ]);

        $this->query(function (Builder $query, array $data): Builder {
            $values = $data['values'] ?? [];
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
            $rawValues = $data['values'] ?? [];
            if (empty($rawValues)) {
                return null;
            }

            $selectComponent = $this->select;
            if (! $selectComponent) {
                return null;
            }

            $values = Arr::flatten($rawValues);
            $options = $this->options;

            if ($options instanceof \Closure) {
                $options = $this->evaluate($options);
            }

            if ($options instanceof Arrayable) {
                $options = $options->toArray();
            }

            $labels = collect($values)
                ->map(fn($value) => $options[$value] ?? $value)
                ->all();

            $exclude = $this->isExcludeMode($data['values__exclude'] ?? null);
            $chipLabel = $exclude ? $this->excludeChipLabel : $this->includeChipLabel;

            return Indicator::make("{$chipLabel} {$this->getLabel()}: " . implode(', ', $labels))
                ->removeField('values');
        });
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

    public function options(array|Arrayable|Closure $options): static
    {
        $this->options = $options;

        if ($this->select) {
            $this->select->options($this->resolveOptions());
        }

        return $this;
    }

    public function configureSelect(Closure $callback): static
    {
        $this->selectConfigurator = $callback;

        if ($this->select) {
            $callback($this->select);
        }

        return $this;
    }

    public function searchResultsUsing(Closure $cb): static
    {
        return $this->configureSelect(fn(InvertibleMultiSelect $s) => $s->searchResultsUsing($cb));
    }

    public function optionLabelUsing(Closure $cb): static
    {
        return $this->configureSelect(fn(InvertibleMultiSelect $s) => $s->optionLabelUsing($cb));
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

    protected function resolveOptions(): array|Closure
    {
        if ($this->options instanceof Closure) {
            return $this->options;
        }

        $array = $this->options instanceof Arrayable ? $this->options->toArray() : (array) $this->options;

        $normalized = [];
        foreach ($array as $value => $label) {
            $normalized[(string) $value] = (string) $label;
        }

        return $normalized;
    }
}