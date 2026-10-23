<?php

namespace Database\Seeders;

use App\Models\ActivityLog;
use App\Models\Education;
use Illuminate\Database\Seeder;

/**
 * Sample education entries. Replace them in Admin → Education.
 * Safe to run on its own: php artisan db:seed --class=EducationSeeder
 */
class EducationSeeder extends Seeder
{
    public function run(): void
    {
        if (Education::exists()) {
            return;
        }
        $logging = ActivityLog::$enabled;
        ActivityLog::$enabled = false;

        $entries = [
            [
                'institution' => 'National University of Laos',
                'degree' => ['lo' => 'ປະລິນຍາຕີ ວິທະຍາສາດຄອມພິວເຕີ', 'en' => 'Bachelor of Computer Science'],
                'field' => ['lo' => 'ວິສະວະກຳຊອບແວ', 'en' => 'Software Engineering'],
                'start_date' => '2017-09-01', 'end_date' => '2021-07-31',
                'description' => [
                    'lo' => 'ສຶກສາການພັດທະນາຊອບແວ, ຖານຂໍ້ມູນ ແລະ ການອອກແບບລະບົບ. ໂຄງການຈົບຊັ້ນແມ່ນລະບົບຈັດການຮ້ານຄ້າອອນລາຍ.',
                    'en' => 'Studied software development, databases and system design. Final-year project: an online store management system.',
                ],
            ],
            [
                'institution' => 'Online Course',
                'degree' => ['lo' => 'ໃບຢັ້ງຢືນ ການອອກແບບ UI/UX', 'en' => 'UI/UX Design Certificate'],
                'field' => ['lo' => 'ການອອກແບບປະສົບການຜູ້ໃຊ້', 'en' => 'User Experience Design'],
                'start_date' => '2022-01-01', 'end_date' => '2022-06-30',
                'description' => [
                    'lo' => 'ຮຽນຮູ້ການຄົ້ນຄວ້າຜູ້ໃຊ້, Wireframe, Prototype ແລະ ການທົດສອບການໃຊ້ງານ.',
                    'en' => 'Covered user research, wireframing, prototyping and usability testing.',
                ],
            ],
            [
                'institution' => 'Vientiane High School',
                'degree' => ['lo' => 'ມັດທະຍົມສຶກສາຕອນປາຍ', 'en' => 'Upper Secondary Diploma'],
                'field' => null,
                'start_date' => '2014-09-01', 'end_date' => '2017-06-30',
                'description' => null,
            ],
        ];

        foreach ($entries as $order => $entry) {
            Education::create([...$entry, 'location' => 'Vientiane, Laos', 'sort_order' => $order, 'status' => 'published']);
        }
        ActivityLog::$enabled = $logging;
    }
}
