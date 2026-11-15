<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

class UserController extends Controller
{
    public function index()
    {
        return view('admin.users.index', ['users' => User::orderBy('name')->get()]);
    }

    public function create()
    {
        return view('admin.users.form', ['user' => new User(['role' => 'editor', 'is_active' => true])]);
    }

    public function store(Request $request)
    {
        User::create($this->data($request, new User));

        return redirect()->route('admin.users.index')->with('success', 'تمت إضافة المستخدم.');
    }

    public function edit(User $user)
    {
        return view('admin.users.form', ['user' => $user]);
    }

    public function update(Request $request, User $user)
    {
        $user->update($this->data($request, $user));

        return redirect()->route('admin.users.index')->with('success', 'تم حفظ المستخدم.');
    }

    public function destroy(Request $request, User $user)
    {
        if ($user->is($request->user())) {
            return back()->with('error', 'لا يمكنك حذف حسابك.');
        }
        if ($user->isAdmin() && User::where('role', 'admin')->count() <= 1) {
            return back()->with('error', 'لا يمكن حذف آخر مدير.');
        }
        $user->delete();

        return redirect()->route('admin.users.index')->with('success', 'تم حذف المستخدم.');
    }

    protected function data(Request $request, User $user): array
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'email' => ['required', 'email', 'max:190', Rule::unique('users')->ignore($user->id)],
            'role' => ['required', Rule::in(array_keys(User::ROLES))],
            'password' => [$user->exists ? 'nullable' : 'required', 'confirmed', Password::min(8)],
        ], [], ['name' => 'الاسم', 'email' => 'البريد الإلكتروني', 'role' => 'الصلاحية', 'password' => 'كلمة المرور']);

        $data['is_active'] = $request->boolean('is_active');
        if (blank($data['password'] ?? null)) {
            unset($data['password']);
        }
        // You can't lock yourself out.
        if ($user->exists && $user->is($request->user())) {
            $data['role'] = $user->role;
            $data['is_active'] = true;
        }

        return $data;
    }
}
