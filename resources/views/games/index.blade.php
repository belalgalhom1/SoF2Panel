@extends('layouts.app')

@section('title', 'Games')

@section('content')
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem;">
        <h2 style="margin: 0;">Manage Games</h2>
        <a href="{{ route('games.create') }}" class="btn btn-primary" style="width: auto;">
            <i data-feather="plus" style="width: 16px; height: 16px; margin-right: 0.5rem;"></i> Add Game
        </a>
    </div>

    <div class="table-container">
        <div class="table-header">
            <h3>Supported Games</h3>
        </div>
        <div class="table-responsive">
        <table class="panel-table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Name</th>
                    <th>Base Location</th>
                    <th style="text-align: right;">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($games as $game)
                    <tr>
                        <td>{{ $game->id }}</td>
                        <td style="font-weight: 600;">{{ $game->name }}</td>
                        <td>{{ $game->base_location }}</td>
                        <td style="text-align: right;">
                            <a href="{{ route('games.edit', $game) }}" class="btn" style="padding: 0.5rem; width: auto; color: var(--primary);">
                                <i data-feather="edit-2" style="width: 16px; height: 16px;"></i>
                            </a>
                            <form action="{{ route('games.destroy', $game) }}" method="POST" style="display: inline-block;">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-primary" style="background: rgba(239, 68, 68, 0.1); color: var(--danger); border: 1px solid rgba(239, 68, 68, 0.2); padding: 0.5rem; width: auto;" onclick="return confirm('Delete this game? This will break associated servers.')" title="Delete Game">
                                    <i data-feather="trash-2" style="width: 16px; height: 16px;"></i>
                                </button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4" style="text-align: center; color: var(--text-muted); padding: 2rem;">No games added yet.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
        </div>
    </div>
@endsection
