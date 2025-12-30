<?php
namespace App\Http\Middleware;

use Illuminate\Auth\Middleware\Authenticate as Middleware;
use Illuminate\Http\Request;

class Authenticate extends Middleware
{
    protected function redirectTo(Request $request): ?string
    {
        // لـ API routes، لا نريد redirect، نريد 401
        if ($request->expectsJson()) {
            return null;
        }
        
        return route('login');
    }
}