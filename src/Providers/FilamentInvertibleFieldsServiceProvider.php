<?php

namespace Araga\FilamentInvertibleFields\Providers;

use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;

class FilamentInvertibleFieldsServiceProvider extends PackageServiceProvider
{
    public function configurePackage(Package $package): void
    {
        $package
            ->name('filament-invertible-fields')
            ->hasViews('filament-invertible-fields')
            ->hasTranslations();
    }
}