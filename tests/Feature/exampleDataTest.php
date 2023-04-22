<?php


// use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class exampleDataTest extends TestCase {

    public function test_example_inputs() : void {

        $exampleInputsFilePathsAndAsserts = [
            [
                'filePath'          => 'tests\\_resource\\example_inputs\\example_data_0.json',
                'assertResponse'    => '470 (370 alappont + 100 többletpont)',
                'assertStatusCode'  => 200,
            ],
            [
                'filePath'          => 'tests\\_resource\\example_inputs\\example_data_1.json',
                'assertResponse'    => '476 (376 alappont + 100 többletpont)',
                'assertStatusCode'  => 200,
            ],
            [
                'filePath'          => 'tests\\_resource\\example_inputs\\example_data_2.json',
                'assertResponse'    => 'hiba, nem lehetséges a pontszámítás a kötelező érettségi tárgyak hiánya miatt!',
                'assertStatusCode'  => 400,
            ],
            [
                'filePath'          => 'tests\\_resource\\example_inputs\\example_data_3.json',
                'assertResponse'    => 'hiba, nem lehetséges a pontszámítás a [magyar nyelv és irodalom] tárgyból elért 20% alatti eredmény miatt!',
                'assertStatusCode'  => 400,
            ],
        ];
        foreach ($exampleInputsFilePathsAndAsserts as $exampleInputData ) {
            $exampleInput = json_decode(file_get_contents($exampleInputData['filePath']),true);

            $response = $this->post('/api',$exampleInput);

            $response->assertExactJson(['response' => $exampleInputData['assertResponse']]);
            $response->assertStatus($exampleInputData['assertStatusCode']);
        }

    }
}
