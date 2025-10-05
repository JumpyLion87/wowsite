<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Models\ShopItem;
use App\Models\Purchase;
use App\Models\UserCurrency;
use App\Models\Character;
use App\Services\ShopService;

class ShopController extends Controller
{
    protected $shopService;

    public function __construct(ShopService $shopService)
    {
        $this->shopService = $shopService;
    }

    /**
     * Показать страницу магазина
     */
    public function index(Request $request)
    {
        $selectedCategory = $request->get('category', 'All');
        $validCategories = ['All', 'Service', 'Mount', 'Pet', 'Gold', 'Stuff'];
        
        if (!in_array($selectedCategory, $validCategories)) {
            $selectedCategory = 'All';
        }

        // Получить все товары (фильтрация будет на клиенте)
        $items = ShopItem::getGroupedByCategory();
        
        // Получить баланс пользователя
        $userBalance = ['points' => 0, 'tokens' => 0];
        $characters = [];
        
        if (Auth::check()) {
            $userCurrency = UserCurrency::getByAccountId(Auth::id());
            if ($userCurrency) {
                $userBalance = $userCurrency->getBalance();
            }
            
            // Получить персонажей пользователя
            $characters = Character::where('account', Auth::id())
                ->where('online', 0) // Только офлайн персонажи
                ->get(['guid', 'name', 'level', 'race', 'class']);
        }

        // Получить доступные категории
        $categories = ShopItem::getCategories();
        array_unshift($categories, 'All');

        return view('shop.index', compact(
            'items',
            'selectedCategory',
            'categories',
            'userBalance',
            'characters'
        ));
    }

    /**
     * Показать детали товара
     */
    public function show($id)
    {
        $item = ShopItem::with('itemTemplate')->findOrFail($id);
        
        $userBalance = ['points' => 0, 'tokens' => 0];
        $characters = [];
        
        if (Auth::check()) {
            $userCurrency = UserCurrency::getByAccountId(Auth::id());
            if ($userCurrency) {
                $userBalance = $userCurrency->getBalance();
            }
            
            $characters = Character::where('account', Auth::id())
                ->where('online', 0)
                ->get(['guid', 'name', 'level', 'race', 'class']);
        }

        return view('shop.show', compact('item', 'userBalance', 'characters'));
    }

    /**
     * Купить товар
     */
    public function buy(Request $request)
    {
        $request->validate([
            'item_id' => 'required|integer|exists:shop_items,item_id',
            'character_guid' => 'required|integer|exists:characters,guid'
        ]);

        if (!Auth::check()) {
            return redirect()->route('login')->with('error', __('shop.login_required'));
        }

        $itemId = $request->input('item_id');
        $characterGuid = $request->input('character_guid');

        try {
            $result = $this->shopService->processPurchase(
                Auth::id(),
                $itemId,
                $characterGuid
            );

            if ($result['success']) {
                return redirect()->route('shop.index')
                    ->with('success', __('shop.purchase_success'));
            } else {
                return redirect()->back()
                    ->with('error', $result['message']);
            }
        } catch (\Exception $e) {
            \Log::error('Shop purchase error: ' . $e->getMessage());
            return redirect()->back()
                ->with('error', __('shop.purchase_error'));
        }
    }

    /**
     * Получить историю покупок пользователя
     */
    public function history()
    {
        if (!Auth::check()) {
            return redirect()->route('login');
        }

        $purchases = Purchase::getUserPurchases(Auth::id());
        
        return view('shop.history', compact('purchases'));
    }

    /**
     * AJAX: Получить товары по категории
     */
    public function getItemsByCategory(Request $request)
    {
        $category = $request->get('category', 'All');
        
        if ($category === 'All') {
            $items = ShopItem::getGroupedByCategory();
        } else {
            $items = ShopItem::getByCategory($category)->groupBy('category');
        }

        return response()->json([
            'items' => $items,
            'category' => $category
        ]);
    }

    /**
     * AJAX: Проверить доступность товара
     */
    public function checkAvailability(Request $request)
    {
        $itemId = $request->get('item_id');
        $characterGuid = $request->get('character_guid');
        
        if (!Auth::check()) {
            return response()->json(['available' => false, 'reason' => 'not_logged_in']);
        }

        $item = ShopItem::find($itemId);
        if (!$item) {
            return response()->json(['available' => false, 'reason' => 'item_not_found']);
        }

        $userCurrency = UserCurrency::getByAccountId(Auth::id());
        if (!$userCurrency) {
            return response()->json(['available' => false, 'reason' => 'no_currency']);
        }

        $character = Character::where('guid', $characterGuid)
            ->where('account', Auth::id())
            ->first();

        if (!$character) {
            return response()->json(['available' => false, 'reason' => 'character_not_found']);
        }

        if ($character->online) {
            return response()->json(['available' => false, 'reason' => 'character_online']);
        }

        if (!$item->isInStock()) {
            return response()->json(['available' => false, 'reason' => 'out_of_stock']);
        }

        if (!$userCurrency->hasEnoughCurrency($item->point_cost, $item->token_cost)) {
            return response()->json(['available' => false, 'reason' => 'insufficient_funds']);
        }

        return response()->json(['available' => true]);
    }
}
