<?php

namespace Route;

use Exception;
use Illuminate\Foundation\Testing\WithFaker;
use JetBrains\PhpStorm\ArrayShape;
use Tests\TestCase;

class RouteTest extends TestCase
{
    use WithFaker;

    /**
     * Generate Phonebook payload for testing.
     *
     * @return array
     */
    #[ArrayShape(['first_name' => "string", 'last_name' => "string", 'phone_number' => "string", 'country_code' => "string", 'timezone_name' => "string"])]
    protected function getPayload(): array
    {
        return [
            'first_name' => $this->faker->firstName,
            'last_name' => $this->faker->lastName,
            'phone_number' => $this->faker->phoneNumber,
            'country_code' => $this->faker->countryCode,
            'timezone_name' => $this->faker->timezone,
        ];
    }

    /**
     * Create new item for testing.
     *
     * @return int|null Id of the new item
     */
    protected function createDummyItem(): ?int
    {
        // Try to create item for further update error
        try {

            $response = $this->post('/api/items?' . $this->getTokenStr(), $this->getPayload());

            if ($response->getStatusCode() === 201) {

                return (int)$response->original['data']->id;
            }

        } catch (Exception) {
            // Suppress errors if item can't create
        }

        return null;
    }

    /**
     * Get API auth token string for HTTP request.
     *
     * @return string
     */
    protected function getTokenStr(): string
    {
        return '&api_token=24f56647eddc650bd0904883dd7168e609017696cf69714fe7d1224012491710';
    }


    // API docs
    public function testApiDocs(): void
    {
        $response = $this->get('/api/documentation');

        $response->assertStatus(200);
    }


    // Search items
    public function testApiItems(): void
    {
        $response = $this->get('/api/items?' . $this->getTokenStr());
        var_dump($response);
        $response->assertStatus(200);

        // Check that there is list of items in the response
        $this->assertGreaterThan(10, $response->original['meta']['total']);
    }

    public function testApiItemsSearch(): void
    {
        $response = $this->get('/api/items?q=a' . $this->getTokenStr());
        var_dump($response);
        $response->assertStatus(200);

        // Check that there is list of items in the response
        $this->assertGreaterThan(10, $response->original['meta']['total']);
    }

    public function testApiItemsSearchNotFound(): void
    {
        $response = $this->get('/api/items?q=123456789' . $this->getTokenStr());

        $response->assertStatus(404);
        $response->assertJsonFragment(['errors' => ["Items not found."]]);
    }

    public function testApiItemsSearchEmptyQuery(): void
    {
        $response = $this->get('/api/items?q=' . $this->getTokenStr());

        $response->assertStatus(200);

        // Check that there is list of items in the response
        $this->assertGreaterThan(10, $response->original['meta']['total']);
    }

    public function testApiItemsSearchPages(): void
    {
        $response = $this->get('/api/items?q=a&page=2' . $this->getTokenStr());

        $response->assertStatus(200);

        // Check that there is list of items in the response
        $this->assertGreaterThan(10, $response->original['meta']['total']);
    }


    // Create item
    public function testApiItemsPostNewItem(): void
    {
        $response = $this->post('/api/items?' . $this->getTokenStr(), $this->getPayload());

        $response->assertStatus(201);
    }

    public function testApiItemsPostNewItemErrorTimeZone(): void
    {
        $payLoad = $this->getPayload();
        $payLoad['timezone_name'] = 'WRONG TIMEZONE';

        $response = $this->post('/api/items?' . $this->getTokenStr(), $payLoad);

        $response->assertStatus(422);
        $response->assertJsonFragment(['errors' => ["The timezone name must be Hostaway format."]]);
    }

    public function testApiItemsPostNewItemErrorCountryCode(): void
    {
        $payLoad = $this->getPayload();
        $payLoad['country_code'] = 'WRONG CODE';

        $response = $this->post('/api/items?' . $this->getTokenStr(), $payLoad);

        $response->assertStatus(422);
        $response->assertJsonFragment(['errors' => ["The country code must be Hostaway format."]]);
    }

    public function testApiItemsPostNewItemErrorRequiredFirstName(): void
    {
        $payLoad = $this->getPayload();
        $payLoad['first_name'] = '';

        $response = $this->post('/api/items?' . $this->getTokenStr(), $payLoad);

        $response->assertStatus(422);
        $response->assertJsonFragment(['errors' => ["The first name field is required."]]);
    }

    public function testApiItemsPostNewItemErrorTooLongFirstName(): void
    {
        $payLoad = $this->getPayload();
        $payLoad['first_name'] = 'qqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqq';

        $response = $this->post('/api/items?' . $this->getTokenStr(), $payLoad);

        $response->assertStatus(422);
        $response->assertJsonFragment(['errors' => ["The first name may not be greater than 300 characters."]]);
    }

    public function testApiItemsPostNewItemErrorNotUniqPhoneNumber(): void
    {
        $payLoad = $this->getPayload();
        $payLoad['phone_number'] = '12345678';

        // Try to create item for further create error
        try {
            $this->post('/api/items?' . $this->getTokenStr(), $payLoad);
        } catch (Exception) {
            // Suppress errors if item already exist
        }

        $response = $this->post('/api/items?' . $this->getTokenStr(), $payLoad);

        $response->assertStatus(422);
        $response->assertJsonFragment(['errors' => ["The phone number has already been taken."]]);
    }


    // Update item by id
    public function testApiItemsPatchItem(): void
    {
        $response = $this->patch('/api/items/1?' . $this->getTokenStr(), $this->getPayload());

        $response->assertStatus(200);
    }

