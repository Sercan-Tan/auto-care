<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Inertia\Inertia;

class UserController extends Controller
{
    public function __construct()
    {
        // Constructor içinde middleware tanımı kaldırıldı
    }

    /**
     * Kullanıcıların listesini göster
     */
    public function index()
    {
        // Admin yetkisi kontrolü
        $this->checkAdminAccess();

        $users = User::all();
        return Inertia::render('Users/Index', [
            'users' => $users
        ]);
    }

    /**
     * Kullanıcı oluşturma formunu göster
     */
    public function create()
    {
        // Admin yetkisi kontrolü
        $this->checkAdminAccess();

        return Inertia::render('Users/Create');
    }

    /**
     * Yeni kullanıcıyı veritabanına kaydet
     */
    public function store(Request $request)
    {
        // Admin yetkisi kontrolü
        $this->checkAdminAccess();

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8|confirmed',
            'role' => 'required|in:admin,service',
        ]);

        User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'role' => $validated['role'],
        ]);

        return redirect()->route('users.index')->with('message', 'Kullanıcı başarıyla oluşturuldu.');
    }

    /**
     * Kullanıcı düzenleme formunu göster
     */
    public function edit(User $user)
    {
        // Admin yetkisi kontrolü
        $this->checkAdminAccess();

        return Inertia::render('Users/Edit', [
            'user' => $user
        ]);
    }

    /**
     * Kullanıcı bilgilerini güncelle
     */
    public function update(Request $request, User $user)
    {
        // Admin yetkisi kontrolü
        $this->checkAdminAccess();

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email,' . $user->id,
            'password' => $request->filled('password') ? 'string|min:8|confirmed' : '',
            'role' => 'required|in:admin,service',
        ]);

        $updateData = [
            'name' => $validated['name'],
            'email' => $validated['email'],
            'role' => $validated['role'],
        ];

        // Eğer şifre alanı doldurulmuşsa güncelle
        if ($request->filled('password')) {
            $updateData['password'] = Hash::make($validated['password']);
        }

        $user->update($updateData);

        return redirect()->route('users.index')->with('message', 'Kullanıcı başarıyla güncellendi.');
    }

    /**
     * Kullanıcıyı sil
     */
    public function destroy(User $user)
    {
        // Admin yetkisi kontrolü
        $this->checkAdminAccess();

        // Kendini silmeye çalışan kullanıcıyı engelle
        if (auth()->id() === $user->id) {
            return redirect()->route('users.index')->with('error', 'Kendinizi silemezsiniz.');
        }

        $user->delete();
        return redirect()->route('users.index')->with('message', 'Kullanıcı başarıyla silindi.');
    }

    /**
     * Admin yetkisi kontrolü yapan yardımcı method
     */
    private function checkAdminAccess()
    {
        if (auth()->check() && auth()->user()->role !== 'admin') {
            abort(403, 'Bu sayfaya erişim yetkiniz yok.');
        }
    }
}
