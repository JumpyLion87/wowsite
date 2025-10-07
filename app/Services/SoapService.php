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
     * Создать SOAP клиент для удаленного сервера
     */
    protected function createSoapClient()
    {
        if ($this->soapClient) {
            return $this->soapClient;
        }

        try {
            // Согласно документации AzerothCore, используем правильный формат URL
            $soapUrl = $this->soapUrl;
            
            // Если URL не содержит протокол, добавляем http://
            if (!preg_match('/^https?:\/\//', $soapUrl)) {
                $soapUrl = 'http://' . $soapUrl;
            }
            
            // Создаем SOAP клиент с правильными параметрами для удаленного сервера
            $this->soapClient = new \SoapClient(null, [
                'location' => $soapUrl,
                'uri' => 'urn:AC',
                'style' => SOAP_RPC,
                'login' => $this->soapUsername,
                'password' => $this->soapPassword,
                'trace' => true,
                'exceptions' => true,
                'connection_timeout' => 15,
                'cache_wsdl' => WSDL_CACHE_NONE,
                'stream_context' => stream_context_create([
                    'http' => [
                        'timeout' => 15,
                        'user_agent' => 'AzerothCore-SOAP-Client/1.0'
                    ]
                ])
            ]);

            return $this->soapClient;
        } catch (\Exception $e) {
            Log::error('SOAP client creation failed: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Выполнить GM команду через SOAP
     */
    public function executeCommand($command, $params = [])
    {
        try {
            $soapClient = $this->createSoapClient();
            
            // Форматируем команду с параметрами
            $formattedCommand = $this->formatCommand($command, $params);
            
            // Согласно документации AzerothCore, используем правильный формат вызова
            $result = $soapClient->executeCommand(new \SoapParam($formattedCommand, 'command'));

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

            // Если SOAP доступен, но есть ошибка аутентификации
            if ($connectionTest['has_soap_error']) {
                return [
                    'status' => 'partial',
                    'message' => 'SOAP сервер доступен, но требуется аутентификация',
                    'response' => $connectionTest['soap_response'],
                    'diagnostics' => $connectionTest,
                    'note' => 'Проверьте правильность логина и пароля GM аккаунта'
                ];
            }

            // Если все хорошо, пробуем выполнить команду
            try {
                $result = $this->getServerInfo();
                return [
                    'status' => 'success',
                    'message' => 'SOAP соединение работает корректно',
                    'response' => $result,
                    'diagnostics' => $connectionTest
                ];
            } catch (\Exception $e) {
                return [
                    'status' => 'auth_error',
                    'message' => 'SOAP сервер доступен, но аутентификация не удалась: ' . $e->getMessage(),
                    'response' => $connectionTest['soap_response'],
                    'diagnostics' => $connectionTest,
                    'note' => 'Проверьте правильность логина и пароля GM аккаунта'
                ];
            }

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
     * Тестировать соединение с удаленным сервером
     */
    protected function testServerConnection()
    {
        $url = parse_url($this->soapUrl);
        $host = $url['host'] ?? 'localhost';
        $port = $url['port'] ?? 7878;
        
        // Проверяем доступность порта на удаленном сервере
        $connection = @fsockopen($host, $port, $errno, $errstr, 10);
        if (!$connection) {
            return [
                'success' => false,
                'message' => "Не удается подключиться к удаленному серверу {$host}:{$port}",
                'error' => "{$errno}: {$errstr}",
                'host' => $host,
                'port' => $port,
                'suggestion' => "Проверьте, что сервер запущен и порт {$port} открыт на удаленной машине"
            ];
        }
        
        fclose($connection);
        
        // Проверяем SOAP сервис через POST запрос (SOAP не поддерживает GET)
        $soapUrl = "http://{$host}:{$port}";
        $soapRequest = $this->createTestSoapRequest();
        
        $context = stream_context_create([
            'http' => [
                'timeout' => 10,
                'method' => 'POST',
                'header' => [
                    'Content-Type: application/xml',
                    'SOAPAction: "urn:AC#executeCommand"',
                    'User-Agent: AzerothCore-SOAP-Client/1.0'
                ],
                'content' => $soapRequest
            ]
        ]);
        
        $response = @file_get_contents($soapUrl, false, $context);
        if ($response === false) {
            return [
                'success' => false,
                'message' => "SOAP сервис на {$soapUrl} недоступен",
                'host' => $host,
                'port' => $port,
                'suggestion' => "Убедитесь, что SOAP включен в worldserver.conf (SOAP.Enabled = 1)"
            ];
        }
        
        // Проверяем, что это валидный SOAP ответ
        $isValidSoap = strpos($response, 'SOAP-ENV:Envelope') !== false;
        $hasError = strpos($response, 'SOAP-ENV:Fault') !== false;
        
        return [
            'success' => true,
            'message' => "Удаленный сервер {$host}:{$port} доступен",
            'host' => $host,
            'port' => $port,
            'soap_response' => substr($response, 0, 500),
            'soap_available' => $isValidSoap,
            'has_soap_error' => $hasError,
            'note' => $hasError ? "SOAP сервер работает, но требует аутентификации" : "SOAP сервер полностью доступен"
        ];
    }

    /**
     * Создать тестовый SOAP запрос для проверки соединения
     */
    protected function createTestSoapRequest()
    {
        return '<?xml version="1.0" encoding="UTF-8"?>
<SOAP-ENV:Envelope 
    xmlns:SOAP-ENV="http://schemas.xmlsoap.org/soap/envelope/" 
    xmlns:SOAP-ENC="http://schemas.xmlsoap.org/soap/encoding/" 
    xmlns:xsi="http://www.w3.org/1999/XMLSchema-instance" 
    xmlns:xsd="http://www.w3.org/1999/XMLSchema" 
    xmlns:ns1="urn:AC">
    <SOAP-ENV:Body>
        <ns1:executeCommand>
            <command>server info</command>
        </ns1:executeCommand>
    </SOAP-ENV:Body>
</SOAP-ENV:Envelope>';
    }

    /**
     * Получить диагностическую информацию для удаленного сервера
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
            'fsockopen_available' => function_exists('fsockopen'),
            'remote_server' => $host !== 'localhost' && $host !== '127.0.0.1',
            'connection_timeout' => 15,
            'suggestions' => [
                'Убедитесь, что на удаленном сервере включен SOAP (SOAP.Enabled = 1)',
                'Проверьте, что порт 7878 открыт на удаленной машине',
                'Убедитесь, что GM аккаунт имеет уровень 3 в account_access с RealmID = -1',
                'Проверьте правильность IP адреса и порта в настройках'
            ]
        ];
    }
}
