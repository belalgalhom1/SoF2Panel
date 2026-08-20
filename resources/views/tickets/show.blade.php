@extends('layouts.app')

@section('title', 'Ticket #' . $ticket->id)

@section('content')

<div style="width: 100%;">
    
    <a href="{{ route('tickets.index') }}" style="color: var(--text-muted); text-decoration: none; font-size: 0.875rem; display: inline-flex; align-items: center; margin-bottom: 1rem; transition: color 0.2s;" onmouseover="this.style.color='#fff'" onmouseout="this.style.color='var(--text-muted)'">
        <i data-feather="arrow-left" style="width: 16px; height: 16px; margin-right: 0.5rem;"></i> Back to Tickets
    </a>

    <div class="glass-panel" style="max-width: 100%; padding: 0; display: flex; flex-direction: column; height: calc(100vh - 180px); min-height: 600px; overflow: hidden; border-radius: 16px; box-shadow: 0 25px 50px -12px rgba(0,0,0,0.5);">
        
        {{-- Chat Header --}}
        <div style="padding: 1.5rem; border-bottom: 1px solid var(--border); background: rgba(0,0,0,0.2); display: flex; justify-content: space-between; align-items: center;">
            <div>
                <h2 style="margin: 0 0 0.5rem; font-size: 1.5rem; display: flex; align-items: center; gap: 0.75rem;">
                    {{ $ticket->subject }}
                    @if($ticket->status === 'Open')
                        <span class="badge" style="background: rgba(16, 185, 129, 0.15); color: #34d399; font-size: 0.75rem; border: 1px solid rgba(16, 185, 129, 0.3);">OPEN</span>
                    @elseif($ticket->status === 'Solved')
                        <span class="badge" style="background: rgba(59, 130, 246, 0.15); color: #60a5fa; font-size: 0.75rem; border: 1px solid rgba(59, 130, 246, 0.3);">SOLVED</span>
                    @else
                        <span class="badge" style="background: rgba(239, 68, 68, 0.15); color: #f87171; font-size: 0.75rem; border: 1px solid rgba(239, 68, 68, 0.3);">CLOSED</span>
                    @endif
                </h2>
                <div style="display: flex; gap: 1.5rem; color: var(--text-muted); font-size: 0.85rem; font-weight: 500;">
                    <div style="display: flex; align-items: center; gap: 0.4rem;">
                        <i data-feather="tag" style="width: 14px; height: 14px;"></i>
                        {{ $ticket->category }}
                    </div>
                    @if($ticket->server)
                    <div style="display: flex; align-items: center; gap: 0.4rem;">
                        <i data-feather="server" style="width: 14px; height: 14px;"></i>
                        {{ $ticket->server->name }}
                    </div>
                    @endif
                    <div style="display: flex; align-items: center; gap: 0.4rem;">
                        <i data-feather="user" style="width: 14px; height: 14px;"></i>
                        {{ $ticket->user->username }}
                    </div>
                </div>
            </div>
            
            <div style="display: flex; align-items: center; gap: 1rem;">
                @if(auth()->user()->isAdmin())
                    <form action="{{ route('tickets.status', $ticket) }}" method="POST" style="margin: 0; display: flex; align-items: center;">
                        @csrf
                        <select name="status" class="form-input" style="padding: 0.4rem 2rem 0.4rem 1rem; width: auto; font-size: 0.85rem; background-color: rgba(0,0,0,0.3); border-right: none; border-radius: 8px 0 0 8px;" onchange="this.form.submit()">
                            <option value="Open" {{ $ticket->status === 'Open' ? 'selected' : '' }}>Status: Open</option>
                            <option value="Solved" {{ $ticket->status === 'Solved' ? 'selected' : '' }}>Status: Solved</option>
                            <option value="Closed" {{ $ticket->status === 'Closed' ? 'selected' : '' }}>Status: Closed</option>
                        </select>
                    </form>
                    <form action="{{ route('tickets.destroy', $ticket) }}" method="POST" style="margin: 0; display: flex; align-items: center;" onsubmit="return confirm('Are you sure you want to permanently delete this ticket?')">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn" style="width: auto; padding: 0.4rem 0.75rem; font-size: 0.85rem; background-color: rgba(239, 68, 68, 0.1); border: 1px solid rgba(239, 68, 68, 0.3); color: var(--danger); border-radius: 0 8px 8px 0; border-left: none; transition: all 0.2s;" onmouseover="this.style.background='var(--danger)'; this.style.color='#fff'" onmouseout="this.style.background='rgba(239, 68, 68, 0.1)'; this.style.color='var(--danger)'" title="Delete Ticket">
                            <i data-feather="trash-2" style="width: 16px; height: 16px;"></i>
                        </button>
                    </form>
                @else
                    @if(!in_array($ticket->status, ['Closed', 'Solved']))
                    <form action="{{ route('tickets.close', $ticket) }}" method="POST" style="margin: 0;" id="closeTicketForm">
                        @csrf
                        <input type="hidden" name="reason" id="closeTicketReason">
                        <button type="button" onclick="closeTicketWithReason()" class="btn" style="width: auto; background: rgba(239, 68, 68, 0.1); border: 1px solid var(--danger); color: var(--danger); padding: 0.5rem 1rem; font-size: 0.9rem; border-radius: 8px; transition: all 0.2s;" onmouseover="this.style.background='var(--danger)'; this.style.color='#fff'" onmouseout="this.style.background='rgba(239, 68, 68, 0.1)'; this.style.color='var(--danger)'">
                            <i data-feather="lock" style="width: 14px; height: 14px; margin-right: 0.4rem;"></i> Close Ticket
                        </button>
                    </form>
                    <script>
                        function closeTicketWithReason() {
                            document.getElementById('closeTicketModal').style.display = 'flex';
                            document.getElementById('modalReasonInput').focus();
                        }
                    </script>
                    @endif
                @endif
            </div>
        </div>

        {{-- Chat Messages Area --}}
        <div style="flex: 1; overflow-y: auto; padding: 2rem; display: flex; flex-direction: column; gap: 1.5rem; background: rgba(0,0,0,0.1);" id="chatContainer">
            @forelse($ticket->messages as $msg)
                @php
                    $isAdmin = $msg->user->isAdmin();
                    $isOwn   = $msg->user_id === auth()->id();
                @endphp

                <div style="display: flex; gap: 1rem; flex-direction: {{ $isOwn ? 'row-reverse' : 'row' }}; align-items: flex-end;">
                    {{-- Avatar --}}
                    <div style="width: 40px; height: 40px; border-radius: 50%; flex-shrink: 0; display: flex; align-items: center; justify-content: center; font-weight: 700; font-size: 0.95rem; color: #fff; background: {{ $isAdmin ? 'var(--primary)' : 'rgba(255,255,255,0.1)' }}; box-shadow: 0 4px 10px rgba(0,0,0,0.2);">
                        {{ strtoupper(substr($msg->user->username, 0, 1)) }}
                    </div>

                    {{-- Bubble Container --}}
                    <div style="max-width: 75%; display: flex; flex-direction: column; align-items: {{ $isOwn ? 'flex-end' : 'flex-start' }};">
                        <div style="font-size: 0.75rem; color: var(--text-muted); margin-bottom: 0.4rem; padding: 0 0.5rem;">
                            <span style="font-weight: 600; color: #fff;">{{ $msg->user->username }}</span>
                            @if($isAdmin)
                                <span style="background: var(--primary); color: #fff; font-size: 0.6rem; padding: 0.15rem 0.4rem; border-radius: 4px; margin-left: 0.25rem; font-weight: 700;">ADMIN</span>
                            @endif
                            <span style="margin-left: 0.5rem; opacity: 0.6;">{{ $msg->created_at->format('M d, H:i') }}</span>
                        </div>
                        
                        <div style="padding: 1rem 1.25rem; font-size: 0.95rem; line-height: 1.5; white-space: pre-wrap; word-break: break-word; text-align: left; box-shadow: 0 4px 15px rgba(0,0,0,0.1);
                            {{ $isOwn
                                ? 'background: linear-gradient(135deg, var(--primary), var(--secondary)); color: #fff; border-radius: 18px 18px 4px 18px;'
                                : 'background: rgba(255,255,255,0.08); border: 1px solid rgba(255,255,255,0.05); border-radius: 18px 18px 18px 4px;'
                            }}">{{ $msg->message }}</div>
                    </div>
                </div>
            @empty
                <div style="flex: 1; display: flex; flex-direction: column; align-items: center; justify-content: center; color: var(--text-muted); opacity: 0.5;">
                    <i data-feather="message-square" style="width: 48px; height: 48px; margin-bottom: 1rem;"></i>
                    <p style="margin: 0; font-size: 1.1rem;">No messages in this ticket yet.</p>
                </div>
            @endforelse
        </div>

        {{-- Reply Box Area --}}
        @if(!in_array($ticket->status, ['Closed', 'Solved']))
        <div style="padding: 1.5rem; border-top: 1px solid var(--border); background: rgba(0,0,0,0.2);">
            <form action="{{ route('tickets.reply', $ticket) }}" method="POST">
                @csrf
                <div style="display: flex; gap: 1rem; align-items: flex-end;">
                    <textarea name="message" id="chatInput" class="form-input" rows="1" placeholder="Type your reply..." required style="resize: none; border-radius: 12px; background: rgba(15, 17, 26, 0.7); overflow-y: hidden; padding-top: 0.8rem; padding-bottom: 0.8rem; height: 46px; transition: height 0.1s;" oninput="this.style.height = '46px'; this.style.height = Math.min(this.scrollHeight, 150) + 'px'"></textarea>
                    
                    <button type="submit" class="btn btn-primary" style="width: auto; padding: 0 1.5rem; border-radius: 8px; flex-shrink: 0; height: 46px; display: flex; align-items: center; justify-content: center;">
                        <span style="display: flex; align-items: center; font-size: 0.95rem;">
                            Send <i data-feather="send" style="width: 15px; height: 15px; margin-left: 0.5rem;"></i>
                        </span>
                    </button>
                </div>
            </form>
        </div>
        @else
        <div style="padding: 1.5rem; border-top: 1px solid var(--border); background: rgba(0,0,0,0.2); text-align: center; color: var(--text-muted);">
            <i data-feather="lock" style="width: 16px; height: 16px; margin-bottom: 0.5rem; opacity: 0.5;"></i>
            <p style="margin: 0; font-size: 0.9rem;">This ticket is marked as {{ strtolower($ticket->status) }} and cannot receive new replies.</p>
        </div>
        @endif
        
    </div>
