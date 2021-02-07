<?php

namespace App\Http\Controllers;

use App\Services\ItemService;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Pagination\LengthAwarePaginator;
use JetBrains\PhpStorm\ArrayShape;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * @OA\Info(
 *     title="API",
 *     description="Hostaway Phonebook API. Test API token for authorization '24f56647eddc650bd0904883dd7168e609017696cf69714fe7d1224012491710'.",
 *     version="0.1",
 *      @OA\Contact(
 *          email="george.webfullstack@gmail.com"
 *      ),
 * )
 * @OA\SecurityScheme(
 *     securityScheme="apiToken",
 *     type="apiKey",
 *     in="query",
 *     name="api_token",
 * )
 * @OA\Server(
 *      url="http://localhost",
 *      description="Api Server"
 * )
 * @OA\Tag(name="Items", description="Hostaway phonebook items")
 */
class ItemController extends Controller
{
    /**
     * PostController Constructor
     *
     * @param ItemService $itemService
     *
     */
    public function __construct(protected ItemService $itemService)
    {
    }

    /**
     * Generate 'links' array from pagination.
     *
     * @param LengthAwarePaginator $pagination Pagination object
     * @return mixed
     */
    protected function paginationGetLinks(LengthAwarePaginator $pagination): mixed
    {
        return $pagination->links()->elements;
    }

    /**
     * Generate 'meta' array from pagination.
     *
     * @param LengthAwarePaginator $pagination Pagination object
     * @return array
     */
    #[ArrayShape(['total' => "int", 'count' => "int", 'from' => "float|int|null", 'to' => "float|int|null", 'per_page' => "int"])]
    protected function paginationGetMeta(LengthAwarePaginator $pagination): array
    {
        return [
            'total' => $pagination->total(),
            'count' => $pagination->count(),
            'from' => $pagination->firstItem(),
            'to' => $pagination->lastItem(),
            'per_page' => $pagination->perPage(),
        ];
    }


    /**
     * Get items list, full or search.
     * If there is a filled 'q' parameter then search, and return search result.
     *
     * @OA\Get(
     *     path="/api/items",
     *     summary="Get items",
     *     tags={"Items"},
     *     description="Returns items from phonebook all or by query",
     *     security={{"apiToken": {"read:items"}}},
     *     @OA\Parameter(
     *          name="q",
     *          description="Query text",
     *          required=false,
     *          in="path",
     *          @OA\Schema(
     *              type="string"
     *          )
     *      ),
     *     @OA\Parameter(
     *          name="page",
     *          description="Page number",
     *          required=false,
     *          in="path",
     *          @OA\Schema(
     *              type="integer"
     *          )
     *      ),
     *     @OA\Response(response="200", description="Success"),
     *     @OA\Response(response=401, description="Unauthorized"),
     *     @OA\Response(response=422, description="Item get error"),
     * )
     * @param Request $request
     * @return JsonResponse
     */
    public function index(Request $request): JsonResponse
    {
        try {

            if ($request->q) {
                $pagination = $this->itemService->findByName($request->q);
            } else {
                $pagination = $this->itemService->getAll();
            }

            return response()
                ->json([
                    'message' => 'Success',
                    'data' => $pagination->items(),
                    'links' => $this->paginationGetLinks($pagination),
                    'meta' => $this->paginationGetMeta($pagination),
                ])
                ->setStatusCode(Response::HTTP_OK);

        } catch (NotFoundHttpException $e) {

            return response()
                ->json([
                    'message' => 'Items not found',
                    'data' => [],
                    'errors' => [$e->getMessage()],
                ])
                ->setStatusCode(Response::HTTP_NOT_FOUND);

        } catch (Exception $e) {

            return response()
                ->json([
                    'message' => 'Items get error',
                    'data' => [],
                    'errors' => [$e->getMessage()],
                ])
                ->setStatusCode(Response::HTTP_UNPROCESSABLE_ENTITY);
        }
    }

