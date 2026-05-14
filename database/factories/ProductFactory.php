<?php

namespace Database\Factories;

use App\Models\Product;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Product>
 */
class ProductFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        // Data hardcode diambil dari CSV
        $products = [
            [
                'name' => 'Keyboard Medis Silikon',
                'description' => 'Keyboard Medis Silikon Nirkabel dengan Bantalan Belakang Sesuai dengan Ergonomi',
                'price' => 100.00,
                'stock' => 100,
                'image' => 'https://image.made-in-china.com/201f0j00HdbioPTChpqr/Wireless-Silicone-Medical-Keyboard-with-Back-Pad-According-with-Ergonomics.webp',
            ],
            [
                'name' => 'Keyboard Metalik Tanpa Suara',
                'description' => 'Tombol Keyboard Kunci Persegi, Tekstur Metalik, 2.4G Nirkabel, Bertenaga Baterai, Tanpa Suara untuk Kantor Publik',
                'price' => 7.25,
                'stock' => 100,
                'image' => 'https://image.made-in-china.com/201f0j00GmDCMKlPEIoL/Square-Keycap-Scissor-Keyboard-Metallic-Texture-2-4G-Wireless-Battery-Powered-Noiseless-for-Public-Office.webp',
            ],
            [
                'name' => 'Keyboard Mekanik Gasket Mount',
                'description' => 'Keyboard Mekanik Nirkabel USB 3.0 Dapat Dipindah Gasket Mount Tata Letak Khusus Gaming Penggunaan Per Kunci Kombinasi Esports',
                'price' => 38.00,
                'stock' => 100,
                'image' => 'https://image.made-in-china.com/201f0j00QAPkSnbGhzpr/New-Wireless-Mechanical-Keyboard-USB-3-0-Hot-Swappable-Gasket-Mount-Layout-Gaming-Specific-Use-Per-Key-Esports-Combo.webp',
            ],
            [
                'name' => 'Kombinasi Keyboard & Mouse Ergonomis',
                'description' => 'Kombinasi Keyboard Mekanik Nirkabel dan Mouse Ukuran Penuh Desain Ergonomis untuk Penggunaan Tablet Laptop Permainan Kantor Penggunaan Permainan',
                'price' => 10.31,
                'stock' => 100,
                'image' => 'https://image.made-in-china.com/201f0j00HwpoBAbhADuU/Wireless-Mechanical-Keyboard-Mouse-Combo-Full-Size-Ergonomic-Design-Tablet-Laptop-Use-Gaming-Office-Gaming-Usage.webp',
            ],
            [
                'name' => 'Keyboard Ajaib (Touch ID)',
                'description' => 'Keyboard Ajaib dengan Touch ID dan Numpad Nirkabel untuk Komputer Mac',
                'price' => 200.00,
                'stock' => 100,
                'image' => 'https://image.made-in-china.com/201f0j00LInGvclEhAbq/Magic-Keyboard-with-Touch-ID-and-Numeric-Keypad-Wireless-for-Mac-Computer.webp',
            ],
        ];


        return ($products);
    }
}