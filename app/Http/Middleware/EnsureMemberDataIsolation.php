<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

class EnsureMemberDataIsolation
{
    public function handle(Request $request, Closure $next): Response
    {
        if (! auth()->check()) {
            return redirect('/login');
        }

        $user = $request->user();
        $userMemberNumber = $user->membercode;

        $routeMemberNumber = $request->route('member_number') ?? $request->route('id');
        $requestMemberNumber = $request->input('member_number');

        foreach ([$routeMemberNumber, $requestMemberNumber] as $targetMemberNumber) {
            if ($targetMemberNumber !== null && (string) $targetMemberNumber !== (string) $userMemberNumber) {
                Log::channel('activity')->warning('Member data isolation violation', [
                    'user_id' => $user->id,
                    'user_member_number' => $userMemberNumber,
                    'attempted_member_number' => $targetMemberNumber,
                    'url' => $request->fullUrl(),
                    'ip' => $request->ip(),
                ]);

                abort(403);
            }
        }

        return $next($request);
    }
}
