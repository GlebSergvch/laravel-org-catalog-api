<?php

namespace Database\Seeders;

use App\Models\Activity;
use App\Models\Building;
use App\Models\Organization;
use App\Models\OrganizationPhone;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Админ
        $admin = User::create([
            'name'              => 'Admin User',
            'email'             => 'admin@example.com',
            'email_verified_at' => now(),
            'password'          => Hash::make('password'),
        ]);

        // 2. 10 зданий
        $buildings = [
            ['ул. Тверская, 7',          'Москва',          55.758, 37.612],
            ['пр. Невский, 85',          'Санкт-Петербург', 59.935, 30.327],
            ['ул. Кирова, 12',           'Екатеринбург',    56.838, 60.597],
            ['ул. Ленина, 45',           'Новосибирск',     55.008, 82.935],
            ['ул. Пушкина, 3',           'Казань',          55.788, 49.122],
            ['пр. Победы, 21',           'Челябинск',       55.164, 61.436],
            ['ул. Мира, 10',             'Омск',            54.989, 73.368],
            ['ул. Садовая, 5',           'Самара',          53.195, 50.100],
            ['ул. Центральная, 1',       'Красноярск',      56.015, 92.893],
            ['ул. Гагарина, 15',         'Воронеж',         51.660, 39.200],
        ];

        $buildingIds = [];
        foreach ($buildings as [$addr, $city, $lat, $lng]) {
            $buildingIds[] = Building::create([
                'address'     => $addr,
                'city'        => $city,
                'location'    => DB::raw("ST_GeomFromText('POINT($lng $lat)', 4326)"),
                'created_by'  => $admin->id,
                'updated_by'  => $admin->id,
            ])->id;
        }

        // 3. 20 активностей
        $activities = [
            ['Еда', null], ['Автомобили', null], ['Медицина', null], ['Строительство', null], ['Образование', null],
            ['Мясная продукция', 'Еда'], ['Молочная продукция', 'Еда'], ['Грузовые', 'Автомобили'], ['Легковые', 'Автомобили'],
            ['Медицинское оборудование', 'Медицина'], ['Фармацевтика', 'Медицина'], ['Жилые дома', 'Строительство'], ['Коммерческая недвижимость', 'Строительство'],
            ['Колбасы', 'Мясная продукция'], ['Сыры', 'Молочная продукция'], ['Запчасти', 'Легковые'], ['Аксессуары', 'Легковые'],
            ['Диагностика', 'Медицинское оборудование'], ['Лекарства', 'Фармацевтика'], ['Кирпич', 'Жилые дома'],
        ];

        $activityMap = [];
        foreach ($activities as [$name, $parentName]) {
            $parentId = $parentName ? ($activityMap[$parentName] ?? null) : null;
            if ($parentId && Activity::find($parentId)->level >= 3) continue;
            $level = $parentId ? Activity::find($parentId)->level + 1 : 1;

            $activity = Activity::create([
                'name'        => $name,
                'parent_id'   => $parentId,
                'level'       => $level,
                'created_by'  => $admin->id,
                'updated_by'  => $admin->id,
            ]);
            $activityMap[$name] = $activity->id;
        }

        // 4. Closure Table
        foreach (Activity::all() as $activity) {
            DB::table('activity_closure')->updateOrInsert(
                ['ancestor_id' => $activity->id, 'descendant_id' => $activity->id],
                ['depth' => 0, 'created_by' => $admin->id, 'updated_by' => $admin->id, 'created_at' => now(), 'updated_at' => now()]
            );

            $descendants = $this->getDescendants($activity->id);
            foreach ($descendants as $desc) {
                DB::table('activity_closure')->updateOrInsert(
                    ['ancestor_id' => $activity->id, 'descendant_id' => $desc['id']],
                    ['depth' => $desc['level'] - $activity->level, 'created_by' => $admin->id, 'updated_by' => $admin->id, 'created_at' => now(), 'updated_at' => now()]
                );
            }
        }

        // 5. 15 организаций + телефоны
        $orgData = [
            ['ООО "Мясной Двор"',      '1234567891', 'Производство колбас'],
            ['ЗАО "Молочный Путь"',    '2345678901', 'Сыры и йогурты'],
            ['ИП Иванов',              '3456789012', 'Розничная торговля'],
            ['ООО "АвтоГруз"',         '4567890123', 'Грузовые перевозки'],
            ['ПАО "Легковоз"',         '5678901234', 'Продажа легковых авто'],
            ['ООО "МедТех"',           '6789012345', 'Медицинское оборудование'],
            ['ЗАО "ФармаПлюс"',        '7890123456', 'Производство лекарств'],
            ['ООО "СтройДом"',         '8901234567', 'Жилое строительство'],
            ['ИП Петров',              '9012345678', 'Ремонт квартир'],
            ['ООО "Образование 21"',   '1122334455', 'Курсы программирования'],
            ['ЗАО "АвтоДеталь"',       '2233445566', 'Запчасти для легковых'],
            ['ООО "Клиника Здоровье"', '3344556677', 'Частная клиника'],
            ['ИП Сидоров',             '4455667788', 'Магазин аксессуаров'],
            ['ООО "КирпичМастер"',     '5566778899', 'Производство кирпича'],
            ['ПАО "ТрансЛогистик"',    '6677889900', 'Логистика и склады'],
        ];

        $phoneTemplates = [
            '+7(901)111-22-33', '+7(902)222-33-44', '+7(903)333-44-55', '+7(904)444-55-66',
            '+7(905)555-66-77', '+7(906)666-77-88', '+7(907)777-88-99', '+7(908)888-99-00',
        ];

        foreach ($orgData as $index => [$name, $inn, $desc]) {
            $org = Organization::create([
                'name'        => $name,
                'inn'         => $inn,
                'description' => $desc,
                'created_by'  => $admin->id,
                'updated_by'  => $admin->id,
            ]);

            // Здания
            $buildingCount = rand(1, 3);
            $attachBuildings = [];
            foreach (array_slice($buildingIds, 0, $buildingCount) as $i => $bid) {
                $attachBuildings[$bid] = [
                    'is_head_office' => $i === 0,
                    'opened_at'      => now()->subMonths(rand(1, 36)),
                    'created_by'     => $admin->id,
                    'updated_by'     => $admin->id,
                ];
            }
            $org->buildings()->attach($attachBuildings);

            // Активности
            $actCount = rand(1, 4);
            $selectedActNames = collect($activityMap)->random($actCount)->keys();
            $attachActivities = [];
            foreach ($selectedActNames as $actName) {
                $actId = $activityMap[$actName] ?? null;
                if ($actId) {
                    $attachActivities[$actId] = ['created_by' => $admin->id, 'updated_by' => $admin->id];
                }
            }
            $org->activities()->attach($attachActivities);

            // Телефоны: 1–3 на организацию
            $phoneCount = rand(1, 3);
            $usedPhones = [];
            for ($i = 0; $i < $phoneCount; $i++) {
                $phone = $phoneTemplates[array_rand($phoneTemplates)];
                while (in_array($phone, $usedPhones)) {
                    $phone = $phoneTemplates[array_rand($phoneTemplates)];
                }
                $usedPhones[] = $phone;

                OrganizationPhone::create([
                    'organization_id' => $org->id,
                    'phone'           => $phone,
                    'is_main'         => $i === 0,
                    'type'            => $i === 0 ? 'офис' : (['склад', 'техподдержка', 'факс'][array_rand(['склад', 'техподдержка', 'факс'])]),
                    'created_by'      => $admin->id,
                    'updated_by'      => $admin->id,
                ]);
            }
        }
    }

    private function getDescendants(int $id): array
    {
        $root = Activity::with(['children.children.children'])->find($id);
        if (!$root) return [];

        $descendants = collect();
        $collect = function ($node) use (&$collect, &$descendants) {
            foreach ($node->children as $child) {
                $descendants->push(['id' => $child->id, 'level' => $child->level]);
                $collect($child);
            }
        };
        $collect($root);
        return $descendants->toArray();
    }
}
