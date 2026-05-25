<?php

namespace App\Http\Controllers;

use App\Models\Agent;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class AgentController extends Controller
{
    public function index(Request $request)
    {
        $agents = Agent::query()
            ->when($request->filled('q'), function ($query) use ($request) {
                $query->where('username', 'like', '%' . $request->q . '%')
                    ->orWhere('remarks', 'like', '%' . $request->q . '%');
            })
            ->orderBy('username')
            ->paginate(30)
            ->withQueryString();

        return view('agents.index', compact('agents'));
    }

    public function create()
    {
        return view('agents.form', ['agent' => new Agent()]);
    }

    public function store(Request $request)
    {
        Agent::create($this->validated($request));

        return redirect()->route('agents.index')->with('status', 'Agent created.');
    }

    public function edit(Agent $agent)
    {
        return view('agents.form', compact('agent'));
    }

    public function update(Request $request, Agent $agent)
    {
        $agent->update($this->validated($request, $agent));

        return redirect()->route('agents.index')->with('status', 'Agent updated.');
    }

    public function destroy(Agent $agent)
    {
        $agent->delete();

        return redirect()->route('agents.index')->with('status', 'Agent deleted.');
    }

    public function search(Request $request)
    {
        $agents = Agent::query()
            ->where('is_active', true)
            ->where('username', 'like', '%' . $request->get('q', '') . '%')
            ->orderBy('username')
            ->limit(12)
            ->get(['id', 'username', 'default_bet_amount', 'my_percent', 'run_ticket', 'bet_amount_locked']);

        return response()->json($agents);
    }

    public function quickCreate(Request $request)
    {
        $data = $request->validate([
            'username' => ['required', 'string', 'max:255', 'unique:agents,username'],
        ]);

        $agent = Agent::create([
            'username' => $data['username'],
            'default_bet_amount' => 0,
            'my_percent' => 1,
            'run_ticket' => 0,
            'is_active' => true,
        ]);

        return response()->json($agent);
    }

    private function validated(Request $request, ?Agent $agent = null): array
    {
        $agentId = $agent?->id;

        return $request->validate([
            'username' => ['required', 'string', 'max:255', Rule::unique('agents', 'username')->ignore($agentId)],
            'default_bet_amount' => ['required', 'numeric'],
            'my_percent' => ['required', 'numeric'],
            'run_ticket' => ['required', 'numeric'],
            'remarks' => ['nullable', 'string'],
            'is_active' => ['nullable', 'boolean'],
            'bet_amount_locked' => ['nullable', 'boolean'],
        ]) + [
            'is_active' => $request->boolean('is_active'),
            'bet_amount_locked' => $request->boolean('bet_amount_locked'),
        ];
    }
}
