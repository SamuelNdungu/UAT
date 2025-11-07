<?php
namespace App\Http\Controllers;

use App\Models\Agent;
use App\Http\Requests\StoreAgentRequest;
use App\Http\Requests\UpdateAgentRequest;
use Illuminate\Http\Request;

class AgentController extends Controller
{
    public function index()
    {
        $agents = Agent::paginate(20);
        return view('settings.agents.index', compact('agents'));
    }

    public function create()
    {
        return view('settings.agents.create');
    }

    public function store(StoreAgentRequest $request)
    {
        $data = $request->validated();
        $data['user_id'] = auth()->id();
        Agent::create($data);
        return redirect()->route('settings.agents.index')->with('success', 'Agent created.');
    }

    public function edit(Agent $agent)
    {
        return view('settings.agents.edit', compact('agent'));
    }

    public function update(UpdateAgentRequest $request, Agent $agent)
    {
        $agent->update($request->validated());
        return redirect()->route('settings.agents.index')->with('success', 'Agent updated.');
    }

    public function destroy(Agent $agent)
    {
        $agent->delete();
        return redirect()->route('settings.agents.index')->with('success', 'Agent deleted.');
    }
}
