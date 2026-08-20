<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Game;

class GameController extends Controller
{
    public function index()
    {
        $games = Game::all();
        return view('games.index', compact('games'));
    }

    public function create()
    {
        return view('games.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'base_location' => 'required|string|max:255',
            'start_script' => 'required|string',
        ]);

        Game::create($request->only('name', 'base_location', 'start_script'));

        return redirect()->route('games.index')->with('success', 'Game added successfully.');
    }

    public function edit(Game $game)
    {
        return view('games.edit', compact('game'));
    }

    public function update(Request $request, Game $game)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'base_location' => 'required|string|max:255',
            'start_script' => 'required|string',
        ]);

        $game->update($request->only('name', 'base_location', 'start_script'));

        return redirect()->route('games.index')->with('success', 'Game updated successfully.');
    }

    public function destroy(Game $game)
    {
        $game->delete();
        return redirect()->route('games.index')->with('success', 'Game deleted successfully.');
    }
}
