<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Log;

class LogRequest
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $method = strtoupper($request->getMethod());
        $uri = $request->getPathInfo();
        $host = "host:{$request->getHost()}";
        $header = 'header:' . json_encode($request->headers->all());
        $body = 'body:' . json_encode($request->except(config('http-logger.except')));
        $message = "{$method}|{$host}|{$uri}|{$header}|{$body}";

        Log::channel('log-request')->debug($message);

        return $next($request);
    }
}
