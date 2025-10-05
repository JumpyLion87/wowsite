<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Models\ShopItem;
use App\Models\Purchase;
use App\Models\UserCurrency;
use App\Models\Character;

class ShopService
{
    /**
     * Обработать покупку товара
     */
    public function processPurchase(int $accountId, int $itemId, int $characterGuid): array
    {
        return DB::connection('mysql_site')->transaction(function () use ($accountId, $itemId, $characterGuid) {
            try {
                // Получить товар
                $item = ShopItem::find($itemId);
                if (!$item) {
                    return ['success' => false, 'message' => __('shop.item_not_found')];
                }

                // Получить валюту пользователя
                $userCurrency = UserCurrency::getByAccountId($accountId);
                if (!$userCurrency) {
                    return ['success' => false, 'message' => __('shop.user_currency_not_found')];
                }

                // Получить персонажа
                $character = Character::where('guid', $characterGuid)
                    ->where('account', $accountId)
                    ->first();

                if (!$character) {
                    return ['success' => false, 'message' => __('shop.character_not_found')];
                }

                // Проверки
                $checks = $this->validatePurchase($item, $userCurrency, $character);
                if (!$checks['valid']) {
                    return ['success' => false, 'message' => $checks['message']];
                }

                // Выполнить покупку
                $this->executePurchase($item, $userCurrency, $character, $accountId);

                return ['success' => true, 'message' => __('shop.purchase_success')];

            } catch (\Exception $e) {
                Log::error('Shop purchase error: ' . $e->getMessage());
                return ['success' => false, 'message' => __('shop.purchase_error')];
            }
        });
    }

    /**
     * Валидация покупки
     */
    private function validatePurchase(ShopItem $item, UserCurrency $userCurrency, Character $character): array
    {
        // Проверка наличия товара
        if (!$item->isInStock()) {
            return ['valid' => false, 'message' => __('shop.out_of_stock')];
        }

        // Проверка валюты
        if (!$userCurrency->hasEnoughCurrency($item->point_cost, $item->token_cost)) {
            return ['valid' => false, 'message' => __('shop.insufficient_funds')];
        }

        // Проверка персонажа онлайн
        if ($character->online) {
            return ['valid' => false, 'message' => __('shop.character_online')];
        }

        // Проверка уровня для бустов
        if ($item->category === 'Service' && $item->level_boost !== null) {
            if ($character->level >= $item->level_boost) {
                return ['valid' => false, 'message' => __('shop.level_too_high')];
            }
        }

        return ['valid' => true];
    }

    /**
     * Выполнить покупку
     */
    private function executePurchase(ShopItem $item, UserCurrency $userCurrency, Character $character, int $accountId): void
    {
        // Списать валюту
        $userCurrency->deductCurrency($item->point_cost, $item->token_cost);

        // Уменьшить количество товара
        if ($item->stock !== null) {
            $item->stock -= 1;
            $item->save();
        }

        // Записать покупку
        Purchase::createPurchase($accountId, $item->item_id, $item->point_cost, $item->token_cost);

        // Применить товар к персонажу
        $this->applyItemToCharacter($item, $character);
    }

    /**
     * Применить товар к персонажу
     */
    private function applyItemToCharacter(ShopItem $item, Character $character): void
    {
        switch ($item->category) {
            case 'Service':
                $this->applyService($item, $character);
                break;
            case 'Mount':
            case 'Pet':
                $this->applyMountOrPet($item, $character);
                break;
            case 'Gold':
                $this->applyGold($item, $character);
                break;
            case 'Stuff':
                $this->applyItem($item, $character);
                break;
        }
    }

    /**
     * Применить сервис (буст уровня)
     */
    private function applyService(ShopItem $item, Character $character): void
    {
        if ($item->level_boost !== null) {
            $character->level = $item->level_boost;
            $character->save();
        }
    }

    /**
     * Применить маунт или питомца
     */
    private function applyMountOrPet(ShopItem $item, Character $character): void
    {
        if ($item->entry) {
            // Здесь должна быть логика добавления маунта/питомца
            // Это зависит от конкретной реализации сервера
            Log::info("Applied mount/pet {$item->entry} to character {$character->name}");
        }
    }

    /**
     * Применить золото
     */
    private function applyGold(ShopItem $item, Character $character): void
    {
        if ($item->gold_amount > 0) {
            $newMoney = $character->money + ($item->gold_amount * 10000); // Конвертация в медь
            $character->money = min($newMoney, 4294967295); // Максимальное значение
            $character->save();
        }
    }

    /**
     * Применить предмет
     */
    private function applyItem(ShopItem $item, Character $character): void
    {
        if ($item->entry && $item->is_item) {
            // Здесь должна быть логика добавления предмета в инвентарь
            // Это зависит от конкретной реализации сервера
            Log::info("Applied item {$item->entry} to character {$character->name}");
        }
    }

    /**
     * Получить статистику магазина
     */
    public function getShopStats(): array
    {
        $totalItems = ShopItem::count();
        $totalPurchases = Purchase::count();
        $totalRevenue = Purchase::sum(DB::raw('point_cost + token_cost'));

        return [
            'total_items' => $totalItems,
            'total_purchases' => $totalPurchases,
            'total_revenue' => $totalRevenue
        ];
    }
}
