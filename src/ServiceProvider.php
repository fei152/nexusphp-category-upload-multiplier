<?php
namespace NexusPlugin\CategoryUploadMultiplier;
use Illuminate\Support\ServiceProvider as BaseServiceProvider;
class ServiceProvider extends BaseServiceProvider { public function boot(): void { $this->loadTranslationsFrom(__DIR__.'/../resources/lang', Repository::ID); } }
