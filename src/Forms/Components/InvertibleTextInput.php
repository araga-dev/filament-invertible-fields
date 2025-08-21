<?php

namespace Araga\FilamentInvertibleFields\Forms\Components;

use Closure;
use Filament\Actions\Action as SuffixAction;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Support\Icons\Heroicon;

class InvertibleTextInput extends TextInput
{
    protected ?string $excludeStatePath = null;

    protected bool $clientSideToggle = true;
    protected string $includeToggleLabel;
    protected string $excludeToggleLabel;
    protected Heroicon $includeIcon = Heroicon::CheckCircle;
    protected Heroicon $excludeIcon = Heroicon::NoSymbol;
    protected string $includeColor = 'success';
    protected string $excludeColor = 'danger';

    protected string $listSeparator = ',';

    protected function setUp(): void
    {
        parent::setUp();

        $this->includeToggleLabel = __('filament-invertible-fields::messages.toggle_to_only');
        $this->excludeToggleLabel = __('filament-invertible-fields::messages.toggle_to_except');

        $this->afterStateHydrated(function (Get $get, Set $set): void {
            if (! $this->excludeStatePath) {
                $this->excludeStatePath = $this->getAbsoluteExcludePath();
            }

            if ($get($this->excludeStatePath) === null) {
                $set($this->excludeStatePath, false);
            }
        });

        if ($this->clientSideToggle) {
            $this->prepareClientSideIconSwap();
        } else {
            $this->suffixActions([$this->makeToggleSuffixAction()]);
        }
    }

    public function listSeparator(string $listSeparator = ','): static
    {
        $this->listSeparator = $listSeparator;
        return $this;
    }

    public function getListSeparator(): string
    {
        return $this->listSeparator;
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
        $base = $this->getStatePath() ?: $this->getName() ?: 'invertible_textinput';
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
                    'includeIcon'        => $this->includeIcon,
                    'excludeIcon'        => $this->excludeIcon,
                    'includeToggleLabel' => $this->includeToggleLabel,
                    'excludeToggleLabel' => $this->excludeToggleLabel,
                    'includeColor'       => $this->includeColor,
                    'excludeColor'       => $this->excludeColor,
                    'excludePath'        => fn() => $this->getAbsoluteExcludePath(),
                ]),
        ]);
    }

    public function parseToList(?string $raw): array
    {
        if ($raw === null || $raw === '') {
            return [];
        }

        $sep = preg_quote($this->listSeparator, '/');

        $parts = preg_split('/' . $sep . '/', $raw);

        if (! is_array($parts)) {
            return [];
        }

        $clean = [];
        foreach ($parts as $p) {
            $t = trim($p);
            if ($t === '') {
                continue;
            }
            if (!in_array($t, $clean, true)) {
                $clean[] = $t;
            }
        }

        return $clean;
    }
}