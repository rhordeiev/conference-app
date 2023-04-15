<?php

use App\Http\Controllers\CategoryController;
use App\Http\Controllers\CommentController;
use App\Http\Controllers\ConferenceController;
use App\Http\Controllers\CountryController;
use App\Http\Controllers\PlanController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\ReportPresentationController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::get('/countries', [CountryController::class, 'all']);
Route::get('/hasToken', [UserController::class, 'hasToken']);

Route::prefix('conferences')->group(function () {
    Route::get('/', [ConferenceController::class, 'all']);
    Route::get('/export', [ConferenceController::class, 'export']);
    Route::get('/search', [ConferenceController::class, 'search']);
});

Route::prefix('reports')->group(function () {
    Route::get('/', [ReportController::class, 'all']);
    Route::get('/search', [ReportController::class, 'search']);
});

Route::prefix('categories')->group(function () {
    Route::get(
        '/{id}/conferences',
        [CategoryController::class, 'getCategoryConferences']
    );
    Route::get(
        '/{id}/reports',
        [CategoryController::class, 'getCategoryReports']
    );
    Route::get('/{id}', [CategoryController::class, 'getCategoryById']);
});

Route::middleware('unauthenticated')->group(function () {
    Route::prefix('users')->group(function () {
        Route::post('/register', [UserController::class, 'register']);
        Route::post('/login', [UserController::class, 'login']);
    });
});

Route::middleware('authenticated')->group(function () {
    Route::prefix('users')->group(function () {
        Route::delete('/logout', [UserController::class, 'logout']);
        Route::put('/', [UserController::class, 'edit']);
    });

    Route::prefix('conferences')->group(function () {
        Route::get(
            '/{id}',
            [ConferenceController::class, 'getConference']
        );
        Route::get(
            '/{id}/category',
            [ConferenceController::class, 'getCategory']
        );
    });

    Route::prefix('reports')->group(function () {
        Route::get(
            '/conference/{id}',
            [ReportController::class, 'getReportsByConference']
        );
        Route::get(
            '/{id}/category',
            [ReportController::class, 'getCategory']
        );
        Route::post(
            '/favorites',
            [ReportController::class, 'addToFavorites']
        );
        Route::delete(
            '/favorites',
            [ReportController::class, 'removeFromFavorites']
        );
        Route::get(
            '/favorites/count',
            [ReportController::class, 'getFavoritesCount']
        );
        Route::get(
            '/favorites',
            [ReportController::class, 'getFavoritesByUser']
        );
        Route::get('/{id}', [ReportController::class, 'getReport']);
    });

    Route::prefix('comments')->group(function () {
        Route::post('/', [CommentController::class, 'create']);
        Route::get('/{reportId}', [CommentController::class, 'all']);
        Route::put('/', [CommentController::class, 'edit']);
    });

    Route::prefix('categories')->group(function () {
        Route::get('/', [CategoryController::class, 'all']);
    });

    Route::middleware('listener')->group(function () {
        Route::prefix('reports')->group(function () {
            Route::post('/join', [ReportController::class, 'join']);
            Route::delete(
                '/cancel',
                [ReportController::class, 'cancel']
            );
        });
    });

    Route::middleware('member')->group(function () {
        Route::prefix('conferences')->group(function () {
            Route::post('/join', [ConferenceController::class, 'join']);
            Route::delete(
                '/cancel',
                [ConferenceController::class, 'cancel']
            );
        });

        Route::prefix('reports')->group(function () {
            Route::post('/', [ReportController::class, 'create']);
        });

        Route::prefix('presentations')->group(function () {
            Route::post(
                '/',
                [ReportPresentationController::class, 'upload']
            );
        });

        Route::prefix('plans')->group(function () {
            Route::get('/', [PlanController::class, 'all']);
            Route::post('/createSetupIntent', [PlanController::class, 'createSetupIntent']);
            Route::post('/subscribe', [PlanController::class, 'subscribe']);
            Route::delete('/cancel', [PlanController::class, 'cancel']);
            Route::get('/{id}', [PlanController::class, 'getPlan']);
        });
    });

    Route::middleware('admin-announcer')->group(function () {
        Route::prefix('conferences')->group(function () {
            Route::post('/conferences', [ConferenceController::class, 'create']);
        });
    });

    Route::middleware('admin-creator-conference')->group(function () {
        Route::prefix('conferences')->group(function () {
            Route::put('/', [ConferenceController::class, 'edit']);
            Route::delete('/', [ConferenceController::class, 'remove']);
        });
    });

    Route::middleware('admin-creator-report')->group(function () {
        Route::prefix('presentations')->group(function () {
            Route::delete(
                '/',
                [ReportPresentationController::class, 'remove']
            );
        });

        Route::prefix('reports')->group(function () {
            Route::delete('/', [ReportController::class, 'remove']);
            Route::put('/', [ReportController::class, 'edit']);
        });
    });
});
