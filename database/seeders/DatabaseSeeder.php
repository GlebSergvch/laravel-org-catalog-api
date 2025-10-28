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
        $admin = $this->seedAdmin();

        $buildingIds = $this->seedBuildings($admin->id);

        $activityMap = $this->seedActivities($admin->id);

        $this->seedActivityClosure($admin->id);

        $this->seedOrganizations($admin->id, $buildingIds, $activityMap);
    }

    private function seedAdmin(): User
    {
        return User::firstOrCreate(
            ['email' => 'admin@example.com'],
            [
                'name'              => 'Admin User',
                'email_verified_at' => now(),
                'password'          => Hash::make('password'),
            ]
        );
    }

    private function seedBuildings(int $adminId): array
    {
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

        $ids = [];
        foreach ($buildings as [$addr, $city, $lat, $lng]) {
            $building = Building::updateOrCreate(
                ['address' => $addr, 'city' => $city],
                [
                    'location'   => DB::raw("ST_GeomFromText('POINT($lng $lat)', 4326)"),
                    'created_by' => $adminId,
                    'updated_by' => $adminId,
                ]
            );
            $ids[] = $building->id;
        }

        return $ids;
    }

    private function seedActivities(int $adminId): array
    {
        // [name, parentName]
        $activities = [
            ['Еда', null], ['Автомобили', null], ['Медицина', null], ['Строительство', null], ['Образование', null],
            ['Мясная продукция', 'Еда'], ['Молочная продукция', 'Еда'], ['Грузовые', 'Автомобили'], ['Легковые', 'Автомобили'],
            ['Медицинское оборудование', 'Медицина'], ['Фармацевтика', 'Медицина'], ['Жилые дома', 'Строительство'], ['Коммерческая недвижимость', 'Строительство'],
            ['Колбасы', 'Мясная продукция'], ['Сыры', 'Молочная продукция'], ['Запчасти', 'Легковые'], ['Аксессуары', 'Легковые'],
            ['Диагностика', 'Медицинское оборудование'], ['Лекарства', 'Фармацевтика'], ['Кирпич', 'Жилые дома'],
        ];

        // Сначала создаём корневые, затем дочерние - чтобы родители уже были в карте
        $activityMap = [];

        foreach ($activities as [$name, $parentName]) {
            $parentId = $parentName ? ($activityMap[$parentName] ?? null) : null;

            // Если родитель указан, но его ещё нет — откладываем создание и продолжим, позже второй проход возьмёт его.
            // Для простоты — делаем find в базе для родителя, если он уже создан (на случай пересортировки)
            if ($parentName && !$parentId) {
                $maybeParent = Activity::where('name', $parentName)->first();
                $parentId = $maybeParent ? $maybeParent->id : null;
            }

            $level = $parentId ? (Activity::find($parentId)->level + 1) : 1;

            // Не создаём уровней глубже 3
            if ($parentId && Activity::find($parentId)->level >= 3) {
                continue;
            }

            $activity = Activity::updateOrCreate(
                ['name' => $name],
                [
                    'parent_id'  => $parentId,
                    'level'      => $level,
                    'created_by' => $adminId,
                    'updated_by' => $adminId,
                ]
            );

            $activityMap[$name] = $activity->id;
        }

        return $activityMap; // name => id
    }

    private function seedActivityClosure(int $adminId): void
    {
        // Для каждой activity добавляем self (depth=0) и все предков (ancestor -> descendant)
        $now = now();
        foreach (Activity::all() as $activity) {
            // self
            DB::table('activity_closure')->updateOrInsert(
                ['ancestor_id' => $activity->id, 'descendant_id' => $activity->id],
                ['depth' => 0, 'created_by' => $adminId, 'updated_by' => $adminId, 'created_at' => $now, 'updated_at' => $now]
            );

            // поднимаемся по цепочке parent_id
            $depth = 1;
            $parentId = $activity->parent_id;
            while ($parentId) {
                $ancestor = Activity::find($parentId);
                if (!$ancestor) break;

                DB::table('activity_closure')->updateOrInsert(
                    ['ancestor_id' => $ancestor->id, 'descendant_id' => $activity->id],
                    ['depth' => $depth, 'created_by' => $adminId, 'updated_by' => $adminId, 'created_at' => $now, 'updated_at' => $now]
                );

                $parentId = $ancestor->parent_id;
                $depth++;
            }
        }
    }

    private function seedOrganizations(int $adminId, array $buildingIds, array $activityMap): void
    {
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

        $activityIds = array_values($activityMap);

        foreach ($orgData as [$name, $inn, $desc]) {
            $org = Organization::updateOrCreate(
                ['inn' => $inn],
                [
                    'name'        => $name,
                    'description' => $desc,
                    'created_by'  => $adminId,
                    'updated_by'  => $adminId,
                ]
            );

            // buildings: attach 1..3 первых из списка случайным образом
            $buildingCount = rand(1, min(3, count($buildingIds)));
            $chosenBuildingIds = collect($buildingIds)->shuffle()->take($buildingCount)->values()->all();

            $attachBuildings = [];
            foreach ($chosenBuildingIds as $i => $bid) {
                $attachBuildings[(int)$bid] = [
                    'is_head_office' => $i === 0,
                    'opened_at'      => now()->subMonths(rand(1, 36)),
                    'created_by'     => $adminId,
                    'updated_by'     => $adminId,
                ];
            }
            if (!empty($attachBuildings)) {
                $org->buildings()->syncWithoutDetaching($attachBuildings);
            }

            // activities: 1..4 случайных
            if (!empty($activityIds)) {
                $actCount = rand(1, min(4, count($activityIds)));
                $selectedActivityIds = collect($activityIds)->shuffle()->take($actCount)->values()->all();

                $attachActivities = [];
                foreach ($selectedActivityIds as $actId) {
                    $attachActivities[(int)$actId] = [
                        'created_by' => $adminId,
                        'updated_by' => $adminId,
                    ];
                }
                if (!empty($attachActivities)) {
                    $org->activities()->syncWithoutDetaching($attachActivities);
                }
            }

            // phones
            $phoneCount = rand(1, 3);
            $usedPhones = [];
            for ($i = 0; $i < $phoneCount; $i++) {
                // выбираем уникальный телефон из шаблонов
                $phone = $phoneTemplates[array_rand($phoneTemplates)];
                while (in_array($phone, $usedPhones, true)) {
                    $phone = $phoneTemplates[array_rand($phoneTemplates)];
                }
                $usedPhones[] = $phone;

                OrganizationPhone::updateOrCreate(
                    ['organization_id' => $org->id, 'phone' => $phone],
                    [
                        'is_main'    => $i === 0,
                        'type'       => $i === 0 ? 'офис' : ['склад', 'техподдержка', 'факс'][array_rand(['склад', 'техподдержка', 'факс'])],
                        'created_by' => $adminId,
                        'updated_by' => $adminId,
                    ]
                );
            }
        }
    }
}
