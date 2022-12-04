<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Hash;
use Illuminate\Database\Seeder;
use App\Models\{
    ReportMessage,
    Organization,
    Teacher,
    Student,
    Report,
}; 

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        ////////////////////////////////////////////////////////////////////////
        // Make 2 organizations
        ////////////////////////////////////////////////////////////////////////

        $organization1 = new Organization([
            'name'              => 'Mallilan Yläkoulu',
            'street_address'    => 'Mallitie 42',
            'city'              => 'Oulu',
            'zip'               => '90500',
        ]);
        $organization1->save();

        $organization2 = new Organization([
            'name'              => 'Esimerkkilän Esikoulu',
            'street_address'    => 'Esimerkkitie 2E',
            'city'              => 'Vantaa',
            'zip'               => '00750',
        ]);
        $organization1->save();

        ////////////////////////////////////////////////////////////////////////
        // Make 2 teachers for Organization 1
        ////////////////////////////////////////////////////////////////////////

        $teacher1 = new Teacher([
            'first_name'    => 'Olli',
            'last_name'     => 'Opettaja',
            'email'         => 'olli.o@esimerkki.fi',
            'password'      => Hash::make('salasana'),
        ]);
        $organization1->teachers()->save($teacher1);

        $teacher2 = new Teacher([
            'first_name'    => 'Kaisa',
            'last_name'     => 'Kuraattori',
            'email'         => 'kaisa.k@esimerkki.fi',
            'password'      => Hash::make('salasana'),
        ]);
        $organization1->teachers()->save($teacher2);

        ////////////////////////////////////////////////////////////////////////
        // Make 3 students for Organization 1
        ////////////////////////////////////////////////////////////////////////

        $student1 = new Student([
            'first_name'    => 'Kerttu',
            'last_name'     => 'Koululainen',
            'email'         => 'kerttu.k@esimerkki.fi',
            'password'      => Hash::make('salasana'),
        ]);
        $organization1->teachers()->save($student1);

        $student2 = new Student([
            'first_name'    => 'Ville',
            'last_name'     => 'Vitosluokkalainen',
            'email'         => 'ville.v@esimerkki.fi',
            'password'      => Hash::make('salasana'),
        ]);
        $organization1->teachers()->save($student2);

        $student3 = new Student([
            'first_name'    => 'Elli',
            'last_name'     => 'Eskarilainen',
            'email'         => 'elli.e@esimerkki.fi',
            'password'      => Hash::make('salasana'),
        ]);
        $organization1->teachers()->save($student3);
    }
}
