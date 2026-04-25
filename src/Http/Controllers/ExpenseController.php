<?php

namespace Whilesmart\Expenses\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Whilesmart\Expenses\Http\Requests\StoreExpenseRequest;
use Whilesmart\Expenses\Http\Requests\UpdateExpenseRequest;
use Whilesmart\Expenses\Http\Resources\ExpenseResource;
use Whilesmart\Expenses\Models\Expense;
use Whilesmart\OwnerAccess\Concerns\AuthorizesOwnerController;

class ExpenseController extends Controller
{
    use AuthorizesOwnerController;

    public function index(Request $request): JsonResponse
    {
        $query = $this->scopeAccessibleOwners(Expense::query(), $request->user());

        if ($request->filled('owner_type') && $request->filled('owner_id')) {
            $query->where('owner_type', $request->input('owner_type'))
                ->where('owner_id', $request->input('owner_id'));
        }

        if ($request->filled('vendor_type') && $request->filled('vendor_id')) {
            $query->where('vendor_type', $request->input('vendor_type'))
                ->where('vendor_id', $request->input('vendor_id'));
        }

        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }

        if ($request->filled('category')) {
            $query->where('category', $request->input('category'));
        }

        if ($request->filled('from')) {
            $query->where('incurred_at', '>=', $request->date('from'));
        }

        if ($request->filled('to')) {
            $query->where('incurred_at', '<=', $request->date('to'));
        }

        if ($request->filled('q')) {
            $term = $request->input('q');
            $query->where(function ($q) use ($term) {
                $q->where('vendor_name', 'ilike', "%{$term}%")
                    ->orWhere('description', 'ilike', "%{$term}%")
                    ->orWhere('number', 'ilike', "%{$term}%");
            });
        }

        $expenses = $query->latest('incurred_at')
            ->paginate((int) $request->input('per_page', 25));

        return response()->json([
            'success' => true,
            'data' => ExpenseResource::collection($expenses)->response()->getData(true),
        ]);
    }

    public function store(StoreExpenseRequest $request): JsonResponse
    {
        $data = $request->validated();
        $data['total_cents'] = ($data['amount_cents'] ?? 0) + ($data['tax_cents'] ?? 0);

        $expense = Expense::create($data);

        return response()->json([
            'success' => true,
            'data' => new ExpenseResource($expense),
        ], 201);
    }

    public function show(Expense $expense, Request $request): JsonResponse
    {
        $this->authorizeAccessTo($expense, $request->user());

        return response()->json([
            'success' => true,
            'data' => new ExpenseResource($expense->load('vendor')),
        ]);
    }

    public function update(UpdateExpenseRequest $request, Expense $expense): JsonResponse
    {
        $expense->fill($request->validated());
        $expense->recalculate()->save();

        return response()->json([
            'success' => true,
            'data' => new ExpenseResource($expense),
        ]);
    }

    public function destroy(Expense $expense, Request $request): JsonResponse
    {
        $this->authorizeAccessTo($expense, $request->user());
        $expense->delete();

        return response()->json([
            'success' => true,
            'message' => 'Expense deleted.',
        ]);
    }
}
