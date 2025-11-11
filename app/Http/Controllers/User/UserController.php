<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    // ==================== USER MANAGEMENT ====================

    public function index(Request $request)
    {
        $tab = $request->query('tab', 'users'); // 'users' or 'roles'

        // Get users dengan JOIN manual
        $users = DB::select('
            SELECT u.*, r.nama_role
            FROM user u
            LEFT JOIN role r ON u.idrole = r.idrole
            ORDER BY u.username
        ');

        // Get roles with user count
        $roles = DB::select(
            'SELECT r.idrole, r.nama_role, COUNT(u.iduser) as total_users
             FROM role r
             LEFT JOIN user u ON r.idrole = u.idrole
             GROUP BY r.idrole, r.nama_role
             ORDER BY r.nama_role'
        );

        // Consolidated statistics
        $stats = DB::selectOne('
            SELECT
                (SELECT COUNT(*) FROM user) as total_user,
                (SELECT COUNT(*) FROM role) as total_role
        ');

        $userPerRole = DB::select('
            SELECT r.nama_role, COUNT(u.iduser) as total
            FROM role r
            LEFT JOIN user u ON r.idrole = u.idrole
            GROUP BY r.idrole, r.nama_role
            ORDER BY r.nama_role
        ');

        return view('user.index', [
            'users' => $users,
            'roles' => $roles,
            'tab' => $tab,
            'totalUser' => $stats->total_user,
            'totalRole' => $stats->total_role,
            'userPerRole' => $userPerRole
        ]);
    }

    // Menampilkan form untuk membuat user baru
    public function create()
    {
        $roles = DB::select('SELECT idrole, nama_role FROM role ORDER BY nama_role ASC');
        return view('user.create', compact('roles'));
    }

    // Menyimpan user baru ke database
    public function store(Request $request)
    {
        $validated = $request->validate([
            'username' => 'required|string|max:45|unique:user,username',
            'password' => 'required|string|min:6',
            'idrole' => 'required|integer|exists:role,idrole'
        ], [
            'username.required' => 'Username wajib diisi',
            'username.unique' => 'Username sudah digunakan',
            'password.required' => 'Password wajib diisi',
            'password.min' => 'Password minimal 6 karakter',
            'idrole.required' => 'Role wajib dipilih',
            'idrole.exists' => 'Role tidak valid'
        ]);

        try {
            DB::transaction(function () use ($validated) {
                DB::insert('INSERT INTO user (username, password, idrole) VALUES (?, ?, ?)', [
                    $validated['username'],
                    Hash::make($validated['password']),
                    $validated['idrole']
                ]);
            });

            return redirect()->route('user.index')->with('success', 'User berhasil ditambahkan!');
        } catch (\Exception $e) {
            return redirect()->back()
                ->withInput()
                ->with('error', 'Gagal menambahkan user: ' . $e->getMessage());
        }
    }

    public function edit(string $id)
    {
        $rows = DB::select('
            SELECT u.*, r.nama_role
            FROM user u
            LEFT JOIN role r ON u.idrole = r.idrole
            WHERE u.iduser = ?
            LIMIT 1
        ', [$id]);
        if (empty($rows)) {
            return redirect()->route('user.index')->with('error', 'User tidak ditemukan!');
        }

        $roles = DB::select('SELECT idrole, nama_role FROM role ORDER BY nama_role');
        return view('user.edit', ['user' => $rows[0], 'roles' => $roles]);
    }

    public function update(Request $request, string $id)
    {
        $validated = $request->validate([
            'username' => 'required|string|max:45|unique:user,username,' . $id . ',iduser',
            'password' => 'nullable|string|min:6',
            'idrole' => 'required|integer|exists:role,idrole'
        ], [
            'username.required' => 'Username wajib diisi',
            'username.unique' => 'Username sudah digunakan',
            'password.min' => 'Password minimal 6 karakter',
            'idrole.required' => 'Role wajib dipilih',
            'idrole.exists' => 'Role tidak valid'
        ]);

        try {
            $exists = DB::select('SELECT iduser FROM user WHERE iduser = ? LIMIT 1', [$id]);
            if (empty($exists)) {
                abort(404);
            }

            if (!empty($validated['password'])) {
                $affected = DB::update('UPDATE user SET username = ?, password = ?, idrole = ? WHERE iduser = ?', [
                    $validated['username'],
                    Hash::make($validated['password']),
                    $validated['idrole'],
                    $id
                ]);
            } else {
                $affected = DB::update('UPDATE user SET username = ?, idrole = ? WHERE iduser = ?', [
                    $validated['username'],
                    $validated['idrole'],
                    $id
                ]);
            }

            if ($affected > 0) {
                return redirect()->route('user.index')->with('success', 'User berhasil diperbarui!');
            } else {
                return redirect()->back()->with('error', 'Tidak ada perubahan atau user tidak ditemukan!');
            }
        } catch (\Exception $e) {
            return redirect()->back()
                ->withInput()
                ->with('error', 'Gagal memperbarui user: ' . $e->getMessage());
        }
    }

    // Fungsi untuk menon-aktifkan user (soft-delete)
    public function deactivate(string $id)
    {
        try {
            $exists = DB::select('SELECT iduser FROM user WHERE iduser = ? LIMIT 1', [$id]);
            if (empty($exists)) {
                return redirect()->route('user.index')->with('error', 'User tidak ditemukan!');
            }

            // Set status menjadi 'N' (non aktif)
            DB::update('UPDATE user SET status = ? WHERE iduser = ?', ['N', $id]);

            return redirect()->route('user.index')->with('success', 'User berhasil dinon-aktifkan.');
        } catch (\Exception $e) {
            return redirect()->route('user.index')->with('error', 'Gagal menon-aktifkan user: ' . $e->getMessage());
        }
    }

    // Fungsi untuk mengaktifkan kembali user yang dinon-aktifkan
    public function activate(string $id)
    {
        try {
            $exists = DB::select('SELECT iduser FROM user WHERE iduser = ? LIMIT 1', [$id]);
            if (empty($exists)) {
                return redirect()->route('user.index')->with('error', 'User tidak ditemukan!');
            }

            // Set status menjadi 'Y' (aktif)
            DB::update('UPDATE user SET status = ? WHERE iduser = ?', ['Y', $id]);

            return redirect()->route('user.index')->with('success', 'User berhasil diaktifkan.');
        } catch (\Exception $e) {
            return redirect()->route('user.index')->with('error', 'Gagal mengaktifkan user: ' . $e->getMessage());
        }
    }

    // ==================== ROLE MANAGEMENT ====================

    public function roleIndex()
    {
        return redirect()->route('user.index', ['tab' => 'roles']);
    }

    public function roleCreate()
    {
        return view('role.create');
    }

    public function roleStore(Request $request)
    {
        $validated = $request->validate([
            'nama_role' => 'required|string|max:45|unique:role,nama_role'
        ], [
            'nama_role.required' => 'Nama role wajib diisi',
            'nama_role.unique' => 'Nama role sudah ada dalam database',
            'nama_role.max' => 'Nama role maksimal 45 karakter'
        ]);

        try {
            DB::transaction(function () use ($validated) {
                DB::insert('INSERT INTO role (nama_role) VALUES (?)', [
                    $validated['nama_role']
                ]);
            });

            return redirect()->route('user.index', ['tab' => 'roles'])->with('success', 'Role berhasil ditambahkan!');
        } catch (\Exception $e) {
            return redirect()->back()
                ->withInput()
                ->with('error', 'Gagal menambahkan role: ' . $e->getMessage());
        }
    }

    public function roleEdit($id)
    {
        $rows = DB::select('SELECT idrole, nama_role FROM role WHERE idrole = ? LIMIT 1', [$id]);
        if (empty($rows)) {
            return redirect()->route('user.index', ['tab' => 'roles'])->with('error', 'Role tidak ditemukan!');
        }

        return view('role.edit', ['role' => $rows[0]]);
    }

    public function roleUpdate(Request $request, $id)
    {
        $validated = $request->validate([
            'nama_role' => 'required|string|max:45|unique:role,nama_role,' . $id . ',idrole'
        ], [
            'nama_role.required' => 'Nama role wajib diisi',
            'nama_role.unique' => 'Nama role sudah ada dalam database',
            'nama_role.max' => 'Nama role maksimal 45 karakter'
        ]);

        try {
            $exists = DB::select('SELECT idrole FROM role WHERE idrole = ? LIMIT 1', [$id]);
            if (empty($exists)) {
                abort(404);
            }

            DB::update('UPDATE role SET nama_role = ? WHERE idrole = ?', [
                $validated['nama_role'],
                $id
            ]);

            return redirect()->route('user.index', ['tab' => 'roles'])->with('success', 'Role berhasil diperbarui!');
        } catch (\Exception $e) {
            return redirect()->back()
                ->withInput()
                ->with('error', 'Gagal memperbarui role: ' . $e->getMessage());
        }
    }

    public function roleDestroy($id)
    {
        try {
            // Check if role is used by any user
            $usedByUser = DB::select('SELECT COUNT(*) as cnt FROM user WHERE idrole = ?', [$id]);
            if ($usedByUser[0]->cnt > 0) {
                return redirect()->route('user.index', ['tab' => 'roles'])->with('error', 'Role tidak dapat dihapus karena masih digunakan oleh user!');
            }

            $exists = DB::select('SELECT idrole FROM role WHERE idrole = ? LIMIT 1', [$id]);
            if (empty($exists)) {
                return redirect()->route('user.index', ['tab' => 'roles'])->with('error', 'Role tidak ditemukan!');
            }

            DB::delete('DELETE FROM role WHERE idrole = ?', [$id]);

            return redirect()->route('user.index', ['tab' => 'roles'])->with('success', 'Role berhasil dihapus!');
        } catch (\Exception $e) {
            return redirect()->route('user.index', ['tab' => 'roles'])->with('error', 'Gagal menghapus role: ' . $e->getMessage());
        }
    }
}
