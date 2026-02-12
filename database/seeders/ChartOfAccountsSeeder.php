<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Models\User;
use Carbon\Carbon;

class ChartOfAccountsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run(): void
    {
        $user = User::first();
        if (!$user) {
            $this->command->error('No users found in the database. Please create a user before running this seeder.');
            return;
        }
        $now = Carbon::now();

        // Hierarchical structure of accounts with type attribute
        $accounts = [
            [
                'code' => '100',
                'name' => 'ACTIVOS',
                'type' => 'asset',
                'children' => [
                    ['code' => '100.01', 'name' => 'EFECTIVO'],
                    ['code' => '100.02', 'name' => 'BANCOS'],
                    ['code' => '100.03', 'name' => 'INVERSIONES'],
                    ['code' => '100.04', 'name' => 'CUENTAS DE AHORRO'],
                    ['code' => '100.05', 'name' => 'DEUDORES DIVERSOS'],
                ]
            ],
            [
                'code' => '110',
                'name' => 'ACTIVOS FIJOS',
                'type' => 'asset',
                'children' => [
                    ['code' => '110.01', 'name' => 'TERRENOS'],
                    ['code' => '110.02', 'name' => 'INMUEBLES CASA HABITACION / DEPTO'],
                    ['code' => '110.03', 'name' => 'EQUIPOS DE TRANSPORTE'],
                    ['code' => '110.04', 'name' => 'MOBILIARIO Y EQUIPO'],
                    ['code' => '110.05', 'name' => 'EQUIPOS DE COMPUTO'],
                    ['code' => '110.06', 'name' => 'OTRO GRUPO'],
                ]
            ],
            [
                'code' => '200',
                'name' => 'PASIVOS',
                'type' => 'liability',
                'children' => [
                    ['code' => '200.01', 'name' => 'TARJETAS DE CREDITO'],
                    ['code' => '200.02', 'name' => 'PRESTAMOS BANCARIOS'],
                    ['code' => '200.03', 'name' => 'CREDITOS AUTOMOTRICES'],
                    ['code' => '200.04', 'name' => 'CREDITOS HIPOTECARIOS'],
                    ['code' => '200.05', 'name' => 'ACREEDORES DIVERSOS'],
                ]
            ],
            [
                'code' => '300',
                'name' => 'PATRIMONIO',
                'type' => 'equity',
                'children' => [
                    ['code' => '300.01', 'name' => 'DEFICIT O REMANENTE DEL EJERCICIO'],
                    ['code' => '300.02', 'name' => 'DEFICIT O REMANENTE DE EJERCICIO ANTERIORES'],
                ]
            ],
            // Accounts without children
            ['code' => '400', 'name' => 'INGRESOS', 'type' => 'income', 'children' => []],
            ['code' => '500', 'name' => 'EGRESOS', 'type' => 'expense', 'children' => []],
            ['code' => '600', 'name' => 'GASTOS FINANCIEROS', 'type' => 'expense', 'children' => []],
            ['code' => '700', 'name' => 'PRODUCTOS FINANCIEROS', 'type' => 'income', 'children' => []],
            ['code' => '800', 'name' => 'OTROS PRODUCTOS', 'type' => 'income', 'children' => []],
            ['code' => '900', 'name' => 'OTROS GASTOS', 'type' => 'expense', 'children' => []],
        ];

        foreach ($accounts as $parentAccount) {
            // Insert the parent account and get its ID
            $parentId = DB::table('chart_of_accounts')->insertGetId([
                'user_id' => $user->id,
                'parent_id' => null,
                'code' => $parentAccount['code'],
                'name' => $parentAccount['name'],
                'type' => $parentAccount['type'], // **** ADDED TYPE ****
                'created_at' => $now,
                'updated_at' => $now,
            ]);

            $childAccountsToInsert = [];
            foreach ($parentAccount['children'] as $childAccount) {
                $childAccountsToInsert[] = [
                    'user_id' => $user->id,
                    'parent_id' => $parentId,
                    'code' => $childAccount['code'],
                    'name' => $childAccount['name'],
                    'type' => $parentAccount['type'], // **** ADDED TYPE (inherited from parent) ****
                    'created_at' => $now,
                    'updated_at' => 'now',
                ];
            }

            if (!empty($childAccountsToInsert)) {
                DB::table('chart_of_accounts')->insert($childAccountsToInsert);
            }
        }

        $this->command->info('Chart of accounts has been successfully seeded with types!');
    }
}
