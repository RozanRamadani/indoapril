<?php

namespace App\Http\Controllers\Role;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class RoleController extends Controller
{
    public function index(Request $request)
    {
        // Use master view to get roles with user counts
        $roles = DB::select(
            'SELECT r.idrole, r.nama_role, COUNT(u.iduser) as total_users 
             FROM role r 
             LEFT JOIN user u ON r.idrole = u.idrole 
             GROUP BY r.idrole, r.nama_role 
             ORDER BY r.nama_role ASC'
        );
        
        $totalRole = DB::selectOne('SELECT COUNT(*) as cnt FROM role')->cnt;
        $roleWithUsers = count(array_filter($roles, fn($r) => ($r->total_users ?? 0) > 0));
        $roleWithoutUsers = max(0, $totalRole - $roleWithUsers);
        
        return view('role.index', compact('roles', 'totalRole', 'roleWithUsers', 'roleWithoutUsers'));
    }

    public function create()
    {
        return view('role.create');
    }

    public function store(Request $request)
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

            return redirect()->route('role.index')->with('success', 'Role berhasil ditambahkan!');
        } catch (\Exception $e) {
            return redirect()->back()
                ->withInput()
                ->with('error', 'Gagal menambahkan role: ' . $e->getMessage());
        }
    }

    public function edit(string $id)
    {
        $rows = DB::select('SELECT idrole, nama_role FROM role WHERE idrole = ? LIMIT 1', [$id]);
        if (empty($rows)) {
            return redirect()->route('role.index')->with('error', 'Role tidak ditemukan!');
        }

        return view('role.edit', ['role' => $rows[0]]);
    }

    public function update(Request $request, string $id)
    {
        $validated = $request->validate([
            'nama_role' => 'required|string|max:45|unique:role,nama_role,' . $id . ',idrole'
        ], [
            'nama_role.required' => 'Nama role wajib diisi',
            'nama_role.unique' => 'Nama role sudah ada dalam database',
            'nama_role.max' => 'Nama role maksimal 45 karakter'
        ]);

        try {
            $affected = DB::update('UPDATE role SET nama_role = ? WHERE idrole = ?', [
                $validated['nama_role'],
                $id
            ]);

            if ($affected > 0) {
                return redirect()->route('role.index')->with('success', 'Role berhasil diperbarui!');
            } else {
                return redirect()->back()->with('error', 'Tidak ada perubahan atau role tidak ditemukan!');
            }
        } catch (\Exception $e) {
            return redirect()->back()
                ->withInput()
                ->with('error', 'Gagal memperbarui role: ' . $e->getMessage());
        }
    }

    public function destroy(string $id)
    {
        try {
            // Check if role is used by any user
            $usedByUser = DB::select('SELECT COUNT(*) as cnt FROM user WHERE idrole = ?', [$id]);
            if ($usedByUser[0]->cnt > 0) {
                return redirect()->route('role.index')
                    ->with('error', 'Role tidak dapat dihapus karena masih digunakan oleh user!');
            }

            $exists = DB::select('SELECT idrole FROM role WHERE idrole = ? LIMIT 1', [$id]);
            if (empty($exists)) {
                return redirect()->route('role.index')->with('error', 'Role tidak ditemukan!');
            }

            DB::delete('DELETE FROM role WHERE idrole = ?', [$id]);
            return redirect()->route('role.index')->with('success', 'Role berhasil dihapus!');
        } catch (\Exception $e) {
            return redirect()->route('role.index')->with('error', 'Gagal menghapus role: ' . $e->getMessage());
        }
    }
}