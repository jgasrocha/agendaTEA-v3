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
        
        // Verifica se o curso existe e se é um objeto do tipo Curso
        if (!$curso) {
            return response()->json(['message' => 'Curso não encontrado.'], 404);
        }
        
        // Verifica se o usuário tem permissão
        if ($user->is_admin || $user->cursosAdmin->contains($curso)) {
            return $next($request);
        }
        
        return response()->json(['message' => 'Acesso não autorizado a este curso.'], 403);
    }
}
