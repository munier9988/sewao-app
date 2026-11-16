<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Database\QueryException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class UserController extends Controller
{
    public function index(Request $request): View
    {
        $search = trim((string) $request->query('q', ''));
        $roleFilter = $request->query('role', '');

        $users = User::query()
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%")
                        ->orWhere('wa_phone', 'like', "%{$search}%");
                });
            })
            ->when(in_array($roleFilter, ['admin', 'seller', 'pembeli'], true), function ($query) use ($roleFilter) {
                $query->where('role', $roleFilter);
            })
            ->latest('id')
            ->get();

        $roleCounts = User::selectRaw('role, COUNT(*) as total')
            ->groupBy('role')
            ->pluck('total', 'role');

        $stats = [
            'total' => $roleCounts->sum(),
            'admin' => (int) ($roleCounts['admin'] ?? 0),
            'seller' => (int) ($roleCounts['seller'] ?? 0),
            'pembeli' => (int) ($roleCounts['pembeli'] ?? 0),
        ];

        return view('admin.users.index', compact('users', 'stats', 'search', 'roleFilter'));
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validateUser($request);
        $data['password'] = Hash::make($data['password']);

        User::create($data);

        return redirect()->route('admin.users.index')
            ->with('success', 'Pengguna baru berhasil ditambahkan.');
    }

    public function update(Request $request, User $user): RedirectResponse
    {
        $data = $this->validateUser($request, $user);

        if (! empty($data['password'])) {
            $data['password'] = Hash::make($data['password']);
        } else {
            unset($data['password']);
        }

        $user->update($data);

        return redirect()->route('admin.users.index')
            ->with('success', "Data pengguna {$user->name} berhasil diperbarui.");
    }

    public function destroy(Request $request, User $user): RedirectResponse
    {
        if ($user->id === $request->user()->id) {
            return redirect()->route('admin.users.index')
                ->with('error', 'Anda tidak dapat menghapus akun sendiri yang sedang digunakan.');
        }

        try {
            $name = $user->name;
            $user->delete();

            return redirect()->route('admin.users.index')
                ->with('success', "Pengguna {$name} berhasil dihapus.");
        } catch (QueryException) {
            return redirect()->route('admin.users.index')
                ->with('error', 'Pengguna tidak dapat dihapus karena masih memiliki data transaksi atau produk terkait.');
        }
    }

    /**
     * Validate and normalize user payload for both create and update.
     */
    private function validateUser(Request $request, ?User $user = null): array
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'email' => ['required', 'email', 'max:120', Rule::unique('users', 'email')->ignore($user?->id)],
            'role' => ['required', Rule::in(['admin', 'seller', 'pembeli'])],
            'wa_phone' => ['nullable', 'string', 'max:20'],
            'password' => [$user ? 'nullable' : 'required', 'string', 'min:6'],
        ]);

        $waPhone = User::normalizeWaPhone($validated['wa_phone'] ?? null);

        if ($validated['role'] === 'seller' && empty($waPhone)) {
            throw ValidationException::withMessages([
                'wa_phone' => 'Nomor WhatsApp wajib diisi untuk akun Seller.',
            ]);
        }

        if ($waPhone !== null && ! preg_match('/^628\d{7,15}$/', $waPhone)) {
            throw ValidationException::withMessages([
                'wa_phone' => 'Format nomor WhatsApp tidak valid. Gunakan awalan 08 atau 628 (minimal 10 digit).',
            ]);
        }

        $validated['wa_phone'] = $waPhone;

        return $validated;
    }
}
