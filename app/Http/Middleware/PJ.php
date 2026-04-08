<?php

namespace App\Http\Middleware;

use App\Exception\PostException;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class PJ
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (!$request->user()->hasRole('sekretariat') && !$request->user()->hasRole('auditor') && !$request->user()->hasRole('penanggung-jawab')) {
            throw new PostException('Unauthorized access', 403);
        }
        return $next($request);
    }
}
