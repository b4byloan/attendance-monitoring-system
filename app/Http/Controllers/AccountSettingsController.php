<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\LoadsNotificationBell;
use App\Http\Requests\Account\UpdateAccountSettingsRequest;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AccountSettingsController extends Controller
{
    use LoadsNotificationBell;

    public function edit(Request $request): View
    {
        return view('account-settings.edit', [
            'user' => $request->user(),
            'layout' => $this->layoutFor($request->user()),
        ])->with($this->notificationBellData());
    }

    public function update(UpdateAccountSettingsRequest $request): RedirectResponse
    {
        $user = $request->user();
        $validated = $request->validated();

        $user->name = $validated['name'];
        $user->email = $validated['email'];

        if (! empty($validated['new_password'])) {
            $user->password = $validated['new_password'];
        }

        $user->save();

        return back()->with('success', 'Your account has been updated.');
    }

    private function layoutFor(User $user): string
    {
        return $user->isAdmin() || $user->isSubAdmin() ? 'layout' : 'simple-layout';
    }
}
