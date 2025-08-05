<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

class StorageImageHeaders {
    public function handle(Request $request, Closure $next) {
        Log::info('Storage middleware running for path: ' . $request->path());
        $response = $next($request);

        // Check if the request path starts with '/storage/' and is an image
        if (str_starts_with($request->path(), 'storage/') && $this->isFileResponse($response)) {
            // Add any other headers you need
            $response->header('Access-Control-Allow-Origin', '*');
        }

        return $response;
    }

    private function isFileResponse($response) {
        if (!$response->headers->has('Content-Type')) {
            return false;
        }

        $contentType = $response->headers->get('Content-Type');
        return str_starts_with($contentType, 'image/') || str_starts_with($contentType, 'application/');
    }
}
