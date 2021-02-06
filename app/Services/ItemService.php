<?php

namespace App\Services;

use App\Models\Item;
use App\Repositories\ItemRepository;
use Exception;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use InvalidArgumentException;
use RuntimeException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class ItemService
{
    /**
     * @var int Number of the transactions before fail.
     */
    protected int $transactionRetry = 3;

    /**
     * @var int Request cache time.
     */
    protected int $cacheTime = 60;

    //todo env

    /**
     * ItemService constructor.
     *
     * @param ItemRepository    $itemRepository
     * @param ValidationService $validationService
     */
    public function __construct(
        protected ItemRepository $itemRepository,
        protected ValidationService $validationService
    )
    {
    }

    /**
     * Delete item by id.
     *
     * @param string $id Item id
     * @return Item
     * @throws ModelNotFoundException|InvalidArgumentException|RuntimeException
     */
    public function deleteById(string $id): Item
    {
        $this->validationService->checkItemId($id);

        try {

            return DB::transaction(function () use ($id) {

                return $this->itemRepository->delete((int)$id);

            }, $this->transactionRetry);


        } catch (ModelNotFoundException $e) {

            Log::channel('error')->info(get_class($this) . ' ' . $e->getMessage());

            throw new NotFoundHttpException('Item not found.');

        } catch (Exception $e) {

            Log::channel('error')->info(get_class($this) . ' ' . $e->getMessage());

            throw new RuntimeException('Unable to delete item data.');

        }
    }

    /**
     * Get all items.
     *
     * @return LengthAwarePaginator
     * @throws NotFoundHttpException|InvalidArgumentException
     */
    public function getAll(): LengthAwarePaginator
    {
        try {

            return $this->itemRepository->getAll();

        } catch (ModelNotFoundException $e) {

            Log::channel('error')->info(get_class($this) . ' ' . $e->getMessage());

            throw new NotFoundHttpException('Items not found.');

        } catch (Exception $e) {

            Log::channel('error')->info(get_class($this) . ' ' . $e->getMessage());

            throw new InvalidArgumentException('Unable to find items.');

        }
    }

    /**
     * Get item by id.
     *
     * @param string $id Item id
     * @return Item
     * @throws NotFoundHttpException|InvalidArgumentException|RuntimeException
     */
    public function getById(string $id): Item
    {
        $this->validationService->checkItemId($id);

        try {

            return $this->itemRepository->getById((int)$id);

        } catch (ModelNotFoundException $e) {

            Log::channel('error')->info(get_class($this) . ' ' . $e->getMessage());

            throw new NotFoundHttpException('Item not found.');

        } catch (Exception $e) {

            Log::channel('error')->info(get_class($this) . ' ' . $e->getMessage());

            throw new RuntimeException('Unable to update item data.');

        }
    }

    /**
     * Create new item with validation.
     *
     * @param Request $request Input data
     * @return Item
     * @throws InvalidArgumentException|RuntimeException
     */
    public function create(Request $request): Item
    {
        $this->validationService->checkRequestParams($request);

        try {

            return DB::transaction(function () use ($request) {

                return $this->itemRepository->save($request->all());

            }, $this->transactionRetry);

        } catch (Exception $e) {

            Log::channel('error')->info(get_class($this) . ' ' . $e->getMessage());

            throw new RuntimeException('Unable to create item.');

        }
    }

    /**
     * Find items by names (first and last).
     *
     * @param string $text Search text
     * @return LengthAwarePaginator
     * @throws NotFoundHttpException|RuntimeException
     */
    public function findByName(string $text): LengthAwarePaginator
    {
        //todo check text

        try {

            return $this->itemRepository->findByName($text);

        } catch (ModelNotFoundException $e) {

            Log::channel('error')->info(get_class($this) . ' ' . $e->getMessage());

            throw new NotFoundHttpException('Items not found.');

        } catch (Exception $e) {

            Log::channel('error')->info(get_class($this) . ' ' . $e->getMessage());

            throw new RuntimeException('Unable to find items.');

        }
    }

    /**
     * Update item data by id.
     *
     * @param Request $request Input data
     * @param string  $id      Item id
     * @return Item
     * @throws NotFoundHttpException|InvalidArgumentException|RuntimeException
     */
    public function updateById(Request $request, string $id): Item
    {

        $this->validationService->checkItemId($id);
        $this->validationService->checkRequestParams($request);

        try {

            return DB::transaction(function () use ($request, $id) {

                return $this->itemRepository->update($request->all(), (int)$id);

            }, $this->transactionRetry);

        } catch (ModelNotFoundException $e) {

            Log::channel('error')->info(get_class($this) . ' ' . $e->getMessage());

            throw new NotFoundHttpException('Item not found.');

        } catch (Exception $e) {

            Log::channel('error')->info(get_class($this) . ' ' . $e->getMessage());

            throw new RuntimeException('Unable to update item data.');

        }
    }
}
