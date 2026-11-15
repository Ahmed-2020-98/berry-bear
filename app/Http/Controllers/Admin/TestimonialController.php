<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Testimonial;
use Illuminate\Http\Request;

class TestimonialController extends Controller
{
    public function index()
    {
        return view('admin.testimonials.index', ['testimonials' => Testimonial::ordered()->get()]);
    }

    public function create()
    {
        return view('admin.testimonials.form', ['t' => new Testimonial(['is_published' => true, 'rating' => 5, 'role' => 'عميل', 'avatar_color' => '#E51B6E'])]);
    }

    public function store(Request $request)
    {
        Testimonial::create($this->data($request) + ['sort' => (int) Testimonial::max('sort') + 1]);

        return redirect()->route('admin.testimonials.index')->with('success', 'تمت إضافة الرأي.');
    }

    public function edit(Testimonial $testimonial)
    {
        return view('admin.testimonials.form', ['t' => $testimonial]);
    }

    public function update(Request $request, Testimonial $testimonial)
    {
        $testimonial->update($this->data($request));

        return redirect()->route('admin.testimonials.index')->with('success', 'تم حفظ الرأي.');
    }

    public function destroy(Testimonial $testimonial)
    {
        $testimonial->delete();

        return redirect()->route('admin.testimonials.index')->with('success', 'تم حذف الرأي.');
    }

    protected function data(Request $request): array
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:150'],
            'text' => ['required', 'string', 'max:3000'],
            'role' => ['nullable', 'string', 'max:100'],
            'avatar_color' => ['nullable', 'regex:/^#([0-9a-f]{3}|[0-9a-f]{6})$/i'],
            'rating' => ['required', 'integer', 'between:1,5'],
        ], [], ['name' => 'الاسم', 'text' => 'نص الرأي', 'rating' => 'التقييم', 'avatar_color' => 'اللون']);

        return $data + ['is_published' => $request->boolean('is_published')];
    }
}
