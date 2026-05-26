<?php
namespace App\Http\Middleware;
use App\Models\Team;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureTeamMember
{
    public function handle(Request $request, Closure $next): Response
    {
        $param = $request->route('team');
        $team = $param instanceof Team
            ? $param
            : Team::where('slug', $param)->firstOrFail();

        if (!$team->teamMembers()->where('user_id', $request->user()->id)->exists()) {
            abort(403, 'You are not a member of this team.');
        }

        $request->route()->setParameter('team', $team);

        return $next($request);
    }
}
