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
    protected function paginationGetLinks(LengthAwarePaginator $pagination)
    {
        //todo prev last etc
        return $pagination->links()->elements;
    }

    /**
     * Generate 'meta' array from pagination.
     *
     * @param LengthAwarePaginator $pagination Pagination object
     * @return array
     */
    #[ArrayShape(['total' => "mixed", 'count' => "mixed"])]
    protected function paginationGetMeta(LengthAwarePaginator $pagination): array
    {
        //todo prev last etc
        return [
            'total' => $pagination->total(),
            'count' => $pagination->count(),
        ];
    }


    /**
     * Get items list, full or search.
     * If there is a filled 'q' parameter then search, and return search result.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function list(Request $request): JsonResponse
    {
        try {

            if ($request->q) {
                $pagination = $this->itemService->findByName($request->q);
            } else {
                $pagination = $this->itemService->getAll();
            }

            return response()
                ->json([
                    'message' => 'Items list get',
                    'data' => $pagination->items(),
                    'links' => $this->paginationGetLinks($pagination),
                    'meta' => $this->paginationGetMeta($pagination),
                    'full' => $pagination, //todo del
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
                    'message' => 'Items list get error',
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
     *     @OA\Response(response="200", description="Item successfully got"),
     *     @OA\Response(response=401, description="Unauthorized"),
     *     @OA\Response(response=404, description="Item not found"),
     *     @OA\Response(response=422, description="Item get error"),
     * )
     * @param string $id Item id
     * @return JsonResponse
     */
    public function get(string $id = ''): JsonResponse
    {
        try {

            return response()
                ->json([
                    'message' => 'Item successfully got',
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
     *
     * @param Request $request Input data
     * @return JsonResponse
     */
    public function create(Request $request): JsonResponse
    {
        try {

            return response()
                ->json([
                    'message' => 'Item successfully created',
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
     * @param Request $request Input data
     * @param string  $id      Item id
     * @return JsonResponse
     */
    public function update(Request $request, string $id = ''): JsonResponse
    {
        try {

            return response()
                ->json([
                    'message' => 'Item successfully updated',
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
     * @param string $id Utem id
     * @return JsonResponse
     */
    public function delete(string $id = ''): JsonResponse
    {
        try {

            return response()
                ->json([
                    'message' => 'Item successfully deleted',
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
