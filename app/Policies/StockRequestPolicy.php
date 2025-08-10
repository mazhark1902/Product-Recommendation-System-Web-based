<?php

namespace App\Policies;

use App\Models\User;
use App\Models\StockRequest;
use Illuminate\Auth\Access\HandlesAuthorization;

class StockRequestPolicy
{
    use HandlesAuthorization;

    public function viewAny(User $user): bool
    {
        return $user->can('view_any_stock::request');
    }

    public function view(User $user, StockRequest $stockRequest): bool
    {
        return $user->can('view_stock::request');
    }

    public function create(User $user): bool
    {
        return $user->can('create_stock::request');
    }

    public function update(User $user, StockRequest $stockRequest): bool
    {
        // Pengguna bisa update jika punya izin DAN status request masih PENDING
        return $user->can('update_stock::request') && $stockRequest->status === 'PENDING';
    }

    public function delete(User $user, StockRequest $stockRequest): bool
    {
        return $user->can('delete_stock::request');
    }

    /**
     * Izin Kustom untuk Approval.
     * Fungsi inilah yang akan dipanggil oleh tombol approve di tabel.
     *
     * @param User $user Pengguna yang sedang login.
     * @param StockRequest $stockRequest Record yang sedang dilihat.
     * @return bool
     */
    public function approve(User $user, StockRequest $stockRequest): bool
    {
        // Tombol akan muncul JIKA:
        // 1. Pengguna punya izin 'approve_stock::request'.
        // 2. Status record-nya adalah 'PENDING'.
        return $user->can('approve_stock::request') && $stockRequest->status === 'PENDING';
    }
}