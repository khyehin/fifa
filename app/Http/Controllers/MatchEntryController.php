<?php

namespace App\Http\Controllers;

use App\Models\FootballMatch;
use App\Models\MatchEntry;
use App\Models\Agent;
use Illuminate\Http\Request;

class MatchEntryController extends Controller
{
    public function store(Request $request, FootballMatch $match)
    {
        $data = $this->validated($request);
        $agent = Agent::findOrFail($data['agent_id']);

        $entry = MatchEntry::updateOrCreate(
            ['football_match_id' => $match->id, 'agent_id' => $agent->id],
            $this->entryData($data, $agent)
        )->load('agent');

        return response()->json($this->payload($entry));
    }

    public function update(Request $request, MatchEntry $entry)
    {
        $data = $this->validated($request, false);
        $entry->load('agent');
        if ($entry->agent->bet_amount_locked) {
            unset($data['bet_amount']);
        }
        $entry->update($data);
        $entry->refresh()->load('agent');

        return response()->json($this->payload($entry));
    }

    public function destroy(MatchEntry $entry)
    {
        $entry->delete();

        return response()->json(['deleted' => true]);
    }

    private function validated(Request $request, bool $requireUser = true): array
    {
        $request->merge([
            'bet_amount' => $this->numericValue($request->input('bet_amount')),
            'ha' => $this->numericValue($request->input('ha')),
            'ou' => $this->numericValue($request->input('ou')),
            'black_red_amount' => $this->numericValue($request->input('black_red_amount')),
            'my_percent' => $this->numericValue($request->input('my_percent')),
            'rebate_percent' => $this->numericValue($request->input('rebate_percent')),
            'run_ticket' => $this->numericValue($request->input('run_ticket')),
        ]);

        $rules = [
            'bet_amount' => ['nullable', 'numeric'],
            'ha' => ['nullable', 'numeric'],
            'ou' => ['nullable', 'numeric'],
            'black_red_amount' => ['nullable', 'numeric'],
            'my_percent' => ['nullable', 'numeric'],
            'rebate_percent' => ['nullable', 'numeric'],
            'run_ticket' => ['nullable', 'numeric'],
            'remarks' => ['nullable', 'string'],
        ];

        if ($requireUser) {
            $rules['agent_id'] = ['required', 'exists:agents,id'];
        }

        return $request->validate($rules);
    }

    private function numericValue(mixed $value): mixed
    {
        if ($value === null || $value === '') {
            return $value;
        }

        $text = trim((string) $value);
        $negative = str_starts_with($text, '(') && str_ends_with($text, ')');
        $number = str_replace([',', '(', ')'], '', $text);

        return $negative ? '-' . $number : $number;
    }

    private function entryData(array $data, Agent $agent): array
    {
        $entry = [
            'bet_amount' => $data['bet_amount'] ?? $agent->default_bet_amount,
            'ha' => $data['ha'] ?? null,
            'ou' => $data['ou'] ?? null,
            'black_red_amount' => $data['black_red_amount'] ?? 0,
            'my_percent' => $data['my_percent'] ?? $agent->my_percent,
            'rebate_percent' => $data['rebate_percent'] ?? 0,
            'run_ticket' => $data['run_ticket'] ?? $agent->run_ticket,
            'remarks' => $data['remarks'] ?? null,
        ];

        if ($agent->bet_amount_locked) {
            $entry['bet_amount'] = $agent->default_bet_amount;
        }

        return $entry;
    }

    private function payload(MatchEntry $entry): array
    {
        return [
            'id' => $entry->id,
            'agent' => $entry->agent->username,
            'bet_amount' => number_format((float) $entry->bet_amount, 2, '.', ''),
            'black_red_amount' => number_format((float) $entry->black_red_amount, 2, '.', ''),
            'my_percent' => number_format((float) $entry->my_percent, 4, '.', ''),
            'bet_share' => number_format((float) $entry->bet_share, 2, '.', ''),
            'my_winlose' => number_format((float) $entry->my_winlose, 2, '.', ''),
            'rebate_percent' => number_format((float) $entry->rebate_percent, 4, '.', ''),
            'rebate_amount' => number_format((float) $entry->rebate_amount, 2, '.', ''),
            'run_ticket' => number_format((float) $entry->run_ticket, 2, '.', ''),
            'net_total' => number_format((float) $entry->net_total, 2, '.', ''),
        ];
    }
}
