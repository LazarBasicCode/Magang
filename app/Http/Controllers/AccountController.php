<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

/**
 * Pengaturan akun milik user yang sedang login (menu profil kanan atas):
 * ganti password & email pemulihan. Dipakai lewat fetch() dari
 * public/js/profile-account.js, jadi semua respons berupa JSON.
 */
class AccountController extends Controller
{
    public function changePassword(Request $request): JsonResponse
    {
        $data = $request->validate([
            'current_password'      => ['required', 'string'],
            'password'              => ['required', 'string', 'min:8', 'confirmed', 'different:current_password'],
        ], [
            'current_password.required' => 'Password lama wajib diisi.',
            'password.required'         => 'Password baru wajib diisi.',
            'password.min'              => 'Password baru minimal 8 karakter.',
            'password.confirmed'        => 'Konfirmasi password tidak cocok.',
            'password.different'        => 'Password baru tidak boleh sama dengan password lama.',
        ]);

        /** @var User $user */
        $user = $request->user();

        if (!Hash::check($data['current_password'], $user->password)) {
            return response()->json([
                'message' => 'Password lama salah.',
                'errors'  => ['current_password' => ['Password lama salah.']],
            ], 422);
        }

        $user->forceFill(['password' => Hash::make($data['password'])])->save();

        return response()->json(['message' => 'Password berhasil diganti.']);
    }

    public function updateRecoveryEmail(Request $request): JsonResponse
    {
        $data = $request->validate([
            'email' => [
                'required', 'string', 'email:rfc', 'max:255',
                Rule::unique('users', 'email')->ignore($request->user()->id),
            ],
            'password' => ['required', 'string'],
        ], [
            'email.required' => 'Email baru wajib diisi.',
            'email.email'    => 'Format email tidak valid (contoh: nama@domain.com).',
            'email.unique'   => 'Email ini sudah dipakai akun lain.',
            'password.required' => 'Password wajib diisi untuk konfirmasi.',
        ]);

        /** @var User $user */
        $user = $request->user();

        if (!Hash::check($data['password'], $user->password)) {
            return response()->json([
                'message' => 'Password salah.',
                'errors'  => ['password' => ['Password salah.']],
            ], 422);
        }

        $user->forceFill(['email' => strtolower($data['email'])])->save();

        return response()->json(['message' => 'Email pemulihan berhasil diperbarui.']);
    }
}
