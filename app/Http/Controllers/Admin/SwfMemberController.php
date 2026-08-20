<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\SwfMember;
use App\Models\User;
use App\Services\EncryptedIdService;
use App\Traits\FlashMessages;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class SwfMemberController extends Controller
{
    use FlashMessages;

    public function __construct(
        protected EncryptedIdService $encryptedIdService,
    ) {
    }

    public function create(): View
    {
        $members = User::where('role', 'member')->whereDoesntHave('swfMember')->get();
        
        return view('admin.swf.members.create', [
            'members' => $members,
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'join_date' => 'required|date',
        ]);

        try {
            $user = User::findOrFail($request->input('user_id'));
            
            // Use user's member_number as SWF membership number
            $membershipNumber = $user->membercode;
            
            $swfMember = SwfMember::create([
                'user_id' => $user->id,
                'membership_number' => $membershipNumber,
                'join_date' => $request->input('join_date'),
                'total_contributions' => 0,
                'total_benefits_received' => 0,
                'is_active' => true,
            ]);

            ActivityLog::create([
                'user_id' => Auth::id(),
                'subject_type' => 'swf_member',
                'subject_id' => $swfMember->id,
                'description' => "Admin registered new SWF member: {$membershipNumber}",
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'properties' => [
                    'user_id' => $user->id,
                    'user_name' => $user->name,
                    'membership_number' => $membershipNumber,
                ],
            ]);

            $this->success('SWF member registered successfully!');
            return redirect()->route('admin.swf.index');
        } catch (\Exception $e) {
            return back()->with('error', 'Failed to register SWF member: ' . $e->getMessage());
        }
    }

    public function show(string $encryptedId): View
    {
        $id = $this->encryptedIdService->decrypt($encryptedId);
        $swfMember = SwfMember::with(['user', 'contributions', 'benefits'])->findOrFail($id);
        
        return view('admin.swf.members.show', [
            'swfMember' => $swfMember,
            'encryptedId' => $encryptedId,
        ]);
    }
}
