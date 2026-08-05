<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\PaymentGateway;
use App\Models\Product;
use App\Models\StoreSetting;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    public function run(): void
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        $permissionNames = [
            'access admin',
            'manage products',
            'manage orders',
            'manage store settings',
            'view reports',
        ];

        foreach ($permissionNames as $permissionName) {
            Permission::findOrCreate($permissionName, 'web');
        }

        app(PermissionRegistrar::class)->forgetCachedPermissions();

        $ownerRole = Role::findOrCreate('owner', 'web');
        $ownerRole->syncPermissions($permissionNames);

        $staffRole = Role::findOrCreate('staff', 'web');
        $staffRole->syncPermissions([
            'access admin',
            'manage products',
            'manage orders',
            'view reports',
        ]);

        $owner = User::query()->firstOrCreate(
            ['email' => 'owner@example.com'],
            ['name' => 'Pemilik Toko', 'email_verified_at' => now(), 'password' => Hash::make('password')],
        );
        $owner->syncRoles($ownerRole);

        $staff = User::query()->firstOrCreate(
            ['email' => 'staff@example.com'],
            ['name' => 'Staff Toko', 'email_verified_at' => now(), 'password' => Hash::make('password')],
        );
        $staff->syncRoles($staffRole);

        $home = Category::query()->updateOrCreate(
            ['slug' => 'rumah'],
            ['name' => 'Rumah', 'is_active' => true],
        );
        $lifestyle = Category::query()->updateOrCreate(
            ['slug' => 'gaya-hidup'],
            ['name' => 'Gaya Hidup', 'is_active' => true],
        );
        $aroma = Category::query()->updateOrCreate(
            ['slug' => 'aroma'],
            ['name' => 'Aroma', 'is_active' => true],
        );

        Product::query()->updateOrCreate(
            ['slug' => 'teko-tanah-sore'],
            [
                'category_id' => $home->id,
                'name' => 'Teko Tanah Sore',
                'description' => 'Teko tanah liat yang dibentuk tangan untuk teman minum teh yang lebih tenang.',
                'price' => 189000,
                'stock' => 18,
                'is_featured' => true,
                'is_active' => true,
            ],
        );
        Product::query()->updateOrCreate(
            ['slug' => 'tas-kanvas-rimba'],
            [
                'category_id' => $lifestyle->id,
                'name' => 'Tas Kanvas Rimba',
                'description' => 'Tas kanvas tebal dengan ruang lega untuk aktivitas sehari-hari.',
                'price' => 245000,
                'stock' => 8,
                'is_featured' => true,
                'is_active' => true,
            ],
        );
        Product::query()->updateOrCreate(
            ['slug' => 'lilin-aroma-hujan'],
            [
                'category_id' => $aroma->id,
                'name' => 'Lilin Aroma Hujan',
                'description' => 'Perpaduan aroma kayu dan tanah basah untuk sore yang lebih nyaman.',
                'price' => 129000,
                'stock' => 24,
                'is_featured' => true,
                'is_active' => true,
            ],
        );

        PaymentGateway::query()->updateOrCreate(
            ['code' => 'manual-transfer'],
            [
                'name' => 'Transfer Bank Manual',
                'instructions' => 'Transfer ke Bank Demo 1234567890 a.n. Toko Senja, lalu konfirmasi melalui WhatsApp.',
                'config' => ['account_name' => 'Toko Senja', 'account_number' => '1234567890'],
                'is_active' => true,
            ],
        );

        StoreSetting::query()->firstOrCreate(
            [],
            [
                'store_name' => 'Toko Senja',
                'contact_email' => 'halo@tokosenja.test',
                'phone' => '0812-0000-0000',
                'address' => 'Jl. Senja No. 10, Indonesia',
                'tagline' => 'Produk lokal yang dibuat dengan cerita.',
            ],
        );

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }
}
