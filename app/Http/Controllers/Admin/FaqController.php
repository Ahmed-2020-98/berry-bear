<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Faq;
use Illuminate\Http\Request;

class FaqController extends Controller
{
    public function index()
    {
        return view('admin.faqs.index', ['faqs' => Faq::ordered()->get()]);
    }

    public function create()
    {
        return view('admin.faqs.form', ['faq' => new Faq(['is_published' => true])]);
    }

    public function store(Request $request)
    {
        Faq::create($this->data($request) + ['sort' => (int) Faq::max('sort') + 1]);

        return redirect()->route('admin.faqs.index')->with('success', 'تمت إضافة السؤال.');
    }

    public function edit(Faq $faq)
    {
        return view('admin.faqs.form', ['faq' => $faq]);
    }

    public function update(Request $request, Faq $faq)
    {
        $faq->update($this->data($request));

        return redirect()->route('admin.faqs.index')->with('success', 'تم حفظ السؤال.');
    }

    public function destroy(Faq $faq)
    {
        $faq->delete();

        return redirect()->route('admin.faqs.index')->with('success', 'تم حذف السؤال.');
    }

    protected function data(Request $request): array
    {
        $data = $request->validate([
            'question' => ['required', 'string', 'max:255'],
            'answer' => ['required', 'string'],
        ], [], ['question' => 'السؤال', 'answer' => 'الإجابة']);

        return $data + ['is_published' => $request->boolean('is_published')];
    }
}
