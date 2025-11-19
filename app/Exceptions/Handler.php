<?php

namespace App\Exceptions;

use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Throwable;
use Illuminate\Http\Exceptions\PostTooLargeException;

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
            //
        });

        // Handle oversized POST/upload (file too large)
        $this->renderable(function (PostTooLargeException $e, $request) {
            $maxUpload = ini_get('upload_max_filesize');
            $message = 'Fichier trop volumineux. Taille maximale autorisée : ' . $maxUpload;

            if ($request->expectsJson() || $request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => $message
                ], 413);
            }

            // Redirect back for standard form submissions, remove file inputs from old input
            return redirect()->back()->withInput($request->except(array_keys($request->files->all())))->with('error', $message);
        });
    }
}
