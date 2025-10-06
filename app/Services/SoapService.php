<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Config;

class SoapService
{
    protected $soapClient;
    protected $soapUrl;
    protected $soapUsername;
    protected $soapPassword;

    public function __construct()
    {
        $this->soapUrl = config('wow.soap_url', 'http://localhost:7878');
        $this->soapUsername = config('wow.soap_username', 'admin');
        $this->soapPassword = config('wow.soap_password', 'admin');
    }

    /**
     * Создать SOAP клиент
     */
    protected function createSoapClient()
    {
        if ($this->soapClient) {
            return $this->soapClient;
        }

        try {
            $this->soapClient = new \SoapClient($this->soapUrl . '?WSDL', [
                'login' => $this->soapUsername,
                'password' => $this->soapPassword,
                'trace' => true,
                'exceptions' => true,
                'connection_timeout' => 10,
                'cache_wsdl' => WSDL_CACHE_NONE
            ]);

            return $this->soapClient;
        } catch (\Exception $e) {
            Log::error('SOAP client creation failed: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Выполнить GM команду
     */
    public function executeCommand($command, $params = [])
    {
        try {
            $soapClient = $this->createSoapClient();
            
            // Форматируем команду с параметрами
            $formattedCommand = $this->formatCommand($command, $params);
            
            $result = $soapClient->executeCommand([
                'command' => $formattedCommand
            ]);

            Log::info("SOAP command executed: {$formattedCommand} - Result: " . json_encode($result));
            return $result;

        } catch (\SoapFault $e) {
            Log::error('SOAP command error: ' . $e->getMessage());
            throw $e;
        } catch (\Exception $e) {
            Log::error('SOAP execution error: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Форматировать команду с параметрами
     */
    protected function formatCommand($command, $params = [])
    {
        $commandTemplate = config("wow.commands.{$command}", $command);
        
        // Заменяем плейсхолдеры на реальные значения
        foreach ($params as $key => $value) {
            $commandTemplate = str_replace("{{$key}}", $value, $commandTemplate);
        }

        return $commandTemplate;
    }

    /**
     * Кикнуть игрока
     */
    public function kickPlayer($playerName, $reason = 'Admin kick')
    {
        return $this->executeCommand('kick', [
            'player' => $playerName,
            'reason' => $reason
        ]);
    }

    /**
     * Забанить аккаунт
     */
    public function banAccount($accountName, $duration = '1d', $reason = 'Admin ban')
    {
        return $this->executeCommand('ban_account', [
            'account' => $accountName,
            'duration' => $duration,
            'reason' => $reason
        ]);
    }

    /**
     * Забанить персонажа
     */
    public function banCharacter($characterName, $duration = '1d', $reason = 'Admin ban')
    {
        return $this->executeCommand('ban_character', [
            'character' => $characterName,
            'duration' => $duration,
            'reason' => $reason
        ]);
    }

    /**
     * Разбанить аккаунт
     */
    public function unbanAccount($accountName)
    {
        return $this->executeCommand('unban_account', [
            'account' => $accountName
        ]);
    }

    /**
     * Телепортировать игрока
     */
    public function teleportPlayer($playerName, $x, $y, $z, $map = 0)
    {
        return $this->executeCommand('teleport', [
            'player' => $playerName,
            'x' => $x,
            'y' => $y,
            'z' => $z,
            'map' => $map
        ]);
    }

    /**
     * Воскресить игрока
     */
    public function revivePlayer($playerName)
    {
        return $this->executeCommand('revive', [
            'player' => $playerName
        ]);
    }

    /**
     * Вылечить игрока
     */
    public function healPlayer($playerName)
    {
        return $this->executeCommand('heal', [
            'player' => $playerName
        ]);
    }

    /**
     * Заморозить игрока
     */
    public function freezePlayer($playerName)
    {
        return $this->executeCommand('freeze', [
            'player' => $playerName
        ]);
    }

    /**
     * Разморозить игрока
     */
    public function unfreezePlayer($playerName)
    {
        return $this->executeCommand('unfreeze', [
            'player' => $playerName
        ]);
    }

    /**
     * Заглушить игрока
     */
    public function mutePlayer($playerName, $duration = '1h', $reason = 'Admin mute')
    {
        return $this->executeCommand('mute', [
            'player' => $playerName,
            'duration' => $duration,
            'reason' => $reason
        ]);
    }

    /**
     * Разглушить игрока
     */
    public function unmutePlayer($playerName)
    {
        return $this->executeCommand('unmute', [
            'player' => $playerName
        ]);
    }

    /**
     * Добавить предмет игроку
     */
    public function addItem($playerName, $itemId, $count = 1)
    {
        return $this->executeCommand('additem', [
            'player' => $playerName,
            'itemid' => $itemId,
            'count' => $count
        ]);
    }

    /**
     * Добавить деньги игроку
     */
    public function addMoney($playerName, $amount)
    {
        return $this->executeCommand('addmoney', [
            'player' => $playerName,
            'amount' => $amount
        ]);
    }

    /**
     * Получить информацию о сервере
     */
    public function getServerInfo()
    {
        return $this->executeCommand('server_info');
    }

    /**
     * Получить список онлайн игроков
     */
    public function getOnlinePlayers()
    {
        return $this->executeCommand('online_players');
    }

    /**
     * Отправить объявление
     */
    public function announce($message)
    {
        return $this->executeCommand('announce', [
            'message' => $message
        ]);
    }

    /**
     * Проверить соединение
     */
    public function checkConnection()
    {
        try {
            // Сначала проверяем доступность сервера
            $connectionTest = $this->testServerConnection();
            if (!$connectionTest['success']) {
                return [
                    'status' => 'error',
                    'message' => 'Сервер недоступен: ' . $connectionTest['message'],
                    'response' => null,
                    'diagnostics' => $connectionTest
                ];
            }

            // Затем проверяем SOAP
            $result = $this->getServerInfo();
            return [
                'status' => 'success',
                'message' => 'SOAP соединение работает',
                'response' => $result,
                'diagnostics' => $connectionTest
            ];
        } catch (\Exception $e) {
            return [
                'status' => 'error',
                'message' => 'Ошибка SOAP соединения: ' . $e->getMessage(),
                'response' => null,
                'diagnostics' => $this->getDiagnostics()
            ];
        }
    }

    /**
     * Тестировать соединение с сервером
     */
    protected function testServerConnection()
    {
        $url = parse_url($this->soapUrl);
        $host = $url['host'] ?? 'localhost';
        $port = $url['port'] ?? 7878;
        
        // Проверяем доступность порта
        $connection = @fsockopen($host, $port, $errno, $errstr, 5);
        if (!$connection) {
            return [
                'success' => false,
                'message' => "Не удается подключиться к {$host}:{$port}",
                'error' => "{$errno}: {$errstr}",
                'host' => $host,
                'port' => $port
            ];
        }
        
        fclose($connection);
        
        // Проверяем HTTP доступность
        $httpUrl = "http://{$host}:{$port}";
        $context = stream_context_create([
            'http' => [
                'timeout' => 5,
                'method' => 'GET'
            ]
        ]);
        
        $response = @file_get_contents($httpUrl, false, $context);
        if ($response === false) {
            return [
                'success' => false,
                'message' => "HTTP запрос к {$httpUrl} не удался",
                'host' => $host,
                'port' => $port
            ];
        }
        
        return [
            'success' => true,
            'message' => "Сервер {$host}:{$port} доступен",
            'host' => $host,
            'port' => $port,
            'http_response' => substr($response, 0, 200)
        ];
    }

    /**
     * Получить диагностическую информацию
     */
    protected function getDiagnostics()
    {
        $url = parse_url($this->soapUrl);
        $host = $url['host'] ?? 'localhost';
        $port = $url['port'] ?? 7878;
        
        return [
            'soap_url' => $this->soapUrl,
            'host' => $host,
            'port' => $port,
            'username' => $this->soapUsername,
            'password_set' => !empty($this->soapPassword),
            'php_soap_available' => class_exists('SoapClient'),
            'curl_available' => function_exists('curl_init'),
            'fsockopen_available' => function_exists('fsockopen')
        ];
    }
}
