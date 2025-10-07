<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreSellerRequest;
use App\Http\Requests\UpdateSellerRequest;
use App\Http\Resources\SellerResource;
use App\Http\Resources\SaleResource;
use App\Models\Seller;
use App\Services\SellerService;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Response;

class SellerController extends Controller
{
    public function __construct(protected SellerService $sellerService)
    {
    }

    public function index(Request $request): AnonymousResourceCollection
    {
        $sellers = $this->sellerService->getAll($request);
        return SellerResource::collection($sellers);
    }

    public function store(StoreSellerRequest $request): SellerResource
    {
        $seller = $this->sellerService->create($request->user(), $request->validated());
        return SellerResource::make($seller);
    }

    public function show(Seller $seller): SellerResource
    {
        return SellerResource::make($seller);
    }

    public function update(UpdateSellerRequest $request, Seller $seller): SellerResource
    {
        $this->sellerService->update($request->user(), $seller, $request->validated());
        return SellerResource::make($seller->fresh());
    }

    public function destroy(Request $request, Seller $seller): Response
    {
        $this->sellerService->delete($request->user(), $seller);
        return response()->noContent();
    }

    public function sales(Request $request, Seller $seller): AnonymousResourceCollection
    {
        $sales = $this->sellerService->getSalesBySeller($seller, $request);
        return SaleResource::collection($sales);
    }
}
