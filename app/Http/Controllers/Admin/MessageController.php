<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ContactMessage;
use Illuminate\Http\Request;

class MessageController extends Controller
{
    public function index(Request $request)
    {
        $messages = ContactMessage::query()
            ->when($request->filter === 'unread', fn ($q) => $q->whereNull('read_at'))
            ->when($request->filled('subject'), fn ($q) => $q->where('subject', $request->subject))
            ->when($request->filled('q'), fn ($q) => $q->where(fn ($w) => $w
                ->where('name', 'like', '%'.$request->q.'%')
                ->orWhere('email', 'like', '%'.$request->q.'%')
                ->orWhere('phone', 'like', '%'.$request->q.'%')))
            ->latest()->paginate(25)->withQueryString();

        return view('admin.messages.index', ['messages' => $messages]);
    }

    public function show(ContactMessage $message)
    {
        if (! $message->read_at) {
            $message->update(['read_at' => now()]);
        }

        return view('admin.messages.show', ['message' => $message]);
    }

    public function toggle(ContactMessage $message)
    {
        $message->update(['read_at' => $message->read_at ? null : now()]);

        return back()->with('success', $message->read_at ? 'تم التعليم كمقروءة.' : 'تم التعليم كغير مقروءة.');
    }

    public function destroy(ContactMessage $message)
    {
        $message->delete();

        return redirect()->route('admin.messages.index')->with('success', 'تم حذف الرسالة.');
    }
}
