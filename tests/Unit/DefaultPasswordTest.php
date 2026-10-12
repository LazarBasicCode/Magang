<?php

namespace Tests\Unit;

use App\Support\DefaultPassword;
use Tests\TestCase;

class DefaultPasswordTest extends TestCase
{
    public function test_nama_depan_huruf_kecil_digabung_angkatan(): void
    {
        $this->assertSame('budi2022', DefaultPassword::make('Budi Santoso', 2022));
    }

    public function test_gelar_di_depan_nama_dilewati(): void
    {
        $this->assertSame('ani2020', DefaultPassword::make('Dr. Ani Lestari', 2020));
        $this->assertSame('budi2020', DefaultPassword::make('Prof. Ir. H. Budi', 2020));
    }

    public function test_tanpa_angkatan_memakai_tahun_terbaru(): void
    {
        $this->assertSame('sari' . now()->year, DefaultPassword::make('Sari Dewi'));
    }

    public function test_hasil_minimal_enam_karakter(): void
    {
        $this->assertSame('a20000', DefaultPassword::make('A', 2000));
        $this->assertGreaterThanOrEqual(6, strlen(DefaultPassword::make('Al', 2000)));
    }
}
