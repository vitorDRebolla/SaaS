<?php
namespace App\Http\Controllers\Api\V1;
use App\Actions\Teams\InviteMemberAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\Team\InviteMemberRequest;
use App\Http\Requests\Team\UpdateMemberRequest;
use App\Http\Resources\TeamInvitationResource;
use App\Http\Resources\TeamMemberResource;
use App\Models\Team;
use App\Models\TeamInvitation;
use App\Models\TeamMember;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class TeamMemberController extends Controller
{
    public function index(Team $team): JsonResponse
    {
        $members = $team->teamMembers()->with('user')->get();
        return response()->json(['data' => TeamMemberResource::collection($members)]);
    }

    public function invite(InviteMemberRequest $request, Team $team, InviteMemberAction $action): JsonResponse
    {
        $this->authorize('manageMembers', $team);
        $invitation = $action->execute($team, $request->email, $request->role);
        return response()->json(['data' => new TeamInvitationResource($invitation)], 201);
    }

    public function update(UpdateMemberRequest $request, Team $team, TeamMember $member): JsonResponse
    {
        $this->authorize('manageMembers', $team);

        if ($member->isOwner()) {
            abort(422, 'Cannot change the role of the team owner.');
        }

        $member->update(['role' => $request->role]);
        return response()->json(['data' => new TeamMemberResource($member->load('user'))]);
    }

    public function destroy(Request $request, Team $team, TeamMember $member): JsonResponse
    {
        $this->authorize('manageMembers', $team);

        if ($member->isOwner()) {
            abort(422, 'Cannot remove the team owner.');
        }

        if ($member->user_id === $request->user()->id) {
            abort(422, 'Use the leave endpoint to leave the team.');
        }

        $member->delete();
        return response()->json(null, 204);
    }

    public function acceptInvitation(Request $request, string $token): JsonResponse
    {
        $invitation = TeamInvitation::where('token', $token)->firstOrFail();

        if ($invitation->isExpired()) {
            abort(422, 'This invitation has expired.');
        }

        if ($invitation->accepted_at) {
            abort(422, 'This invitation has already been accepted.');
        }

        $user = $request->user();
        $team = $invitation->team;

        if (!$team->teamMembers()->where('user_id', $user->id)->exists()) {
            TeamMember::create([
                'team_id' => $team->id,
                'user_id' => $user->id,
                'role' => $invitation->role,
                'joined_at' => now(),
            ]);
        }

        $invitation->update(['accepted_at' => now()]);

        return response()->json(['data' => ['team' => $team->slug]]);
    }
}
