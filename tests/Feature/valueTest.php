<?php


// use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class valueTest extends TestCase {

    use \Tests\_resource\Helpers;

    public function test_invalid_szak() : void {

        foreach (array_keys($this->getValidPattern()['valasztott-szak']) as $attr) {

            $data = $this->getValidPattern();
            $data['valasztott-szak'][$attr] = $this->getInvalidString();

            $response = $this->post('/api',$data);

            $response->assertExactJson(['response' => 'hiba, a választott szak érvénytelen!']);
            $response->assertStatus(400);

        }

    }

    public function test_invalid_eredmenyek() : void {

        foreach (array_keys($this->getValidPattern()['erettsegi-eredmenyek'][0]) as $attr) {

            $data = $this->getValidPattern();
            $data['erettsegi-eredmenyek'][0][$attr] = $this->getInvalidString();

            $response = $this->post('/api',$data);

            switch ($attr) {
                case 'nev' : {
                    $response->assertExactJson(['response' => 'hiba, a ['.$this->getInvalidString().'] nevü tantárgy érvénytelen!']);
                    break;
                }
                case 'tipus' : {
                    $response->assertExactJson(['response' => 'hiba, a ['.$this->getInvalidString().'] típus érvénytelen a ['.$data['erettsegi-eredmenyek'][0]['nev'].'] tantárgynál!']);
                    break;
                }
                case 'eredmeny' : {
                    $response->assertExactJson(['response' => 'hiba, a ['.$this->getInvalidString().'] eredmény érvénytelen a ['.$data['erettsegi-eredmenyek'][0]['nev'].'] tantárgynál, minimum 0% maximum 100% lehet!']);
                    break;
                }
                default : {
                    throw new \RuntimeException('Unhandled [erettsegi-eredmenyek] attr testcase!');
                }
            }

            $response->assertStatus(400);

        }

    }

    public function test_invalid_tobbletpon_nyelviizsga() : void {

        foreach (array_diff(array_keys($this->getValidPattern()['tobbletpontok'][0]),['kategoria']) as $attr) {

            $data = $this->getValidPattern();
            $data['tobbletpontok'][0][$attr] = $this->getInvalidString();

            $response = $this->post('/api',$data);

            switch ($attr) {
                case 'tipus' : {
                    $response->assertExactJson(['response' => 'hiba, a [Nyelvvizsga] - ['.$this->getInvalidString().'] érvénytelen típus a többletpontnál!']);
                    break;
                }
                case 'nyelv' : {
                    $response->assertExactJson(['response' => 'hiba, a [Nyelvvizsga] - ['.$this->getInvalidString().'] érvénytelen nyelv a többletpontnál!']);
                    break;
                }
                default : {
                    throw new \RuntimeException('Unhandled [tobbletpontok] attr testcase!');
                }
            }

            $response->assertStatus(400);

        }

    }


}