    public function testApiItemsPatchItemErrorNotExist(): void
    {
        $payLoad = $this->getPayload();

        $response = $this->patch('/api/items/999999999?' . $this->getTokenStr(), $payLoad);

        $response->assertStatus(404);
        $response->assertJsonFragment(['errors' => ["Item not found."]]);
    }

    public function testApiItemsPatchItemErrorIdText(): void
    {
        $payLoad = $this->getPayload();

        $response = $this->patch('/api/items/text?' . $this->getTokenStr(), $payLoad);

        $response->assertStatus(422);
        $response->assertJsonFragment(['errors' => ["Item ID is not numeric."]]);
    }

    public function testApiItemsPatchItemErrorIdZero(): void
    {
        $payLoad = $this->getPayload();

        $response = $this->patch('/api/items/0?' . $this->getTokenStr(), $payLoad);

        $response->assertStatus(404);
        $response->assertJsonFragment(['errors' => ["Item not found."]]);
    }

    public function testApiItemsPatchItemErrorTimeZone(): void
    {
        $payLoad = $this->getPayload();
        $payLoad['timezone_name'] = 'WRONG TIMEZONE';

        $response = $this->patch('/api/items/1?' . $this->getTokenStr(), $payLoad);

        $response->assertStatus(422);
        $response->assertJsonFragment(['errors' => ["The timezone name must be Hostaway format."]]);
    }

    public function testApiItemsPatchItemErrorCountryCode(): void
    {
        $payLoad = $this->getPayload();
        $payLoad['country_code'] = 'WRONG CODE';

        $response = $this->patch('/api/items/1?' . $this->getTokenStr(), $payLoad);

        $response->assertStatus(422);
        $response->assertJsonFragment(['errors' => ["The country code must be Hostaway format."]]);
    }

    public function testApiItemsPatchItemErrorRequiredFirstName(): void
    {
        $payLoad = $this->getPayload();
        $payLoad['first_name'] = '';

        $response = $this->patch('/api/items/1?' . $this->getTokenStr(), $payLoad);

        $response->assertStatus(422);
        $response->assertJsonFragment(['errors' => ["The first name field is required."]]);
    }

    public function testApiItemsPatchItemErrorTooLongFirstName(): void
    {
        $payLoad = $this->getPayload();
        $payLoad['first_name'] = 'qqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqq';

        $response = $this->patch('/api/items/1?' . $this->getTokenStr(), $payLoad);

        $response->assertStatus(422);
        $response->assertJsonFragment(['errors' => ["The first name may not be greater than 300 characters."]]);
    }

    public function testApiItemsPatchItemErrorNotUniqPhoneNumber(): void
    {
        $payLoad = $this->getPayload();
        $payLoad['phone_number'] = '12345678';

        // Try to update item for further update error
        try {
            $this->patch('/api/items/1?' . $this->getTokenStr(), $payLoad);
        } catch (Exception) {
            // Suppress errors if item already exist
        }

        $response = $this->patch('/api/items/1?' . $this->getTokenStr(), $payLoad);

        $response->assertStatus(422);
        $response->assertJsonFragment(['errors' => ["The phone number has already been taken."]]);
    }


    // Find by id
    public function testApiItemsGetItem(): void
    {
        $response = $this->get('/api/items/1?' . $this->getTokenStr());

        $response->assertStatus(200);
    }

    public function testApiItemsGetItemErrorNotExist(): void
    {
        $payLoad = $this->getPayload();

        $response = $this->get('/api/items/999999999?' . $this->getTokenStr(), $payLoad);

        $response->assertStatus(404);
        $response->assertJsonFragment(['errors' => ["Item not found."]]);
    }

    public function testApiItemsGetItemErrorIdText(): void
    {
        $payLoad = $this->getPayload();

        $response = $this->get('/api/items/text?' . $this->getTokenStr(), $payLoad);

        $response->assertStatus(422);
        $response->assertJsonFragment(['errors' => ["Item ID is not numeric."]]);
    }

    public function testApiItemsGetItemErrorIdZero(): void
    {
        $payLoad = $this->getPayload();

        $response = $this->get('/api/items/0?' . $this->getTokenStr(), $payLoad);

        $response->assertStatus(404);
        $response->assertJsonFragment(['errors' => ["Item not found."]]);
    }


    // Delete by id
    public function testApiItemsDeleteItem(): void
    {
        // Create new item for further deletion
        $id = $this->createDummyItem();

        $this->assertGreaterThan(0, $id);

        $response = $this->delete('/api/items/' . $id . '?' . $this->getTokenStr());

        $response->assertStatus(200);
    }

    public function testApiItemsDeleteItemErrorNotExist(): void
    {
        $response = $this->delete('/api/items/999999999?' . $this->getTokenStr());

        $response->assertStatus(404);
        $response->assertJsonFragment(['errors' => ["Item not found."]]);
    }

    public function testApiItemsDeleteItemErrorIdText(): void
    {
        $response = $this->delete('/api/items/text?' . $this->getTokenStr());

        $response->assertStatus(422);
        $response->assertJsonFragment(['errors' => ["Item ID is not numeric."]]);
    }

    public function testApiItemsDeleteItemErrorIdZero(): void
    {
        $response = $this->delete('/api/items/0?' . $this->getTokenStr());

        $response->assertStatus(404);
        $response->assertJsonFragment(['errors' => ["Item not found."]]);
    }
}
