# Настройка SOAP сервера для AzerothCore

## Проблема
Ошибка: `SOAP-ERROR: Parsing WSDL: Couldn't load from '185.175.16.107:7878?WSDL' : failed to load external entity`

## Решение

### 1. Проверка доступности сервера

Сначала убедитесь, что сервер доступен:

```bash
# Проверка доступности порта
telnet 185.175.16.107 7878

# Или с помощью nc
nc -zv 185.175.16.107 7878

# Проверка HTTP ответа
curl -v http://185.175.16.107:7878
```

### 2. Настройка AzerothCore SOAP

#### В worldserver.conf:
```conf
# SOAP настройки
SOAP.Enabled = 1
SOAP.IP = 0.0.0.0
SOAP.Port = 7878
SOAP.User = admin
SOAP.Pass = admin
```

#### Перезапуск сервера:
```bash
# Остановить сервер
./worldserver

# Запустить с новой конфигурацией
./worldserver
```

### 3. Проверка WSDL

После настройки проверьте WSDL:
```bash
curl http://185.175.16.107:7878?WSDL
```

Должен вернуть XML с описанием SOAP сервиса.

### 4. Настройка .env

В файле `.env` добавьте:
```env
WOW_SOAP_URL=http://185.175.16.107:7878
WOW_SOAP_USERNAME=admin
WOW_SOAP_PASSWORD=admin
```

### 5. Альтернативные методы

Если SOAP не работает, можно использовать:

#### A. Прямые команды в консоли сервера:
```bash
# В консоли worldserver
.kick PlayerName Reason
.ban account AccountName 1d Reason
```

#### B. Через базу данных:
```sql
-- Установить online = 0 для всех персонажей аккаунта
UPDATE characters SET online = 0 WHERE account = (SELECT id FROM account WHERE username = 'AccountName');
```

#### C. Через файл команд:
```bash
# Создать файл команд
echo ".kick PlayerName Reason" > /path/to/commands.txt
```

### 6. Диагностика

Используйте страницу "Проверить SOAP" в админ панели для диагностики:

1. **Проверка порта** - доступен ли порт 7878
2. **HTTP ответ** - отвечает ли сервер на HTTP запросы
3. **SOAP клиент** - может ли PHP создать SOAP клиент
4. **WSDL парсинг** - может ли PHP загрузить WSDL

### 7. Возможные проблемы

#### Проблема: Порт заблокирован файрволом
**Решение:**
```bash
# Открыть порт в файрволе
ufw allow 7878
# Или
iptables -A INPUT -p tcp --dport 7878 -j ACCEPT
```

#### Проблема: SOAP не включен в PHP
**Решение:**
```bash
# Установить SOAP расширение
sudo apt-get install php-soap
# Или
sudo yum install php-soap
```

#### Проблема: Сервер не запущен
**Решение:**
```bash
# Проверить процессы
ps aux | grep worldserver

# Запустить сервер
cd /path/to/azerothcore
./worldserver
```

### 8. Тестирование

После настройки протестируйте:

1. Откройте админ панель
2. Перейдите в "Проверить SOAP"
3. Нажмите "Проверить соединение"
4. Проверьте диагностическую информацию

### 9. Логи

Проверьте логи для диагностики:
```bash
# Логи worldserver
tail -f /path/to/logs/worldserver.log

# Логи PHP
tail -f /var/log/php_errors.log

# Логи Laravel
tail -f storage/logs/laravel.log
```

## Заключение

Если SOAP не работает, система автоматически переключится на альтернативные методы кика (временный бан, отключение аккаунта, телепорт в безопасное место), которые должны отключить игрока даже без SOAP.
