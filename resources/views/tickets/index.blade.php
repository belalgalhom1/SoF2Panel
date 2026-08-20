@extends('layouts.app')

@section('title', 'Support Tickets')

@section('content')
<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem;">
    <div>
        <h1 class="page-title">Support Tickets</h1>
        <p class="subtitle">Manage and reply to support inquiries.</p>
    </div>
    <a href="{{ route('tickets.create') }}" class="btn btn-primary" style="width: auto;">
        <i data-feather="plus" style="width: 16px; height: 16px; margin-right: 0.5rem;"></i> Create Ticket
    </a>
</div>

<div class="table-container">
    <div class="table-responsive">
        <table class="panel-table">
            <thead>
                <tr>
                    <th style="width: 50px;">ID</th>
                    @if(auth()->user()->isAdmin())
                        <th>User</th>
                    @endif
                    <th>Subject</th>
                    <th>Category</th>
                    <th>Server</th>
                    <th>Status</th>
                    <th>Date</th>
                    <th style="text-align: right;">Action</th>
                </tr>
            </thead>
            <tbody>
                @forelse($tickets as $ticket)
                    <tr style="cursor: pointer;" onclick="window.location='{{ route('tickets.show', $ticket) }}'">
                        <td style="color: var(--text-muted);">#{{ $ticket->id }}</td>
                        
                        @if(auth()->user()->isAdmin())
                            <td>
                                <div style="display: flex; align-items: center; gap: 0.75rem;">
                                    <div style="width: 32px; height: 32px; border-radius: 50%; background: var(--bg-modifier-hover); display: flex; align-items: center; justify-content: center; font-weight: 600; color: var(--primary);">
                                        {{ strtoupper(substr($ticket->user->username, 0, 1)) }}
                                    </div>
                                    <div style="font-weight: 500;">{{ $ticket->user->username }}</div>
                                </div>
                            </td>
                        @endif

                        <td style="font-weight: 500;">
                            {{ $ticket->subject }}
                        </td>
                        
                        <td>
                            <span class="badge" style="background: rgba(255,255,255,0.05); color: var(--text-muted);">
                                {{ $ticket->category }}
                            </span>
                        </td>

                        <td>
                            @if($ticket->server)
                                <div style="display: flex; align-items: center; gap: 0.5rem; color: var(--text-muted);">
                                    <i data-feather="server" style="width: 14px; height: 14px;"></i>
                                    {{ $ticket->server->name }}
                                </div>
                            @else
                                <span style="color: var(--text-muted);">-</span>
                            @endif
                        </td>

                        <td>
                            @if($ticket->status === 'Open')
                                <span class="badge" style="background: rgba(16, 185, 129, 0.15); color: #34d399;">Open</span>
                            @elseif($ticket->status === 'Solved')
                                <span class="badge" style="background: rgba(59, 130, 246, 0.15); color: #60a5fa;">Solved</span>
                            @else
                                <span class="badge" style="background: rgba(239, 68, 68, 0.15); color: #f87171;">Closed</span>
                            @endif
                        </td>
                        
                        <td style="color: var(--text-muted);">{{ $ticket->created_at->diffForHumans() }}</td>
                        
                        <td style="text-align: right;">
                            <a href="{{ route('tickets.show', $ticket) }}" class="btn" style="width: auto; padding: 0.4rem 1rem; background: rgba(99,102,241,0.1); border: 1px solid var(--primary); color: var(--primary); font-size: 0.85rem;">
                                View
                            </a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="{{ auth()->user()->isAdmin() ? '8' : '7' }}" style="text-align: center; padding: 3rem 1rem; color: var(--text-muted);">
                            <i data-feather="inbox" style="width: 48px; height: 48px; margin-bottom: 1rem; opacity: 0.5;"></i>
                            <p>No tickets found.</p>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<div style="margin-top: 2rem;">
    {{ $tickets->links() }}
</div>
@endsection