    /**
     * Get item by id.
     *
     * @OA\Get(
     *     path="/api/items/{id}",
     *     summary="Get item by id",
     *     tags={"Items"},
     *     description="Returns item from phonebook",
     *     security={{"apiToken": {"read:items"}}},
     *     @OA\Parameter(
     *          name="id",
     *          description="Item id",
     *          required=true,
     *          in="path",
     *          @OA\Schema(
     *              type="integer"
     *          )
     *      ),
     *     @OA\Response(response="200", description="Success"),
     *     @OA\Response(response=401, description="Unauthorized"),
     *     @OA\Response(response=404, description="Item not found"),
     *     @OA\Response(response=422, description="Item get error"),
     * )
     * @param string $id Item id
     * @return JsonResponse
     */
    public function show(string $id = ''): JsonResponse
    {
        try {

            return response()
                ->json([
                    'message' => 'Success',
                    'data' => $this->itemService->getById($id),
                ])
                ->setStatusCode(Response::HTTP_OK);

        } catch (NotFoundHttpException $e) {

            return response()
                ->json([
                    'message' => 'Item not found',
                    'data' => [],
                    'errors' => [$e->getMessage()],
                ])
                ->setStatusCode(Response::HTTP_NOT_FOUND);

        } catch (Exception $e) {

            return response()
                ->json([
                    'message' => 'Item get error',
                    'data' => [],
                    'errors' => [$e->getMessage()],
                ])
                ->setStatusCode(Response::HTTP_UNPROCESSABLE_ENTITY);
        }

    }

    /**
     * Create new item.
     *
     * @OA\Post(
     *     path="/api/items",
     *     summary="Create item",
     *     tags={"Items"},
     *     description="Create item in phonebook",
     *     security={{"apiToken": {"write:items"}}},
     *     @OA\Parameter(
     *          name="first_name",
     *          description="Item first name",
     *          required=true,
     *          in="path",
     *          @OA\Schema(
     *              type="string"
     *          )
     *      ),
     *      @OA\Parameter(
     *          name="last_name",
     *          description="Item last name",
     *          required=false,
     *          in="path",
     *          @OA\Schema(
     *              type="string"
     *          )
     *      ),
     *      @OA\Parameter(
     *          name="phone_number",
     *          description="Item uniq phone number",
     *          required=true,
     *          in="path",
     *          @OA\Schema(
     *              type="string"
     *          )
     *      ),
     *      @OA\Parameter(
     *          name="country_code",
     *          description="Item country code",
     *          required=false,
     *          in="path",
     *          @OA\Schema(
     *              type="string"
     *          )
     *      ),
     *      @OA\Parameter(
     *          name="timezone_name",
     *          description="Item timezone",
     *          required=false,
     *          in="path",
     *          @OA\Schema(
     *              type="string"
     *          )
     *      ),
     *     @OA\Response(response="200", description="Success"),
     *     @OA\Response(response=401, description="Unauthorized"),
     *     @OA\Response(response=422, description="Item create error"),
     * )
     * @param Request $request Input data
     * @return JsonResponse
     */
    public function store(Request $request): JsonResponse
    {
        try {

            return response()
                ->json([
                    'message' => 'Success',
                    'data' => $this->itemService->create($request),
                ])
                ->setStatusCode(Response::HTTP_CREATED);

        } catch (Exception $e) {

            return response()
                ->json([
                    'message' => 'Item create error',
                    'data' => [],
                    'errors' => [$e->getMessage()],
                ])
                ->setStatusCode(Response::HTTP_UNPROCESSABLE_ENTITY);
        }
    }

