<?php

namespace App\Http\Middleware;

use App\Models\Employee;
use App\Models\User;
use App\Support\AuthActor;
use Closure;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Auth\Middleware\EnsureEmailIsVerified;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

/**
 * Lets signed-in employees use the community module alongside regular users.
 *
 * The community code works with a users-table account (post owner, reactions, comments, ...),
 * so for this request only an employee with Community access acts as the admin team's account
 * (see AuthActor::ownerUserId()) — the same owner used for offers they post. Nothing is written
 * to the session: the employee stays logged in as an employee, and the layouts keep showing
 * the employee through AuthActor::viewer().
 *
 * Usage: "community.actor" (sign-in required) or "community.actor:optional" (public pages).
 */
class EnsureCommunityActor
{
    public function handle(Request $request, Closure $next, string $mode = 'required'): Response
    {
        $optional = $mode === 'optional';

        if (Auth::guard('web')->check()) {
            Auth::shouldUse('web');

            return $optional
                ? $next($request)
                : app(EnsureEmailIsVerified::class)->handle($request, $next);
        }

        $employee = Auth::guard('employee')->user();

        if ($employee instanceof Employee) {
            $response = $this->actAsCommunityOwner($request, $employee, $optional);

            if ($response !== null) {
                return $response;
            }

            return $next($request);
        }

        if ($optional) {
            return $next($request);
        }

        throw new AuthenticationException('Unauthenticated.', ['web']);
    }

    /**
     * @return Response|null a response to stop the request with, or null to continue
     */
    private function actAsCommunityOwner(Request $request, Employee $employee, bool $optional): ?Response
    {
        if (! $employee->is_active) {
            if ($optional) {
                return null;
            }

            Auth::guard('employee')->logout();

            return $this->deny(
                $request,
                'Your employee account is waiting for admin activation and a role assignment.',
                route('employee.login')
            );
        }

        if (! $employee->hasModuleAccess('community')) {
            return $optional ? null : $this->deny($request, 'You do not have permission to access the community module.');
        }

        $ownerId = AuthActor::ownerUserId();
        $owner = $ownerId !== null ? User::query()->find($ownerId) : null;

        if (! $owner) {
            return $optional ? null : $this->deny($request, 'No admin account is available to post community content for employees.');
        }

        // In-memory only (setUser never touches the session), so it ends with this request.
        Auth::guard('web')->setUser($owner);
        Auth::shouldUse('web');
        $request->attributes->set(AuthActor::ACTING_EMPLOYEE, $employee);

        return null;
    }

    private function deny(Request $request, string $message, ?string $redirectTo = null): Response
    {
        if ($request->expectsJson()) {
            return response()->json(['message' => $message], 403);
        }

        if ($redirectTo !== null) {
            return redirect()->to($redirectTo)->withErrors(['email' => $message]);
        }

        abort(403, $message);
    }
}
