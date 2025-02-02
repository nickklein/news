<?php

namespace NickKlein\News\Controllers;

use App\Http\Controllers\Controller;
use NickKlein\News\Requests\NewsSourceRequest;
use NickKlein\Tags\Requests\TagRequest;
use NickKlein\Tags\Resources\TagsResource;
use NickKlein\Tags\Services\TagsService;
use NickKlein\Tags\Repositories\TagsRepository;
use NickKlein\News\Services\NewsService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class NewsController extends Controller
{
    /**
     * 
     */
    public function index(NewsService $service)
    {
        return Inertia::render('News/Index', [
            'links' => $service->list(Auth::user()->id),
        ]);
    }

    /**
     * Render the Manage News section page
     * 
     * @param NewsService $service
     */
    public function edit(NewsService $service)
    {
        return Inertia::render('News/Edit', [
            'sources' => $service->listSources(Auth::user()->id),
            'sourceUpdateUrl' => route('news.edit.update-source'),
            'tags' => TagsResource::collection($service->listTags(Auth::user()->id)),
            'tagsAddUrl' => route('news.edit.add-tag'),
            'tagsRemoveUrl' => route('news.edit.remove-tag'),
        ]);
    }

    public function showFavourites(NewsService $service)
    {
        return Inertia::render('News/Favourites', [
            'links' => $service->listFavourite(Auth::user()->id),
        ]);
    }

    /**
     * Update source
     * 
     * @param NewsSourceRequest $request
     * @param NewsService $service
     * @return JsonResponse
     */
    public function updateSource(NewsSourceRequest $request, NewsService $service): JsonResponse
    {
        $fields = $request->validated();
        $response = $service->updateSources(Auth::user()->id, $fields['sourceId'], $fields['state']);

        if (!$response) {
            return response()->json(['state' => 'error']);
        }

        return response()->json(['state' => 'success']);
    }

    /**
     * Add Tag
     *
     * @param TagRequest $request
     * @param NewsService $service
     * @return JsonResponse
     */
    public function addTag(TagRequest $request, TagsRepository $tagsRepository): JsonResponse
    {
        $fields = $request->validated();
        $response = $tagsRepository->createUserTag(Auth::user()->id, $fields['tagName']);

        return response()->json($response);
    }

    /**
     * Destroy Tag
     *
     * @param TagRequest $request
     * @param TagsService $service
     * @return JsonResponse
     */
    public function removeTag(TagRequest $request, TagsService $service): JsonResponse
    {
        $fields = $request->validated();
        $response = $service->destroyUserTag(Auth::user()->id, $fields['tagName']);

        return response()->json($response);
    }

    public function toggleFavourites(Request $request, NewsService $service)
    {
        $fields = $request->validate([
            'sourceLinkId' => 'required|integer',
        ]);

        $response = $service->favourite(Auth::user()->id, $fields['sourceLinkId']);

        return response()->json($response);
    }
}
