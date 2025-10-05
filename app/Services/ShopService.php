<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Models\ShopItem;
use App\Models\Purchase;
use App\Models\UserCurrency;
use App\Models\Character;
use App\Services\MailService;

class ShopService
{
    /**
     * Обработать покупку товара
     */
    public function processPurchase(int $accountId, int $itemId, int $characterGuid, array $serviceData = []): array
    {
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
            $this->executePurchase($item, $userCurrency, $character, $accountId, $serviceData);

            return ['success' => true, 'message' => __('shop.purchase_success')];

        } catch (\Exception $e) {
            Log::error('Shop purchase error: ' . $e->getMessage());
            return ['success' => false, 'message' => __('shop.purchase_error')];
        }
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
    private function executePurchase(ShopItem $item, UserCurrency $userCurrency, Character $character, int $accountId, array $serviceData = []): void
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
        $this->applyItemToCharacter($item, $character, $serviceData);
    }

    /**
     * Применить товар к персонажу
     */
    private function applyItemToCharacter(ShopItem $item, Character $character, array $serviceData = []): void
    {
        // Проверяем, является ли товар услугой
        if ($this->isServiceItem($item)) {
            $this->applyServiceDirectly($item, $character, $serviceData);
        } else {
            // Все остальные товары отправляем через почту
            $this->sendItemViaMail($item, $character);
        }
    }

    /**
     * Проверить, является ли товар услугой
     */
    private function isServiceItem(ShopItem $item): bool
    {
        // Услуги: смена расы, имени, пола, фракции, повышение уровня
        $serviceTypes = [
            'race_change',
            'name_change', 
            'gender_change',
            'faction_change',
            'level_boost',
            'customize'
        ];

        // Проверяем по типу товара или специальным полям
        if (in_array($item->category, ['Service', 'Customize'])) {
            return true;
        }

        // Проверяем специальные поля для услуг
        if ($item->level_boost !== null || 
            $item->race_change !== null ||
            $item->name_change !== null ||
            $item->gender_change !== null ||
            $item->faction_change !== null) {
            return true;
        }

        return false;
    }

    /**
     * Применить услугу напрямую к персонажу
     */
    private function applyServiceDirectly(ShopItem $item, Character $character, array $serviceData = []): void
    {
        $mailService = new MailService();
        
        // Буст уровня
        if ($item->level_boost !== null) {
            $character->level = $item->level_boost;
            $character->save();
            
            $subject = "Буст уровня: {$item->name}";
            $body = "Ваш персонаж получил буст уровня до {$item->level_boost} уровня.";
            $mailService->sendMail($character->guid, $subject, $body);
        }
        
        // Смена расы
        if (isset($serviceData['race']) && $serviceData['race'] !== null) {
            $character->race = $serviceData['race'];
            $character->save();
            
            $subject = "Смена расы: {$item->name}";
            $body = "Раса вашего персонажа была изменена на: " . $this->getRaceName($serviceData['race']);
            $mailService->sendMail($character->guid, $subject, $body);
        }
        
        // Смена имени
        if (isset($serviceData['name']) && $serviceData['name'] !== null) {
            $character->name = $serviceData['name'];
            $character->save();
            
            $subject = "Смена имени: {$item->name}";
            $body = "Имя вашего персонажа было изменено на: {$serviceData['name']}";
            $mailService->sendMail($character->guid, $subject, $body);
        }
        
        // Смена пола
        if (isset($serviceData['gender']) && $serviceData['gender'] !== null) {
            $character->gender = $serviceData['gender'];
            $character->save();
            
            $subject = "Смена пола: {$item->name}";
            $body = "Пол вашего персонажа был изменен на: " . ($serviceData['gender'] == 0 ? 'Мужской' : 'Женский');
            $mailService->sendMail($character->guid, $subject, $body);
        }
        
        // Смена фракции
        if (isset($serviceData['faction']) && $serviceData['faction'] !== null) {
            $character->race = $serviceData['faction'];
            $character->save();
            
            $subject = "Смена фракции: {$item->name}";
            $body = "Фракция вашего персонажа была изменена на: " . $this->getFactionName($serviceData['faction']);
            $mailService->sendMail($character->guid, $subject, $body);
        }
    }

    /**
     * Отправить товар через почту (предметы, маунты, питомцы, золото)
     */
    private function sendItemViaMail(ShopItem $item, Character $character): void
    {
        $mailService = new MailService();
        
        // Маунты и питомцы (приоритет)
        if ($item->entry && in_array($item->category, ['Mount', 'Pet'])) {
            $subject = "Маунт/Питомец: {$item->name}";
            $body = "Вы получили маунт/питомца: {$item->name}";
            
            $itemData = [
                'entry' => $item->entry,
                'count' => 1
            ];
            
            $mailService->sendShopItem($character->guid, $item->name, $itemData);
        }
        // Обычные предметы
        elseif ($item->entry && $item->is_item) {
            $subject = "Предмет: {$item->name}";
            $body = "Вы получили предмет: {$item->name}";
            
            $itemData = [
                'entry' => $item->entry,
                'count' => 1
            ];
            
            $mailService->sendShopItem($character->guid, $item->name, $itemData);
        }
        // Золото
        elseif ($item->gold_amount > 0) {
            $subject = "Золото: {$item->name}";
            $body = "Вы получили {$item->gold_amount} золота";
            
            $itemData = [
                'gold' => $item->gold_amount
            ];
            
            $mailService->sendShopItem($character->guid, $item->name, $itemData);
        }
    }

    /**
     * Получить название расы по ID
     */
    private function getRaceName(int $raceId): string
    {
        $races = [
            1 => 'Человек',
            2 => 'Орк',
            3 => 'Дворф',
            4 => 'Ночной эльф',
            5 => 'Нежить',
            6 => 'Таурен',
            7 => 'Гном',
            8 => 'Тролль',
            10 => 'Эльф крови',
            11 => 'Дреней'
        ];
        
        return $races[$raceId] ?? 'Неизвестная раса';
    }

    /**
     * Получить название фракции по ID
     */
    private function getFactionName(int $factionId): string
    {
        $factions = [
            1 => 'Альянс',
            2 => 'Орда'
        ];
        
        return $factions[$factionId] ?? 'Неизвестная фракция';
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
