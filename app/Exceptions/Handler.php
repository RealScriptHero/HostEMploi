<?php

namespace App\Exceptions;

use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Database\QueryException;
use Throwable;

class Handler extends ExceptionHandler
{
    /**
     * The list of the inputs that are never flashed to the session on validation exceptions.
     *
     * @var array<int, string>
     */
    protected $dontFlash = [
        'current_password',
        'password',
        'password_confirmation',
    ];

    /**
     * Register the exception handling callbacks for the application.
     */
    public function register(): void
    {
        $this->reportable(function (Throwable $e) {
            // Log all exceptions in production
            if (config('app.env') === 'production') {
                logger()->error('Exception: ' . class_basename($e), [
                    'message' => $e->getMessage(),
                    'file' => $e->getFile(),
                    'line' => $e->getLine(),
                ]);
            }
        });

        // Handle ModelNotFoundException gracefully
        $this->renderable(function (ModelNotFoundException $e) {
            return response()->view('errors.404', [], 404);
        });

        // Handle database query exceptions
        $this->renderable(function (QueryException $e) {
            if (config('app.env') === 'production') {
                return response()->view('errors.500', [
                    'message' => 'Database error occurred. Please contact support.',
                ], 500);
            }
        });

        // Catch-all for any null access errors
        $this->renderable(function (Throwable $e) {
            if (config('app.env') === 'production' && str_contains($e->getMessage(), 'Trying to get property')) {
                return response()->view('errors.500', [
                    'message' => 'A required resource could not be loaded. Please try again.',
                ], 500);
            }
        });
    }
}
