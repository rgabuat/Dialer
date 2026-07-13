<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Campaign;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class AdminCampaignController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->input('search');

        $campaigns = Campaign::with('inGroups')
            ->when($search, fn ($q) => $q->where('name', 'like', "%{$search}%")
                ->orWhere('description', 'like', "%{$search}%"))
            ->latest()
            ->paginate(20)
            ->withQueryString();

        return view('admin.campaigns.index', compact('campaigns', 'search'));
    }

    public function create()
    {
        $types = Campaign::TYPES;
        $dialModes = Campaign::DIAL_MODES;
        $ttsVoices = Campaign::TTS_VOICES;
        $recChannels = Campaign::REC_CHANNELS;

        return view('admin.campaigns.create', compact('types', 'dialModes', 'ttsVoices', 'recChannels'));
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
            'type' => ['required', 'in:'.implode(',', Campaign::TYPES)],
            'dial_mode' => ['required', 'in:'.implode(',', Campaign::DIAL_MODES)],
            'dial_level' => 'nullable|numeric|min:0|max:9999',
            'caller_id' => 'nullable|string|max:50',
            'acw_seconds' => 'nullable|integer|min:0|max:3600',
            'hopper_level' => 'nullable|integer|min:0|max:9999',
            'max_calls' => 'nullable|integer|min:0',
            'script' => 'nullable|string',
            'is_active' => 'boolean',
            'tts_voice' => ['nullable', 'in:'.implode(',', Campaign::TTS_VOICES)],
            'tts_language' => 'nullable|string|max:20',
            'greeting_message' => 'nullable|string|max:500',
            'hold_music_url' => 'nullable|url|max:1000',
            'tts_completed' => 'nullable|string|max:500',
            'tts_busy' => 'nullable|string|max:500',
            'tts_no_answer' => 'nullable|string|max:500',
            'tts_failed' => 'nullable|string|max:500',
            'tts_canceled' => 'nullable|string|max:500',
            'recording_enabled' => 'boolean',
            'recording_channels' => ['nullable', 'in:'.implode(',', Campaign::REC_CHANNELS)],
        ]);

        if ($validator->fails()) {
            if ($request->expectsJson()) {
                return response()->json(['errors' => $validator->errors()], 422);
            }

            return back()->withErrors($validator)->withInput();
        }

        $data = $validator->validated();
        $data['is_active'] = $request->boolean('is_active');
        $data['recording_enabled'] = $request->boolean('recording_enabled');

        $campaign = Campaign::create($data);

        if ($request->expectsJson()) {
            return response()->json(['success' => true, 'campaign' => $campaign]);
        }

        return redirect()->route('admin.campaigns.index')
            ->with('success', 'Campaign created successfully.');
    }

    public function edit(Campaign $campaign)
    {
        $types = Campaign::TYPES;
        $dialModes = Campaign::DIAL_MODES;
        $ttsVoices = Campaign::TTS_VOICES;
        $recChannels = Campaign::REC_CHANNELS;

        return view('admin.campaigns.edit', compact('campaign', 'types', 'dialModes', 'ttsVoices', 'recChannels'));
    }

    public function update(Request $request, Campaign $campaign)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
            'type' => ['required', 'in:'.implode(',', Campaign::TYPES)],
            'dial_mode' => ['required', 'in:'.implode(',', Campaign::DIAL_MODES)],
            'dial_level' => 'nullable|numeric|min:0|max:9999',
            'caller_id' => 'nullable|string|max:50',
            'acw_seconds' => 'nullable|integer|min:0|max:3600',
            'hopper_level' => 'nullable|integer|min:0|max:9999',
            'max_calls' => 'nullable|integer|min:0',
            'script' => 'nullable|string',
            'is_active' => 'boolean',
            'tts_voice' => ['nullable', 'in:'.implode(',', Campaign::TTS_VOICES)],
            'tts_language' => 'nullable|string|max:20',
            'greeting_message' => 'nullable|string|max:500',
            'hold_music_url' => 'nullable|url|max:1000',
            'tts_completed' => 'nullable|string|max:500',
            'tts_busy' => 'nullable|string|max:500',
            'tts_no_answer' => 'nullable|string|max:500',
            'tts_failed' => 'nullable|string|max:500',
            'tts_canceled' => 'nullable|string|max:500',
            'recording_enabled' => 'boolean',
            'recording_channels' => ['nullable', 'in:'.implode(',', Campaign::REC_CHANNELS)],
        ]);

        if ($validator->fails()) {
            if ($request->expectsJson()) {
                return response()->json(['errors' => $validator->errors()], 422);
            }

            return back()->withErrors($validator)->withInput();
        }

        $data = $validator->validated();
        $data['is_active'] = $request->boolean('is_active');
        $data['recording_enabled'] = $request->boolean('recording_enabled');

        $campaign->update($data);

        if ($request->expectsJson()) {
            return response()->json(['success' => true, 'campaign' => $campaign->fresh()]);
        }

        return redirect()->route('admin.campaigns.index')
            ->with('success', 'Campaign updated successfully.');
    }

    public function destroy(Campaign $campaign)
    {
        $campaign->delete();

        return redirect()->route('admin.campaigns.index')
            ->with('success', 'Campaign deleted.');
    }
}
