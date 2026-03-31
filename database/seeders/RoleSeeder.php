<?php

namespace Database\Seeders;

use App\Models\Role;
use Illuminate\Database\Seeder;

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $roles = [
            ['name' => 'Social User',    'slug' => 'social_user',    'description' => 'Utilisateur réseau social'],
            ['name' => 'Client',         'slug' => 'client',         'description' => 'Artisan abonné BatiAssist'],
            ['name' => 'Assistant',      'slug' => 'assistant',      'description' => 'Assistant humain BatiAssist'],
            ['name' => 'Admin',          'slug' => 'admin',          'description' => 'Administration plateforme'],
            ['name' => 'Particulier',    'slug' => 'particulier',    'description' => 'Particulier cherchant des services'],
            ['name' => 'Professionnel',  'slug' => 'professionnel',  'description' => 'Artisan/pro du bâtiment'],
        ];

        foreach ($roles as $role) {
            Role::updateOrCreate(['slug' => $role['slug']], $role);
        }
    }
}
