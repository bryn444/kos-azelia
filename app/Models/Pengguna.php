<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class Pengguna extends Authenticatable
{
    use Notifiable;

    protected $table = 'pengguna';

    protected $fillable = [
        'username',
        'password',
        'role',
        'nama_lengkap',
        'latar_tampilan',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Accessor untuk menghasilkan nilai CSS --latar-halaman
     */
    public function getCssLatarHalamanAttribute(): string
    {
        $val = $this->latar_tampilan;

        if (empty($val)) {
            // Default Preset: Biru Laut
            return 'radial-gradient(circle at 10% 20%, rgba(59, 95, 138, 0.9) 0%, rgba(31, 36, 48, 0.95) 90%), linear-gradient(135deg, #3B5F8A 0%, #1F2430 100%)';
        }

        if (str_starts_with($val, 'preset:')) {
            $key = str_replace('preset:', '', $val);
            return match ($key) {
                'teal' => 'radial-gradient(circle at 10% 20%, rgba(20, 110, 110, 0.9) 0%, rgba(15, 35, 45, 0.95) 90%), linear-gradient(135deg, #146E6E 0%, #0F232D 100%)',
                'senja' => 'radial-gradient(circle at 10% 20%, rgba(120, 60, 110, 0.9) 0%, rgba(35, 20, 45, 0.95) 90%), linear-gradient(135deg, #783C6E 0%, #23142D 100%)',
                'abu' => 'radial-gradient(circle at 10% 20%, rgba(70, 80, 95, 0.9) 0%, rgba(20, 25, 30, 0.95) 90%), linear-gradient(135deg, #46505F 0%, #14191E 100%)',
                'jingga' => 'radial-gradient(circle at 10% 20%, rgba(180, 100, 50, 0.9) 0%, rgba(45, 25, 15, 0.95) 90%), linear-gradient(135deg, #B46432 0%, #2D190F 100%)',
                default => 'radial-gradient(circle at 10% 20%, rgba(59, 95, 138, 0.9) 0%, rgba(31, 36, 48, 0.95) 90%), linear-gradient(135deg, #3B5F8A 0%, #1F2430 100%)',
            };
        }

        if (str_starts_with($val, 'unggah:')) {
            $path = str_replace('unggah:', '', $val);
            if (\Storage::disk('public')->exists($path)) {
                $url = asset('storage/' . $path);
                return 'url("' . $url . '")';
            }
        }

        // Fallback bawaan
        return 'radial-gradient(circle at 10% 20%, rgba(59, 95, 138, 0.9) 0%, rgba(31, 36, 48, 0.95) 90%), linear-gradient(135deg, #3B5F8A 0%, #1F2430 100%)';
    }
}