    /**
     * Update item by id.
     *
     * @OA\Patch(
     *     path="/api/items/{id}",
     *     summary="Update item by id",
     *     tags={"Items"},
     *     description="Update item in phonebook",
     *     security={{"apiToken": {"read:items"}}},
     *     @OA\Parameter(
     *          name="id",
     *          description="Item id",
     *          required=true,
     *          in="path",
     *          @OA\Schema(
     *              type="integer"
     *          )
     *      ),
     *     @OA\Parameter(
     *          name="first_name",
     *          description="Item first name",
     *          required=true,
     *          in="path",
     *          @OA\Schema(
     *              type="string"
     *          )
     *      ),
     *      @OA\Parameter(
     *          name="last_name",
     *          description="Item last name",
     *          required=false,
     *          in="path",
     *          @OA\Schema(
     *              type="string"
     *          )
     *      ),
     *      @OA\Parameter(
     *          name="phone_number",
     *          description="Item uniq phone number",
     *          required=true,
     *          in="path",
     *          @OA\Schema(
     *              type="string"
     *          )
     *      ),
     *      @OA\Parameter(
     *          name="country_code",
     *          description="Item country code",
     *          required=false,
     *          in="path",
     *          @OA\Schema(
     *              type="string"
     *          )
     *      ),
     *      @OA\Parameter(
     *          name="timezone_name",
     *          description="Item timezone",
     *          required=false,
     *          in="path",
     *          @OA\Schema(
     *              type="string"
     *          )
     *      ),
     *     @OA\Response(response="200", description="Success"),
     *     @OA\Response(response=401, description="Unauthorized"),
     *     @OA\Response(response=404, description="Item not found"),
     *     @OA\Response(response=422, description="Item update error"),
     * )
     * @param Request $request Input data
     * @param string  $id      Item id
     * @return JsonResponse
     */
    public function update(Request $request, string $id = ''): JsonResponse
    {
        try {

            return response()
                ->json([
                    'message' => 'Success',
                    'data' => $this->itemService->updateById($request, $id),
                ])
                ->setStatusCode(Response::HTTP_OK);

        } catch (NotFoundHttpException $e) {

            return response()
                ->json([
                    'message' => 'Item not found',
                    'data' => [],
                    'errors' => [$e->getMessage()],
                ])
                ->setStatusCode(Response::HTTP_NOT_FOUND);

        } catch (Exception $e) {

            return response()
                ->json([
                    'message' => 'Item update error',
                    'data' => [],
                    'errors' => [$e->getMessage()],
                ])
                ->setStatusCode(Response::HTTP_UNPROCESSABLE_ENTITY);
        }
    }

    /**
     * Delete item by id.
     *
     * @OA\Delete(
     *     path="/api/items/{id}",
     *     summary="Delete item by id",
     *     tags={"Items"},
     *     description="Delete item from phonebook",
     *     security={{"apiToken": {"read:items"}}},
     *     @OA\Parameter(
     *          name="id",
     *          description="Item id",
     *          required=true,
     *          in="path",
     *          @OA\Schema(
     *              type="integer"
     *          )
     *      ),
     *     @OA\Response(response="200", description="Success"),
     *     @OA\Response(response=401, description="Unauthorized"),
     *     @OA\Response(response=404, description="Item not found"),
     *     @OA\Response(response=422, description="Item delete error"),
     * )
     * @param string $id Item id
     * @return JsonResponse
     */
    public function destroy(string $id = ''): JsonResponse
    {
        try {

            return response()
                ->json([
                    'message' => 'Success',
                    'data' => $this->itemService->deleteById($id),
                ])
                ->setStatusCode(Response::HTTP_OK);

        } catch (NotFoundHttpException $e) {

            return response()
                ->json([
                    'message' => 'Item not found',
                    'data' => [],
                    'errors' => [$e->getMessage()],
                ])
                ->setStatusCode(Response::HTTP_NOT_FOUND);

        } catch (Exception $e) {

            return response()
                ->json([
                    'message' => 'Item delete error',
                    'data' => [],
                    'errors' => [$e->getMessage()],
                ])
                ->setStatusCode(Response::HTTP_UNPROCESSABLE_ENTITY);
        }

    }
}
