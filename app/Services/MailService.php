<?php

namespace App\Services;

use App\Models\Mail;
use App\Models\MailItem;
use App\Models\Character;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class MailService
{
    /**
     * Отправить почту персонажу
     */
    public function sendMail(int $receiverGuid, string $subject, string $body, array $items = [], int $money = 0, int $senderGuid = 0): bool
    {
        try {
            $now = time();
            $expireTime = $now + (30 * 24 * 60 * 60); // 30 дней

            // Если отправитель не указан, используем системный GUID
            if ($senderGuid === 0) {
                $senderGuid = 1; // Системный отправитель
            }

            $mail = Mail::create([
                'messageType' => 0, // Обычное сообщение
                'stationery' => 41, // Стандартный конверт
                'mailTemplateId' => 0,
                'sender' => $senderGuid,
                'receiver' => $receiverGuid,
                'subject' => $subject,
                'body' => $body,
                'has_items' => count($items) > 0 ? 1 : 0,
                'expire_time' => $expireTime,
                'deliver_time' => $now,
                'money' => $money,
                'cod' => 0,
                'checked' => 0
            ]);

            // Добавить предметы в почту
            foreach ($items as $item) {
                $this->addItemToMail($mail->id, $item['entry'], $item['count'] ?? 1);
            }

            Log::info("Mail sent to character {$receiverGuid}: {$subject}");
            return true;

        } catch (\Exception $e) {
            Log::error('Mail sending error: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Добавить предмет в почту
     */
    private function addItemToMail(int $mailId, int $itemEntry, int $count = 1): void
    {
        // Создаем предметы в базе данных персонажей
        for ($i = 0; $i < $count; $i++) {
            // Получаем следующий GUID для предмета
            $itemGuid = $this->getNextItemGuid();
            
            // Создаем запись в item_instance
            DB::connection('mysql_char')->table('item_instance')->insert([
                'guid' => $itemGuid,
                'itemEntry' => $itemEntry,
                'owner_guid' => 0, // Будет установлен при получении письма
                'creatorGuid' => 0,
                'giftCreatorGuid' => 0,
                'count' => 1,
                'duration' => 0,
                'charges' => '',
                'flags' => 0,
                'enchantments' => '',
                'randomPropertyId' => 0,
                'durability' => 0,
                'playedTime' => 0,
                'text' => ''
            ]);
            
            // Создаем запись в mail_items
            MailItem::create([
                'mail_id' => $mailId,
                'item_guid' => $itemGuid,
                'receiver' => $mailId // Используем mail_id как receiver для связи
            ]);

            Log::info("Item {$itemEntry} added to mail {$mailId} with GUID {$itemGuid}");
        }
    }

    /**
     * Получить следующий GUID для предмета
     */
    private function getNextItemGuid(): int
    {
        // Получаем максимальный GUID из item_instance
        $maxGuid = DB::connection('mysql_char')
            ->table('item_instance')
            ->max('guid') ?? 0;
        
        return $maxGuid + 1;
    }

    /**
     * Отправить товар из магазина персонажу
     */
    public function sendShopItem(int $characterGuid, string $itemName, array $itemData): bool
    {
        $subject = "Покупка в магазине: {$itemName}";
        $body = "Вы получили товар из магазина: {$itemName}";
        
        $items = [];
        $money = 0;

        // Обработка разных типов товаров
        if (isset($itemData['entry']) && $itemData['entry'] > 0) {
            $items[] = [
                'entry' => $itemData['entry'],
                'count' => $itemData['count'] ?? 1
            ];
        }

        if (isset($itemData['gold']) && $itemData['gold'] > 0) {
            $money = $itemData['gold'] * 10000; // Конвертация в медь
        }

        // Используем системного отправителя для товаров из магазина
        return $this->sendMail($characterGuid, $subject, $body, $items, $money, 1);
    }
}
