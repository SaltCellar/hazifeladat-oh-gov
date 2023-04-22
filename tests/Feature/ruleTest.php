<?php


// use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ruleTest extends TestCase {

    use \Tests\_resource\Helpers;

    /*

    CASE:

        ... tegyük fel, hogy a felvételi összpontszámot 400+100
        (alappont+többletpont) pontos pontszámítási rendszerben kell kiszámítani.

        ...
        A többletpontok összege 0 és legfeljebb 100 pont között lehetséges abban az esetben
        is, ha a jelentkező különböző jogcímek alapján elért többletpontjainak az összege ezt
        meghaladná.

    TEST:

        A lehető legmagasabb pontot kisajtolni! hogy meghaladja a limiteket.

    */

    public function test_eredmeny_pont_hatar_atlepes() : void {

        $data = $this->getValidPatternMaximum();

        $response = $this->post('/api',$data);
        $response->assertExactJson(['response' => '500 (400 alappont + 100 többletpont)']);
        $response->assertStatus(200);

    }

    /*

    CASE:

        Egy adott tárgyból 0-100% között lehet a felvételiző tantárgyi érettségi eredménye.

    TEST:

        [-200, -1, 101, 200]  al végig próbálni egy tantárgyat!

    */

    public function test_tantargy_szazalek_hataron_kivul() : void {

        foreach ([-200, -1, 101, 200] as $eredmeny) {

            $data = $this->getValidPatternMaximum();
            $data['erettsegi-eredmenyek'][0]['eredmeny'] = $eredmeny . '%';
            $response = $this->post('/api',$data);
            $response->assertExactJson(['response' => 'hiba, a ['.$eredmeny.'%] eredmény érvénytelen a ['.$data['erettsegi-eredmenyek'][0]['nev'].'] tantárgynál, minimum 0% maximum 100% lehet!']);
            $response->assertStatus(400);

        }

    }

    /*

    CASE:

        Amennyiben valamely tárgyból 20% alatt teljesített a felvételiző, úgy sikertelen az
        érettségi eredménye és a pontszámítás nem lehetséges.

    TEST:

        20% Alatt beküldeni egy kötelező tantárgyat.

    */

    public function test_kotelezo_tantargy_hatar_ertek_alatt() : void {

        $data = $this->getValidPatternMaximum();
        $data['erettsegi-eredmenyek'][0]['eredmeny'] = '10' . '%';
        $response = $this->post('/api',$data);
        $response->assertExactJson(['response' => 'hiba, nem lehetséges a pontszámítás a ['.$data['erettsegi-eredmenyek'][0]['nev'].'] tárgyból elért 20% alatti eredmény miatt!']);
        $response->assertStatus(400);

    }

    /*

    CASE:

        A jelentkezőknek a következő tárgyakból kötelező érettségi vizsgát tennie: magyar
        nyelv és irodalom, történelem és matematika egyéb esetben a pontszámítás nem
        lehetséges.

    TEST:

        [ magyar nyelv és irodalom, történelem, matematika ] - nélkül külön beküldeni tantárgyakat

    */

    public function test_kotelezo_tantargy_nelkul() : void {

        foreach (range(0,2) as $i) {

            $data = $this->getValidPatternMaximum();

            unset($data['erettsegi-eredmenyek'][$i]);
            $data['erettsegi-eredmenyek'] = array_values($data['erettsegi-eredmenyek']);

            $response = $this->post('/api',$data);
            $response->assertExactJson(['response' => 'hiba, nem lehetséges a pontszámítás a kötelező érettségi tárgyak hiánya miatt!']);
            $response->assertStatus(400);

        }

    }

    /*

    CASE:

        ... vagy egyetlen kötelezően választható tárgyból sem
        tett érettségit a hallgató, úgy a pontszámítás nem lehetséges.

    TEST:

        A kötelezen választhatókbol egyet sem küldünk be

    */

    public function test_kotelezoen_valaszthato_tantargy_nelkul() : void {

        $data = $this->getValidPatternMaximum();

        unset($data['erettsegi-eredmenyek'][3]);
        $data['erettsegi-eredmenyek'] = array_values($data['erettsegi-eredmenyek']);

        $response = $this->post('/api',$data);
        $response->assertExactJson(['response' => 'hiba, nem lehetséges a pontszámítás a kötelezően válaszhtazó érettségi tárgy hiánya miatt!']);
        $response->assertStatus(400);

    }

    /*

    CASE:

        Amennyiben a jelentkező egyazon nyelvből tett le több sikeres nyelvvizsgát, úgy a
        többletpontszámítás során egyszer kerülnek kiértékelésre a nagyobb pontszám
        függvényében (pl.: angol B2 és angol C1 összértéke 40 pont lesz).

    TEST:

        Több nyelvvizsgát azonos nyelven de eltérőő szinten küldünk be

    */

    public function test_azonos_nyelv_eltero_szintu_nyelvizsga() : void {

        $languageScenarios = [
            [
                [
                    'kategoria' => 'Nyelvvizsga',
                    'tipus'     => 'B2',
                    'nyelv'     => 'angol',
                ],
            ],
            [
                [
                    'kategoria' => 'Nyelvvizsga',
                    'tipus'     => 'B2',
                    'nyelv'     => 'angol',
                ],
                [
                    'kategoria' => 'Nyelvvizsga',
                    'tipus'     => 'C1',
                    'nyelv'     => 'angol',
                ],
            ],
        ];
        $languageScenariosResults = [
            '112 (84 alappont + 28 többletpont)',
            '124 (84 alappont + 40 többletpont)',

        ];

        foreach ($languageScenarios as $index => $scenario) {

            $data = $this->getValidPatternMinimum();

            $data['tobbletpontok'] = $scenario;

            $response = $this->post('/api',$data);
            $response->assertExactJson(['response' => $languageScenariosResults[$index]]);
            $response->assertStatus(200);

        }



    }

    /*

    CASE:

        -- A jobb pontal elért választható tárgy figyelembe vétele

    TEST:

        beküldünk egy
            - Választható nem a legjobb,
            - Választható legjobb,          <-- ezt kell figyelembe venni
            - Nem választható (nagyon jó)..
        Tantárgyakbol álló pontszámítást.

    */

    public function test_valaszthato_targyak_eltero_pontok() : void {

        $data = $this->getValidPatternMinimum();

        // Választható nem a legjobb
        $data['erettsegi-eredmenyek'][3] = [
            'nev'       => 'informatika',
            'tipus'     => 'emelt',
            'eredmeny'  => '21%',
        ];

        // Választható legjobb
        $data['erettsegi-eredmenyek'][4] = [
            'nev'       => 'fizika',
            'tipus'     => 'közép',
            'eredmeny'  => '31%',
        ];

        // Nem választható (nagyon jó)
        $data['erettsegi-eredmenyek'][5] = [
            'nev'       => 'történelem', // 'orosz nyelv',
            'tipus'     => 'emelt',
            'eredmeny'  => '100%',
        ];

        $response = $this->post('/api',$data);
        $response->assertExactJson(['response' => '104 (104 alappont + 0 többletpont)']);
        $response->assertStatus(200);

    }

    /* -------------------------------------------------------------------------------------------------------------- */

    /*

    CASE:

        -- A jobb pontal elért tárgy figyelembe vétele ( matek-alap vs matek-emelt )

    TEST:

        Alap és Emelt matekkal de eltérő / ellentétes pontszámmal küldjük be.

    */

    public function test_azonos_tantargy_eltero_szintu_pontok() : void {

        $data = $this->getValidPatternMinimum();
        $data['erettsegi-eredmenyek'][] = [
            'nev'       => 'matematika',
            'tipus'     => 'emelt',
            'eredmeny'  => '31%',
        ];

        $response = $this->post('/api',$data);
        $response->assertExactJson(['response' => '154 (104 alappont + 50 többletpont)']);
        $response->assertStatus(200);

        // Alacsonyabb emelt pontszám...

        $data = $this->getValidPatternMinimum();
        $data['erettsegi-eredmenyek'][] = [
            'nev'       => 'matematika',
            'tipus'     => 'emelt',
            'eredmeny'  => '21%',
        ];

        $data['erettsegi-eredmenyek'][2]['eredmeny'] = '31%';

        $response = $this->post('/api',$data);
        $response->assertExactJson(['response' => '104 (104 alappont + 0 többletpont)']);
        $response->assertStatus(200);

        // Elégtelen emelt pontszám...

        $data = $this->getValidPatternMinimum();
        $data['erettsegi-eredmenyek'][] = [
            'nev'       => 'matematika',
            'tipus'     => 'emelt',
            'eredmeny'  => '10%',
        ];

        $data['erettsegi-eredmenyek'][2]['eredmeny'] = '31%';

        $response = $this->post('/api',$data);
        $response->assertExactJson(['response' => '104 (104 alappont + 0 többletpont)']);
        $response->assertStatus(200);

    }

}
