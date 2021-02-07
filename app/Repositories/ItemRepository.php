<?php

namespace App\Repositories;

use App\Models\Item;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Pagination\LengthAwarePaginator;

class ItemRepository
{
    /**
     * @var int Items per page.
     */
    private int $itemsPerPage;

    /**
     * ItemRepository constructor.
     *
     * @param Item $item
     */
    public function __construct(protected Item $item)
    {
        //todo env
        $this->itemsPerPage = 2; //config('api_items_per_page');
        // dd(config('app.timezone'), env('API_ITEMS_PER_PAGE'));
    }

    /**
     * Get all items or fail.
     *
     * @return LengthAwarePaginator
     * @throws ModelNotFoundException
     */
    public function getAll(): LengthAwarePaginator
    {
        $result = $this->item::orderBy('id', 'desc')
            ->paginate($this->itemsPerPage)
            ->withQueryString();

        if (!$result->items()) {
            throw new ModelNotFoundException('Items not found.');
        }

        return $result;
    }

    /**
     * Get item by id or fail.
     *
     * @param int $id Item id
     * @return Item
     */
    public function getById(int $id): Item
    {
        return $this->item->findOrFail($id);
    }


    /**
     * Find items by names (last and first).
     *
     * @param string $text Search text
     * @return LengthAwarePaginator
     * @throws ModelNotFoundException
     */
    public function findByName(string $text): LengthAwarePaginator
    {
        //todo clear text sql inject
        $result = $this->item::orderBy('id', 'desc')
            ->where('first_name', 'like', '%' . $text . '%')
            ->orWhere('last_name', 'like', '%' . $text . '%')
            ->paginate($this->itemsPerPage)
            ->withQueryString();

        if (!$result->items()) {
            throw new ModelNotFoundException('Items not found.');
        }

        return $result;
    }

    /**
     * Save Item.
     *
     * @param array $data Data to save
     * @return Item
     */
    public function save(array $data): Item
    {
        $item = new $this->item;
        $item->first_name = $data['first_name'];
        $item->last_name = $data['last_name'];
        $item->phone_number = $data['phone_number'];
        $item->country_code = $data['country_code'];
        $item->timezone_name = $data['timezone_name'];

        $item->save();

        return $item->fresh();
    }

    /**
     * Update Item by id.
     *
     * @param array $data Data to update
     * @param int   $id   Item id
     * @return Item
     */
    public function update(array $data, int $id): Item
    {

        $item = $this->item->findOrFail($id);

        $item->first_name = $data['first_name'];
        $item->last_name = $data['last_name'];
        $item->phone_number = $data['phone_number'];
        $item->country_code = $data['country_code'];
        $item->timezone_name = $data['timezone_name'];

        $item->update();

        return $item;
    }

    /**
     * Delete item by id or fail.
     *
     * @param int $id Item id
     * @return Item
     */
    public function delete(int $id): Item
    {

        $item = $this->item->findOrFail($id);
        $item->delete();

        return $item;
    }
}
