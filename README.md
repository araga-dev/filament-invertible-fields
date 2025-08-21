# Filament Invertible Fields

Fields **and** Table Filters for Filament 4 that let users switch between **Include (Only)** and **Exclude (Except)** modes with a single toggle.

Included:
- `InvertibleMultiSelect` + `InvertibleMultiSelectFilter`
- `InvertibleTextInput` + `InvertibleTextInputFilter` (comma-separated by default)

- Package: `araga-dev/filament-invertible-fields`
- Namespace: `Araga\FilamentInvertibleFields`
- View namespace: `filament-invertible-fields::`
- Translations: `resources/lang/en`, `resources/lang/pt_BR`

## Installation

```bash
composer require araga-dev/filament-invertible-fields
```

(Laravel will auto-discover the service provider.)

Optional:
```bash
php artisan vendor:publish --tag="filament-invertible-fields-views"
php artisan vendor:publish --tag="filament-invertible-fields-translations"
```

## Usage

### MultiSelect Filter
```php
use Araga\FilamentInvertibleFields\Tables\Filters\InvertibleMultiSelectFilter;

InvertibleMultiSelectFilter::make('status')
    ->label(__('filament-invertible-fields::messages.status'))
    ->column('status')
    ->options([
        'draft' => 'Draft',
        'published' => 'Published',
        'archived' => 'Archived',
    ]);
```

### MultiSelect Field
```php
use Araga\FilamentInvertibleFields\Forms\Components\InvertibleMultiSelect;

InvertibleMultiSelect::make('values')
    ->options([ '1' => 'One', '2' => 'Two' ]);
```

### Text Filter (comma-separated list by default)
```php
use Araga\FilamentInvertibleFields\Tables\Filters\InvertibleTextInputFilter;

InvertibleTextInputFilter::make('sku')
    ->label(__('filament-invertible-fields::messages.sku'))
    ->column('sku')
    ->listSeparator(','); // "123, 456, 789"
```

### Text Field
```php
use Araga\FilamentInvertibleFields\Forms\Components\InvertibleTextInput;

InvertibleTextInput::make('values')
    ->listSeparator(','); // optional
```

## i18n
- English and Brazilian Portuguese included.
- You can publish and edit the translation files with the command above.

## License
MIT