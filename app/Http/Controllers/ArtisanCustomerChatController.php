<?php

namespace App\Http\Controllers;

use App\Models\ArtisanCustomerChat;
use App\Models\Product;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class ArtisanCustomerChatController extends Controller
{
    public function customerIndex(Request $request): View
    {
        $chats = $request->user()->customerChats()
            ->with(['artisan', 'product', 'latestMessage'])
            ->orderByDesc('last_message_at')
            ->paginate(15);

        return view('chat.index', ['chats' => $chats, 'side' => 'customer']);
    }

    public function artisanIndex(Request $request): View
    {
        $chats = $request->user()->artisanChats()
            ->with(['customer', 'product', 'latestMessage'])
            ->orderByDesc('last_message_at')
            ->paginate(15);

        return view('chat.index', ['chats' => $chats, 'side' => 'artisan']);
    }

    public function startCustomerChat(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'product_id' => ['required', 'integer', 'exists:products,id'],
            'body' => ['required', 'string', 'max:2000'],
        ]);
        $product = Product::whereKey($data['product_id'])
            ->where('verification_status', Product::STATUS_APPROVED)
            ->firstOrFail();

        $chat = DB::transaction(function () use ($request, $data, $product): ArtisanCustomerChat {
            $chat = ArtisanCustomerChat::firstOrCreate([
                'customer_id' => $request->user()->id,
                'artisan_id' => $product->artisan_id,
                'product_id' => $product->id,
            ]);
            $chat->messages()->create([
                'sender_id' => $request->user()->id,
                'body' => $data['body'],
            ]);
            $chat->update(['last_message_at' => now()]);

            return $chat;
        });

        return redirect()->route('chat.customer.show', $chat)->with('success', 'Pesan terkirim ke perajin.');
    }

    public function customerShow(Request $request, ArtisanCustomerChat $chat): View
    {
        abort_unless($chat->customer_id === $request->user()->id, 404);

        return $this->showConversation($request, $chat, 'customer');
    }

    public function artisanShow(Request $request, ArtisanCustomerChat $chat): View
    {
        abort_unless($chat->artisan_id === $request->user()->id, 404);

        return $this->showConversation($request, $chat, 'artisan');
    }

    public function customerMessage(Request $request, ArtisanCustomerChat $chat): RedirectResponse
    {
        abort_unless($chat->customer_id === $request->user()->id, 404);

        return $this->sendMessage($request, $chat, 'chat.customer.show');
    }

    public function artisanMessage(Request $request, ArtisanCustomerChat $chat): RedirectResponse
    {
        abort_unless($chat->artisan_id === $request->user()->id, 404);

        return $this->sendMessage($request, $chat, 'artisan.chats.show');
    }

    private function showConversation(Request $request, ArtisanCustomerChat $chat, string $side): View
    {
        $chat->messages()
            ->whereNull('read_at')
            ->where('sender_id', '!=', $request->user()->id)
            ->update(['read_at' => now()]);
        $chat->load(['customer', 'artisan', 'product', 'messages.sender']);

        return view('chat.show', compact('chat', 'side'));
    }

    private function sendMessage(Request $request, ArtisanCustomerChat $chat, string $redirectRoute): RedirectResponse
    {
        $data = $request->validate(['body' => ['required', 'string', 'max:2000']]);
        $chat->messages()->create([
            'sender_id' => $request->user()->id,
            'body' => $data['body'],
        ]);
        $chat->update(['last_message_at' => now()]);

        return redirect()->route($redirectRoute, $chat)->with('success', 'Pesan terkirim.');
    }
}