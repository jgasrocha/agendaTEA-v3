<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CursoAdminMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $curso = $request->route('curso');
        $user = $request->user();

        if(!$user->is_admin && !$user->cursos->contains($curso)) {
            return response()->json(['message' => 'Acesso não autorizado a este curso.'], 403);
        }
        return $next($request);
    }
}
