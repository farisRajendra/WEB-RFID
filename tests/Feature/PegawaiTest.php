<?php

// taseh error wkwk

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;
use App\Models\Pegawai;
use App\Models\Rfid;

class PegawaiTest extends TestCase
{
    use RefreshDatabase;

    // Sebelum setiap test, kita buat user dan login
    public function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create([
            'password' => bcrypt('password123'), // Sesuaikan dengan password yang digunakan
        ]);
        $this->actingAs($this->user); // Login sebagai user
    }

    // Test untuk melihat daftar pegawai
    public function test_pegawai_index_page_can_be_accessed()
    {
        $response = $this->get('/pegawai');
        $response->assertStatus(200);
        $response->assertSee('Daftar Pegawai'); // Sesuaikan dengan tampilan yang ada di halaman pegawai
    }

    // Test untuk menambah pegawai
    public function test_user_can_store_pegawai()
    {
        $response = $this->post('/pegawai', [
            'nama' => 'John Doe',
            'jabatan' => 'Manager',
            'tanggal_lahir' => '1980-01-01',
            'rfid' => '1234567890',
        ]);

        $response->assertStatus(200);
        $response->assertJson([
            'message' => 'Data pegawai berhasil disimpan',
        ]);

        $this->assertDatabaseHas('pegawai', [
            'nama' => 'John Doe',
            'jabatan' => 'Manager',
        ]);

        $this->assertDatabaseHas('rfids', [
            'rfid' => '1234567890',
        ]);
    }

    // Test untuk melihat data pegawai berdasarkan ID
    public function test_show_pegawai()
    {
        $pegawai = Pegawai::factory()->create();

        $response = $this->get("/pegawai/{$pegawai->id}");
        $response->assertStatus(200);
        $response->assertJson([
            'nama' => $pegawai->nama,
            'jabatan' => $pegawai->jabatan,
        ]);
    }

    // Test untuk mengupdate data pegawai
    public function test_user_can_update_pegawai()
    {
        $pegawai = Pegawai::factory()->create();

        $response = $this->put("/pegawai/{$pegawai->id}", [
            'nama' => 'Jane Doe',
            'jabatan' => 'Supervisor',
            'tanggal_lahir' => '1990-02-02',
            'rfid' => '9876543210',
        ]);

        $response->assertStatus(200);
        $response->assertJson([
            'message' => 'Data pegawai berhasil diperbarui',
        ]);

        $this->assertDatabaseHas('pegawai', [
            'nama' => 'Jane Doe',
            'jabatan' => 'Supervisor',
        ]);

        $this->assertDatabaseHas('rfids', [
            'rfid' => '9876543210',
        ]);
    }

    // Test untuk menghapus data pegawai
    public function test_user_can_delete_pegawai()
    {
        $pegawai = Pegawai::factory()->create();

        $response = $this->delete("/pegawai/{$pegawai->id}");

        $response->assertStatus(200);
        $response->assertJson([
            'message' => 'Data pegawai berhasil dihapus',
        ]);

        $this->assertDatabaseMissing('pegawai', [
            'id' => $pegawai->id,
        ]);
    }

    // Test validasi saat menambah pegawai tanpa mengisi field yang wajib
    public function test_validation_error_when_storing_pegawai()
    {
        $response = $this->post('/pegawai', [
            'nama' => '',
            'jabatan' => '',
            'tanggal_lahir' => '',
            'rfid' => '',
        ]);

        $response->assertSessionHasErrors(['nama', 'jabatan', 'tanggal_lahir']);
    }
}
