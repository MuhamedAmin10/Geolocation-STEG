<?php

namespace App\Http\Controllers;

use App\Models\NotificationPreference;
use Illuminate\Http\Request;

class NotificationPreferenceController extends Controller
{
    public function edit(Request $request)
    {
        $user = $request->user();
        $preferences = NotificationPreference::query()->firstOrCreate([
            'user_id' => $user->id,
        ], [
            'in_app' => true,
            'email' => true,
            'sms' => false,
            'whatsapp' => false,
            'mission_assigned' => true,
            'status_changed' => true,
            'sla_breached' => true,
            'time_reminder' => true,
        ]);

        return view('notification-preferences.edit', compact('preferences'));
    }

    public function update(Request $request)
    {
        $user = $request->user();
        $preferences = NotificationPreference::query()->firstOrCreate(['user_id' => $user->id]);

        $data = $request->validate([
            'in_app' => ['nullable', 'boolean'],
            'email' => ['nullable', 'boolean'],
            'sms' => ['nullable', 'boolean'],
            'whatsapp' => ['nullable', 'boolean'],
            'mission_assigned' => ['nullable', 'boolean'],
            'status_changed' => ['nullable', 'boolean'],
            'sla_breached' => ['nullable', 'boolean'],
            'time_reminder' => ['nullable', 'boolean'],
        ]);

        $preferences->fill([
            'in_app' => $request->boolean('in_app'),
            'email' => $request->boolean('email'),
            'sms' => $request->boolean('sms'),
            'whatsapp' => $request->boolean('whatsapp'),
            'mission_assigned' => $request->boolean('mission_assigned'),
            'status_changed' => $request->boolean('status_changed'),
            'sla_breached' => $request->boolean('sla_breached'),
            'time_reminder' => $request->boolean('time_reminder'),
        ]);
        $preferences->save();

        return redirect()
            ->route('notification-preferences.edit')
            ->with('status', 'Preferences mises a jour.');
    }
}
