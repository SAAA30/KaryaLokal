<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\SupportTicket;
use App\Support\DashboardChartSeries;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;

class SupportTicketController extends Controller
{
    public function customerIndex(Request $request): View
    {
        $tickets = $request->user()->supportTickets()->with('order')->latest()->paginate(15);

        return view('support.customer-index', compact('tickets'));
    }

    public function create(Request $request): View
    {
        $orders = $request->user()->customerOrders()->latest()->limit(50)->get();

        return view('support.create', compact('orders'));
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'order_id' => ['nullable', 'integer', 'exists:orders,id'],
            'category' => ['required', 'in:product,payment,shipping,qr,custom,return,refund,other'],
            'subject' => ['required', 'string', 'max:200'],
            'description' => ['required', 'string', 'max:5000'],
            'evidence' => ['nullable', 'array', 'max:5'],
            'evidence.*' => ['file', 'mimes:jpg,jpeg,png,mp4,pdf', 'max:10240'],
        ]);
        if (!empty($data['order_id'])) {
            $ownsOrder = Order::whereKey($data['order_id'])->where('customer_id', $request->user()->id)->exists();
            if (!$ownsOrder) {
                return back()->withErrors(['order_id' => 'Pesanan tersebut tidak ditemukan pada akunmu.'])->withInput();
            }
        }

        $evidence = collect($request->file('evidence', []))->map(fn ($file) => $file->store('support-evidence', 'public'))->all();
        $ticket = SupportTicket::create([
            'ticket_number' => $this->newTicketNumber(),
            'customer_id' => $request->user()->id,
            'order_id' => $data['order_id'] ?? null,
            'category' => $data['category'],
            'subject' => $data['subject'],
            'description' => $data['description'],
            'evidence' => $evidence,
            'status' => 'open',
        ]);

        return redirect()->route('support.customer.show', $ticket)->with('success', 'Tiket bantuan berhasil dibuat.');
    }

    public function show(Request $request, SupportTicket $ticket): View
    {
        abort_unless(
            $ticket->customer_id === $request->user()->id || in_array($request->user()->role, ['customer_service', 'admin'], true),
            404
        );
        $ticket->load(['customer', 'order', 'assignee']);

        return view('support.show', compact('ticket'));
    }

    public function dashboard(Request $request): View
    {
        $metrics = [
            'open' => SupportTicket::where('status', 'open')->count(),
            'in_progress' => SupportTicket::where('status', 'in_progress')->count(),
            'waiting_customer' => SupportTicket::where('status', 'waiting_customer')->count(),
            'resolved' => SupportTicket::where('status', 'resolved')->count(),
        ];
        $filters = $request->validate([
            'status' => ['nullable', 'in:open,in_progress,waiting_customer,resolved'],
        ]);
        $selectedStatus = $filters['status'] ?? null;
        $ticketQuery = SupportTicket::with(['customer', 'order'])
            ->when($selectedStatus, fn ($query) => $query->where('status', $selectedStatus))
            ->when(!$selectedStatus, fn ($query) => $query->whereIn('status', ['open', 'in_progress', 'waiting_customer']))
            ->oldest();
        $tickets = $ticketQuery->paginate(15)->withQueryString();
        $monthExpression = DashboardChartSeries::monthExpression();
        $monthlyTicketRows = SupportTicket::query()
            ->selectRaw("{$monthExpression} as month, count(*) as total")
            ->where('created_at', '>=', now()->subMonths(5)->startOfMonth())
            ->groupBy('month')
            ->orderBy('month')
            ->get();
        $monthlyTicketSeries = DashboardChartSeries::sixMonths($monthlyTicketRows);

        return view('support.dashboard', compact('metrics', 'tickets', 'monthlyTicketSeries', 'selectedStatus'));
    }

    public function index(Request $request): View
    {
        $filters = $request->validate([
            'status' => ['nullable', 'in:active,open,in_progress,waiting_customer,resolved'],
        ]);
        $selectedStatus = $filters['status'] ?? null;
        $tickets = SupportTicket::with(['customer', 'order'])
            ->when($selectedStatus === 'active', fn ($query) => $query->whereIn('status', ['open', 'in_progress', 'waiting_customer']))
            ->when($selectedStatus && $selectedStatus !== 'active', fn ($query) => $query->where('status', $selectedStatus))
            ->latest()
            ->paginate(10)
            ->withQueryString();

        return view('support.tickets-index', compact('tickets', 'selectedStatus'));
    }

    public function update(Request $request, SupportTicket $ticket): RedirectResponse
    {
        $data = $request->validate([
            'status' => ['required', 'in:in_progress,waiting_customer,resolved'],
            'resolution' => ['nullable', 'string', 'max:2000', 'required_if:status,resolved'],
        ]);
        $ticket->update([
            'status' => $data['status'],
            'resolution' => $data['resolution'] ?? null,
            'assigned_to' => $ticket->assigned_to ?: $request->user()->id,
            'closed_at' => $data['status'] === 'resolved' ? now() : null,
        ]);

        return back()->with('success', 'Status tiket diperbarui.');
    }

    private function newTicketNumber(): string
    {
        do {
            $number = 'CS-' . now()->format('ymd') . '-' . Str::upper(Str::random(6));
        } while (SupportTicket::where('ticket_number', $number)->exists());

        return $number;
    }
}
