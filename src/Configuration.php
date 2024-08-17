<?php

declare(strict_types=1);

namespace madpilot78\FreeBoxPHP;

use chillerlan\SimpleCache\MemoryCache;
use Psr\Container\ContainerInterface;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;
use madpilot78\FreeBoxPHP\Enum\BoxType;
use Psr\SimpleCache\CacheInterface;

final readonly class Configuration
{
    public const string VERSION = '0.0.1';

    public const string DEFAULT_APPID = 'net.madpilot.freeboxphp';
    public const string DEFAULT_APPNAME = 'FreeBoxPHP';
    public const string DEFAULT_HOSTNAME = 'mafreebox.freebox.fr';
    public const string DEFAULT_DEVICENAME = 'Unknwon';
    public const bool DEFAULT_HTTPS = true;
    public const bool DEFAULT_LOCAL_ACCESS = true;
    public const BoxType DEFAULT_BOX_TYPE = BoxType::Free;
    public const int DEFAULT_TIMEOUT = 30;
    public const string CERT_PATH = '/data';

    public ?string $certFile;
    public string $deviceName;
    public CacheInterface $cache;

    public function __construct(
        public string $appId = self::DEFAULT_APPID,
        public string $appName = self::DEFAULT_APPNAME,
        public string $hostname = self::DEFAULT_HOSTNAME,
        public bool $https = self::DEFAULT_HTTPS,
        public bool $localAccess = self::DEFAULT_LOCAL_ACCESS,
        public BoxType $boxType = self::DEFAULT_BOX_TYPE,
        public LoggerInterface $logger = new NullLogger(),
        public ?ContainerInterface $container = null,
        public int $timeout = self::DEFAULT_TIMEOUT,
        ?CacheInterface $cache = null,
        string $deviceName = self::DEFAULT_DEVICENAME,
        ?string $certFile = '',
    ) {
        $this->certFile = $certFile  === ''
            ? realpath(__DIR__ . '/../' . self::CERT_PATH . '/' . $this->boxType->value . '.pem')
            : $certFile;

        $machineHostname = gethostname();
        $this->deviceName = $deviceName === self::DEFAULT_DEVICENAME && strlen($machineHostname)
            ? $machineHostname
            : $deviceName;

        $this->cache = is_null($cache)
            ? new MemoryCache(logger: $this->logger)
            : $cache;
    }

    public function isDefaulthostname(): bool
    {
        return $this->hostname === self::DEFAULT_HOSTNAME;
    }
}