</div>

<script>
    // Auto-scroll chat to bottom on load
    document.addEventListener('DOMContentLoaded', function() {
        const chatContainer = document.getElementById('chatContainer');
        if (chatContainer) {
            chatContainer.scrollTop = chatContainer.scrollHeight;
        }

        const chatInput = document.getElementById('chatInput');
        if (chatInput) {
            chatInput.addEventListener('keydown', function(e) {
                if (e.key === 'Enter' && !e.shiftKey) {
                    e.preventDefault();
                    if (this.value.trim() !== '') {
                        this.closest('form').submit();
                    }
                }
            });
        }
    });
</script>

<div id="closeTicketModal" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.8); z-index: 100; justify-content: center; align-items: center; backdrop-filter: var(--glass-blur);">
    <div class="glass-panel" style="width: 100%; max-width: 450px; padding: 2rem; box-shadow: 0 20px 40px rgba(0,0,0,0.5);">
        <h3 style="margin-top: 0; margin-bottom: 1rem; display: flex; align-items: center; gap: 0.5rem; color: #fff;">
            <i data-feather="lock" style="width: 20px; height: 20px; color: var(--danger);"></i> Close Ticket
        </h3>
        <p style="color: var(--text-muted); font-size: 0.95rem; margin-bottom: 1.5rem;">
            Are you sure you want to close this ticket? You can optionally provide a reason below.
        </p>
        <textarea id="modalReasonInput" class="form-input" rows="3" placeholder="Optional reason for closing..." style="width: 100%; margin-bottom: 1.5rem; border-radius: 8px; resize: none;"></textarea>
        <div style="display: flex; justify-content: flex-end; gap: 1rem;">
            <button type="button" class="btn" onclick="document.getElementById('closeTicketModal').style.display='none'" style="width: auto; border: 1px solid var(--border); color: var(--text-main); background: transparent;">Cancel</button>
            <button type="button" class="btn" style="width: auto; background: var(--danger); border: none; color: #fff;" onclick="submitCloseTicketModal()">Close Ticket</button>
        </div>
    </div>
</div>

<script>
    function submitCloseTicketModal() {
        const reason = document.getElementById('modalReasonInput').value;
        document.getElementById('closeTicketReason').value = reason;
        document.getElementById('closeTicketForm').submit();
    }
</script>

@endsection
