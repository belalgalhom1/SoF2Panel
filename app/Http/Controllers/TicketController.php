<?php

namespace App\Http\Controllers;

use App\Models\Ticket;
use App\Models\TicketMessage;
use Illuminate\Http\Request;

class TicketController extends Controller
{
    public function index()
    {
        if (auth()->user()->isAdmin()) {
            $tickets = Ticket::with(['user', 'server'])->orderByRaw("FIELD(status, 'Open') DESC")->latest()->paginate(20);
        } else {
            $tickets = Ticket::where('user_id', auth()->id())->with(['server'])->latest()->paginate(20);
        }

        return view('tickets.index', compact('tickets'));
    }

    public function create()
    {
        if (auth()->user()->isAdmin()) {
            $servers = \App\Models\Server::all();
        } else {
            $servers = auth()->user()->servers;
        }
        return view('tickets.create', compact('servers'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'category' => 'required|string|max:255',
            'server_id' => 'nullable|exists:servers,id',
            'subject' => 'required|string|max:255',
            'message' => 'required|string',
        ]);

        if ($request->server_id && !auth()->user()->isAdmin()) {
            if (!auth()->user()->servers()->where('servers.id', $request->server_id)->exists()) {
                abort(403);
            }
        }

        $ticket = Ticket::create([
            'user_id' => auth()->id(),
            'server_id' => $request->server_id,
            'category' => $request->category,
            'subject' => $request->subject,
            'status' => 'Open',
        ]);

        $ticket->messages()->create([
            'user_id' => auth()->id(),
            'message' => $request->message,
        ]);

        return redirect()->route('tickets.show', $ticket)->with('success', 'Support ticket created successfully.');
    }

    public function show(Ticket $ticket)
    {
        if (!auth()->user()->isAdmin() && $ticket->user_id !== auth()->id()) {
            abort(403);
        }

        $ticket->load(['messages.user', 'server']);
        return view('tickets.show', compact('ticket'));
    }

    public function reply(Request $request, Ticket $ticket)
    {
        if (!auth()->user()->isAdmin() && $ticket->user_id !== auth()->id()) {
            abort(403);
        }

        $request->validate([
            'message' => 'required|string',
        ]);

        $ticket->messages()->create([
            'user_id' => auth()->id(),
            'message' => $request->message,
        ]);

        if (!auth()->user()->isAdmin()) {
            $ticket->update(['status' => 'Open']);
        }

        return back()->with('success', 'Reply sent successfully.');
    }

    public function updateStatus(Request $request, Ticket $ticket)
    {
        if (!auth()->user()->isAdmin()) {
            abort(403);
        }

        $request->validate([
            'status' => 'required|in:Open,Solved,Closed'
        ]);

        $ticket->update(['status' => $request->status]);

        return back()->with('success', 'Ticket status updated.');
    }

    public function close(Request $request, Ticket $ticket)
    {
        if (!auth()->user()->isAdmin() && $ticket->user_id !== auth()->id()) {
            abort(403);
        }

        $request->validate([
            'reason' => 'nullable|string|max:500'
        ]);

        $ticket->update(['status' => 'Closed']);

        if ($request->filled('reason')) {
            $ticket->messages()->create([
                'user_id' => auth()->id(),
                'message' => "Ticket closed. Reason: " . $request->reason,
            ]);
        }

        return back()->with('success', 'Ticket closed successfully.');
    }

    public function destroy(Ticket $ticket)
    {
        if (!auth()->user()->isAdmin()) {
            abort(403);
        }

        $ticket->delete();

        return redirect()->route('tickets.index')->with('success', 'Ticket deleted successfully.');
    }
}
