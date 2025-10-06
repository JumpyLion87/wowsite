# WoW SOAP Configuration

## Настройка SOAP соединения с игровым сервером

### 1. Переменные окружения (.env)

Добавьте следующие переменные в ваш `.env` файл:

```env
# WoW SOAP Configuration
WOW_SOAP_URL=http://localhost:7878
WOW_SOAP_USERNAME=admin
WOW_SOAP_PASSWORD=admin

# Alternative connection methods (optional)
WOW_REDIS_ENABLED=false
WOW_COMMAND_FILE_ENABLED=false
WOW_COMMAND_FILE_PATH=/tmp/wow_commands.txt
WOW_DB_COMMANDS_ENABLED=false
```

### 2. Настройка игрового сервера

Для работы SOAP API необходимо настроить игровой сервер:

#### AzerothCore
1. Установите SOAP модуль в `modules/`
2. Настройте в `worldserver.conf`:
```conf
SOAP.Enabled = 1
SOAP.IP = 0.0.0.0
SOAP.Port = 7878
SOAP.User = admin
SOAP.Pass = admin
```

#### TrinityCore
1. Включите SOAP в `worldserver.conf`:
```conf
SOAP.Enabled = 1
SOAP.IP = 0.0.0.0
SOAP.Port = 7878
SOAP.User = admin
SOAP.Pass = admin
```

### 3. Типы кика

Система поддерживает три типа кика:

1. **Мягкий кик** (`soft`) - только обновляет статус в базе данных
2. **Жесткий кик** (`hard`) - обновляет статус + устанавливает время выхода
3. **Принудительный кик** (`force`) - отправляет команду через SOAP API

### 4. Доступные GM команды

Через SOAP API можно выполнять следующие GM команды из [AzerothCore](https://www.azerothcore.org/wiki/gm-commands):

#### Управление игроками:
- `.kick {player} {reason}` - кик игрока
- `.ban account {account} {duration} {reason}` - бан аккаунта
- `.ban character {character} {duration} {reason}` - бан персонажа
- `.ban ip {ip} {duration} {reason}` - бан по IP
- `.unban account {account}` - разбан аккаунта
- `.unban character {character}` - разбан персонажа
- `.unban ip {ip}` - разбан по IP

#### Телепортация:
- `.teleport {player} {x} {y} {z} {map}` - телепорт игрока
- `.appear {player}` - телепорт к игроку
- `.summon {player}` - призвать игрока
- `.unstuck {player} [inn/graveyard/startzone]` - телепорт в безопасное место

#### Управление персонажами:
- `.revive {player}` - воскрешение игрока
- `.heal {player}` - лечение игрока
- `.freeze {player}` - заморозка игрока
- `.unfreeze {player}` - разморозка игрока
- `.mute {player} {duration} {reason}` - заглушение игрока
- `.unmute {player}` - разглушение игрока

#### Предметы и валюта:
- `.additem {player} {itemid} {count}` - добавить предмет
- `.addmoney {player} {amount}` - добавить деньги
- `.removemoney {player} {amount}` - удалить деньги

#### Информация о сервере:
- `.server info` - информация о сервере
- `.account onlinelist` - список онлайн аккаунтов
- `.announce {message}` - объявление всем игрокам

### 5. Проверка соединения

Используйте кнопку "Проверить SOAP" в админ панели для проверки соединения с сервером.

### 6. Логирование

Все действия с персонажами логируются в Laravel логах:
- Кик персонажей
- Телепорт персонажей
- SOAP команды

### 7. Безопасность

- SOAP соединение защищено логином и паролем
- Все действия требуют админских прав
- Команды логируются для аудита

### 8. Устранение неполадок

Если SOAP не работает:

1. Проверьте, что сервер запущен
2. Убедитесь, что порт 7878 доступен
3. Проверьте логи сервера на ошибки SOAP
4. Убедитесь, что логин и пароль правильные
5. Проверьте файрвол и сетевые настройки
