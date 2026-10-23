<?php

use App\Http\Controllers\Api;
use Illuminate\Support\Facades\Route;

/*
| JSON API. Reads of published content are public; everything else requires
| authentication: the admin session cookie (Sanctum SPA) or a personal access token.
*/
Route::middleware('throttle:api')->name('api.')->group(function () {
    $resources = [
        'projects' => Api\ProjectController::class,
        'services' => Api\ServiceController::class,
        'skills' => Api\SkillController::class,
        'experiences' => Api\ExperienceController::class,
        'education' => Api\EducationController::class,
        'testimonials' => Api\TestimonialController::class,
    ];

    foreach ($resources as $name => $controller) {
        Route::get($name, [$controller, 'index'])->name("{$name}.index");
        Route::get("{$name}/{id}", [$controller, 'show'])->name("{$name}.show");
    }
    Route::get('settings', [Api\SettingController::class, 'index'])->name('settings.index');
    Route::post('messages', [Api\MessageController::class, 'store'])->middleware('throttle:contact')->name('messages.store');

    Route::middleware('auth:sanctum')->group(function () use ($resources) {
        foreach ($resources as $name => $controller) {
            Route::post($name, [$controller, 'store'])->name("{$name}.store");
            Route::match(['put', 'patch'], "{$name}/{id}", [$controller, 'update'])->name("{$name}.update");
            Route::delete("{$name}/{id}", [$controller, 'destroy'])->name("{$name}.destroy");
        }

        Route::get('messages', [Api\MessageController::class, 'index'])->name('messages.index');
        Route::get('messages/{message}', [Api\MessageController::class, 'show'])->name('messages.show');
        Route::patch('messages/{message}', [Api\MessageController::class, 'update'])->name('messages.update');
        Route::delete('messages/{message}', [Api\MessageController::class, 'destroy'])->name('messages.destroy');

        Route::get('media', [Api\MediaController::class, 'index'])->name('media.index');
        Route::post('media', [Api\MediaController::class, 'store'])->name('media.store');
        Route::patch('media/{medium}', [Api\MediaController::class, 'update'])->name('media.update');
        Route::delete('media/{medium}', [Api\MediaController::class, 'destroy'])->name('media.destroy');

        Route::put('settings/{key}', [Api\SettingController::class, 'update'])->middleware('can:super-admin')->name('settings.update');
    });
});
