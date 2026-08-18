<?php

use Illuminate\Support\Facades\Route;
use TwillRedirects\PluginPage\Http\Controllers\PluginsController;

Route::get('plugins', [PluginsController::class, 'index'])->name('plugins.index');
