<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {

        // Populate (A proba feladat miatt hardkodolva!)

        $DATA_SET = [

            'Language' => [
                1 => [ 'name' => 'angol' ],
                2 => [ 'name' => 'német' ],
            ],
            'LanguageScoreScale' => [
                1 => [ 'level' => 'B2', 'value' => 28 ],
                2 => [ 'level' => 'C1', 'value' => 40 ],
            ],
            'University' => [
                1 => [ 'name' => 'ELTE' ],
                2 => [ 'name' => 'PPKE' ],
            ],
            'Faculty' => [
                1 => [ 'name' => 'IK' ],
                2 => [ 'name' => 'BTK' ],
            ],
            'Course' => [
                1 => [ 'name' => 'Programtervező informatikus' ],
                2 => [ 'name' => 'Anglisztika' ],
            ],
            'Subject' => [
                1 => [ 'name' => 'magyar nyelv és irodalom' ],
                2 => [ 'name' => 'történelem' ],
                3 => [ 'name' => 'matematika' ],
                4 => [ 'name' => 'angol nyelv' ],
                5 => [ 'name' => 'informatika' ],
                6 => [ 'name' => 'fizika' ],
                7 => [ 'name' => 'biológia' ],
                8 => [ 'name' => 'kémia' ],
                9 => [ 'name' => 'francia nyelv' ],
                10=> [ 'name' => 'német nyelv' ],
                11=> [ 'name' => 'olasz nyelv' ],
                12=> [ 'name' => 'orosz nyelv' ],
                13=> [ 'name' => 'spanyol nyelv' ],
            ],

            'UniversityCourse' => [
                1 => [
                    'university'        => 1,       // ELTE
                    'faculty'           => 1,       // IK
                    'course'            => 1,       // Programtervezo informatikus
                    'subject'           => 3,       // Kotelezo tantargy (matematika)
                    'subject_advanced'  => false,   // Kotelezo tantargy (szint: kozep)
                ],
                2 => [
                    'university'        => 2,       // PPKE
                    'faculty'           => 2,       // BTK
                    'course'            => 2,       // Anglisztika
                    'subject'           => 4,       // Kotelezo tantargy (angol nyelv)
                    'subject_advanced'  => true,    // Kotelezo tantargy (szint: emelt)
                ],
            ],

            'CourseSubject' => [

                // ELTE IK (Pi.) - Valaszthato Biosz (Alap)
                2 => [
                    'university_course' => 1,
                    'subject'           => 7,
                    'advanced'          => false,
                ],

                // ELTE IK (Pi.) - Valaszthato Fizika (Alap)
                3 => [
                    'university_course' => 1,
                    'subject'           => 6,
                    'advanced'          => false,
                ],

                // ELTE IK (Pi.) - Valaszthato Info (Alap)
                4 => [
                    'university_course' => 1,
                    'subject'           => 5,
                    'advanced'          => false,
                ],

                // ELTE IK (Pi.) - Valaszthato Kemia (Alap)
                5 => [
                    'university_course' => 1,
                    'subject'           => 8,
                    'advanced'          => false,
                ],

                // PPKE BTK (Ang.) - Valaszthato Francia (Emelt)
                7 => [
                    'university_course' => 2,
                    'subject'           => 9,
                    'advanced'          => false,
                ],

                // PPKE BTK (Ang.) - Valaszthato Nemet (Emelt)
                8 => [
                    'university_course' => 2,
                    'subject'           => 10,
                    'advanced'          => false,
                ],

                // PPKE BTK (Ang.) - Valaszthato Olasz (Emelt)
                9 => [
                    'university_course' => 2,
                    'subject'           => 11,
                    'advanced'          => false,
                ],

                // PPKE BTK (Ang.) - Valaszthato Orosz (Emelt)
                10 => [
                    'university_course' => 2,
                    'subject'           => 12,
                    'advanced'          => false,
                ],

                // PPKE BTK (Ang.) - Valaszthato Spanyol (Emelt)
                11 => [
                    'university_course' => 2,
                    'subject'           => 13,
                    'advanced'          => false,
                ],

            ],

        ];

        foreach ($DATA_SET as $model => $set)
        {
            $modelClass = 'App\\Models\\'.$model;
            foreach ($set as $index => $data)
            {
                (new $modelClass($data))->save();
            }
        }

    }
}
