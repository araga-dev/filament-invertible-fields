<?php

namespace Araga\FilamentInvertibleFields\Forms\Components;

use Closure;
use Filament\Actions\Action as SuffixAction;
use Filament\Forms\Components\Select;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Support\Icons\Heroicon;
use Illuminate\Contracts\Support\Arrayable;

class InvertibleMultiSelect extends Select
{
    protected ?string $excludeStatePath = null;
    protected bool $optionsResolved = false;

    protected bool $clientSideToggle = true;
    protected string $includeToggleLabel;
    protected string $excludeToggleLabel;
    protected Heroicon $includeIcon = Heroicon::CheckCircle;
    protected Heroicon $excludeIcon = Heroicon::NoSymbol;
    protected string $includeColor = 'success';
    protected string $excludeColor = 'danger';

    protected array|Arrayable|Closure|null $rawOptions = [];

    protected function setUp(): void
    {
        parent::setUp();

        $this->includeToggleLabel = __('filament-invertible-fields::messages.toggle_to_only');
        $this->excludeToggleLabel = __('filament-invertible-fields::messages.toggle_to_except');

        $this->multiple()->searchable();

        $this->afterStateHydrated(function (Get $get, Set $set): void {
            if (! $this->excludeStatePath) {
                $this->excludeStatePath = $this->getAbsoluteExcludePath();
            }

            if ($get($this->excludeStatePath) === null) {
                $set($this->excludeStatePath, false);
            }

            $this->ensureOptionsMaterialized();
        });

        if ($this->clientSideToggle) {
            $this->prepareClientSideIconSwap();
        } else {
            $this->suffixActions([$this->makeToggleSuffixAction()]);
        }
    }

    public function excludeState(string $statePath): static
    {
        $this->excludeStatePath = $statePath;
        return $this;
    }

    public function getExcludePath(): string
    {
        return $this->excludeStatePath ?? $this->deriveExcludePath();
    }

    protected function deriveExcludePath(): string
    {
        $base = $this->getStatePath() ?: $this->getName() ?: 'invertible_multiselect';
        return "{$base}__exclude";
    }

    public function getAbsoluteExcludePath(): string
    {
        $containerPath = $this->getContainer()?->getStatePath(true) ?? '';
        $name = $this->getName() ?: 'values';

        return trim($containerPath . '.' . $name . '__exclude', '.');
    }

    public function toggleLabels(string $toIncludeLabel, string $toExcludeLabel): static
    {
        $this->includeToggleLabel = $toIncludeLabel;
        $this->excludeToggleLabel = $toExcludeLabel;
        return $this;
    }

    public function toggleIcons(Heroicon $includeIcon, Heroicon $excludeIcon): static
    {
        $this->includeIcon = $includeIcon;
        $this->excludeIcon = $excludeIcon;
        return $this;
    }

    public function toggleColors(string $includeColor, string $excludeColor): static
    {
        $this->includeColor = $includeColor;
        $this->excludeColor = $excludeColor;
        return $this;
    }

    /**
     * @param  array|Arrayable|string|Closure|null  $options
     */
    public function options(array|Arrayable|string|Closure|null $options): static
    {
        $this->rawOptions = $options;
        $this->optionsResolved = false;

        if ($options instanceof Closure) {
            return parent::options($options);
        }

        $array = $options instanceof Arrayable ? $options->toArray() : (array) $options;

        return parent::options($this->normalizeOptionsArray($array));
    }

    public function clientSideToggle(bool $on = true): static
    {
        $this->clientSideToggle = $on;
        return $this;
    }

    protected function makeToggleSuffixAction(): SuffixAction
    {
        $action = SuffixAction::make('toggleExclude')->label('');

        return $action
            ->icon(fn(Get $get) => $get($this->getExcludePath()) ? $this->excludeIcon : $this->includeIcon)
            ->color(fn(Get $get) => $get($this->getExcludePath()) ? $this->excludeColor : $this->includeColor)
            ->tooltip(fn(Get $get) => $get($this->getExcludePath()) ?  $this->includeToggleLabel : $this->excludeToggleLabel)
            ->action(fn(Set $set, Get $get) => $set($this->getExcludePath(), ! $get($this->getExcludePath())));
    }

    protected function prepareClientSideIconSwap(): void
    {
        $this->suffixActions([
            SuffixAction::make('toggleExclude')
                ->label('')
                ->view('filament-invertible-fields::components.invertible-toggle')
                ->viewData([
                    'includeIcon' => $this->includeIcon,
                    'excludeIcon' => $this->excludeIcon,
                    'includeToggleLabel' => $this->includeToggleLabel,
                    'excludeToggleLabel' => $this->excludeToggleLabel,
                    'includeColor' => $this->includeColor,
                    'excludeColor' => $this->excludeColor,
                    'excludePath' => fn() => $this->getAbsoluteExcludePath(),
                ]),
        ]);
    }

    protected function ensureOptionsMaterialized(): void
    {
        if ($this->optionsResolved || ! ($this->rawOptions instanceof \Closure)) {
            return;
        }

        $resolved = $this->evaluate($this->rawOptions);
        $array = $resolved instanceof Arrayable ? $resolved->toArray() : (array) $resolved;

        parent::options($this->normalizeOptionsArray($array));
        $this->optionsResolved = true;
    }

    protected function normalizeOptionsArray(array $array): array
    {
        $normalized = [];
        foreach ($array as $value => $label) {
            if (is_array($label)) {
                $group = [];
                foreach ($label as $v => $l) {
                    $group[(string) $v] = (string) $l;
                }
                $normalized[(string) $value] = $group;
                continue;
            }
            $normalized[(string) $value] = (string) $label;
        }
        return $normalized;
    }
}