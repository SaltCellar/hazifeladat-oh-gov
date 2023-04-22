<?php


// use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class patternTest extends TestCase {

    use \Tests\_resource\Helpers;

    public function test_valid() : void {

        $data = $this->getValidPattern();

        $response = $this->post('/api',$data);

        $response->assertExactJson(['response' => '378 (300 alappont + 78 többletpont)']);
        $response->assertStatus(200);

        unset($data['tobbletpontok']);

        $response = $this->post('/api',$data);

        $response->assertExactJson(['response' => '350 (300 alappont + 50 többletpont)']);
        $response->assertStatus(200);

    }

    public function test_invalid_szak() : void {

        $data = $this->getValidPattern();
        unset($data['valasztott-szak']);

        $response = $this->post('/api',$data);

        $response->assertExactJson(['response' => 'hiba, a [valasztott-szak] mező kitöltése kötelező!']);
        $response->assertStatus(400);

    }

    public function test_invalid_in_szak() : void {

        foreach (array_keys($this->getValidPattern()['valasztott-szak']) as $attr) {
            $data = $this->getValidPattern();
            unset($data['valasztott-szak'][$attr]);

            $response = $this->post('/api',$data);

            $response->assertExactJson(['response' => 'hiba, a [valasztott-szak.'.$attr.'] mező kitöltése kötelező!']);
            $response->assertStatus(400);
        }

    }

    public function test_invalid_eredmenyek() : void {

        $data = $this->getValidPattern();
        unset($data['erettsegi-eredmenyek']);

        $response = $this->post('/api',$data);

        $response->assertExactJson(['response' => 'hiba, a [erettsegi-eredmenyek] mező kitöltése kötelező!']);
        $response->assertStatus(400);

    }

    public function test_invalid_in_eredmenyek() : void {

        foreach (array_keys($this->getValidPattern()['erettsegi-eredmenyek'][0]) as $attr) {
            $data = $this->getValidPattern();
            unset($data['erettsegi-eredmenyek'][0][$attr]);

            $response = $this->post('/api',$data);

            $response->assertExactJson(['response' => 'hiba, a [erettsegi-eredmenyek.0.'.$attr.'] mező kitöltése kötelező!']);
            $response->assertStatus(400);
        }

    }

    public function test_invalid_in_tobbletpontok() : void {

        $data = $this->getValidPattern();
        unset($data['tobbletpontok'][0]['kategoria']);


        $response = $this->post('/api',$data);

        $response->assertExactJson(['response' => 'hiba, a [tobbletpontok.0.kategoria] mező kitöltése kötelező!']);
        $response->assertStatus(400);

    }

    public function test_invalid_in_tobbletpontok_nyelvvizsga() : void {

        foreach (['tipus','nyelv'] as $attr) {
            $data = $this->getValidPattern();
            unset($data['tobbletpontok'][0][$attr]);

            $response = $this->post('/api',$data);

            $response->assertExactJson(['response' => 'hiba, a [tobbletpontok.0.'.$attr.'] mező kitöltése kötelező!']);
            $response->assertStatus(400);
        }

    }

    /* -------------------------------------------------------------------------------------------------------------- */

    public function test_duplocated_eredmenyek() : void {

        $data = $this->getValidPattern();

        $data['erettsegi-eredmenyek'] [] = [
            'nev'       => 'matematika',
            'tipus'     => 'emelt',
            'eredmeny'  => '100%',
        ];

        $data['erettsegi-eredmenyek'] [] = [
            'nev'       => 'matematika',
            'tipus'     => 'emelt',
            'eredmeny'  => '100%',
        ];

        $response = $this->post('/api',$data);

        $response->assertExactJson(['response' => 'hiba, a [matematika] tárgy többször szerepel ugyan azon a szinten!']);
        $response->assertStatus(400);

    }

    public function test_duplocated_tobbletpontok() : void {

        $data = $this->getValidPattern();

        $data['tobbletpontok'] [] = [
            'kategoria' => 'Nyelvvizsga',
            'tipus'     => 'C1',
            'nyelv'     => 'angol',
        ];

        $data['tobbletpontok'] [] = [
            'kategoria' => 'Nyelvvizsga',
            'tipus'     => 'C1',
            'nyelv'     => 'angol',
        ];

        $response = $this->post('/api',$data);

        $response->assertExactJson(['response' => 'hiba, a [angol] nyelvvizsga többször szerepel ugyan azon a szinten!']);
        $response->assertStatus(400);

    }

}
