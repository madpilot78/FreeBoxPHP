<?php

declare(strict_types=1);

namespace madpilot78\FreeBoxPHP;

use GuzzleHttp\Client as Guzzle;
use GuzzleHttp\ClientInterface;
use League\Container\Container;
use League\Container\ReflectionContainer;
use Psr\Container\ContainerInterface;
use Psr\Log\LoggerInterface;
use Psr\SimpleCache\CacheInterface;
use madpilot78\FreeBoxPHP\Auth\Manager as AuthManager;
use madpilot78\FreeBoxPHP\Auth\ManagerInterface as AuthManagerInterface;
use madpilot78\FreeBoxPHP\Auth\Session as AuthSession;
use madpilot78\FreeBoxPHP\Auth\SessionInterface as AuthSessionInterface;

class Box implements BoxInterface
{
    private AuthManagerInterface $authManager;
    private BoxInfoInterface $boxInfo;
    private ?Configuration $config;
    private Container $container;
    private LoggerInterface $logger;
    private CacheInterface $cache;
    private ?ClientInterface $client;

    public function __construct(
        ?string $authToken = null,
        ?Configuration $configuration = null,
        ?ClientInterface $client = null,
    ) {
        $this->authManager = new AuthManager();

        if (isset($authToken)) {
            $this->authManager->setAuthToken($authToken);
        }

        $this->config = $configuration;
        if (is_null($this->config)) {
            $this->config = new Configuration();
        }

        $this->logger = $this->config->logger;
        $this->logger->debug('FreeBoxPHP Intializing');

        $this->cache = $this->config->cache;

        $this->client = $client;
        if (is_null($this->client)) {
            $this->client = new Guzzle([
                'timeout' => $this->config->timeout,
                'verify' => $this->config->certFile ?? true,
                'version' => 2.0,
            ]);
        }

        $this->boxInfo = new BoxInfo($this->config);

        $this->container = new Container();
        $this->container->delegate(new ReflectionContainer(true));
        if (is_a($this->config->container, ContainerInterface::class)) {
            $this->container->delegate($this->config->container);
        }
        $this->container->add(Configuration::class, $this->config);
        $this->container->add(LoggerInterface::class, $this->logger);
        $this->container->add(CacheInterface::class, $this->cache);
        $this->container->add(ClientInterface::class, $this->client);
        $this->container->add(HttpClientInterface::class, HttpClient::class)
            ->addArgument(ClientInterface::class)
            ->addArgument(LoggerInterface::class);
        $this->container->add(BoxInfoInterface::class, $this->boxInfo);
        $this->container->add(AuthManagerInterface::class, $this->authManager);
        $this->container->add(AuthSessionInterface::class, AuthSession::class)
            ->addArgument(AuthManagerInterface::class)
            ->addArgument(BoxInfoInterface::class)
            ->addArgument(Configuration::class)
            ->addArgument(HttpClientInterface::class)
            ->addArgument(LoggerInterface::class)
            ->addArgument(CacheInterface::class);

        $this->logger->debug('FreeBoxPHP Initialization done');
    }

    public function getBoxInfo(): array
    {
        return $this->boxInfo->getInfo();
    }

    public function connectionConfiguration(string $action = 'get', array|int|string $id = [], array $params = []): array|BoxInterface
    {
        return $this->runMethod(__FUNCTION__, $action, $id, $params);
    }

    public function connectionIPv6Configuration(string $action = 'get', array|int|string $id = [], array $params = []): array|BoxInterface
    {
        return $this->runMethod(__FUNCTION__, $action, $id, $params);
    }

    public function connectionStatus(string $action = 'get', array|int|string $id = [], array $params = []): array
    {
        return $this->runMethod(__FUNCTION__, $action, $id, $params);
    }

    public function discover(string $action = 'get', array|int|string $id = [], array $params = []): BoxInterface
    {
        return $this->runMethod(__FUNCTION__, $action, $id, $params);
    }

    public function fwRedir(string $action = 'get', array|int|string $id = [], array $params = []): array|BoxInterface
    {
        return $this->runMethod(__FUNCTION__, $action, $id, $params);
    }

    public function lanBrowserInterfaces(string $action = 'get', array|int|string $id = [], array $params = []): array
    {
        return $this->runMethod(__FUNCTION__, $action, $id, $params);
    }

    public function language(string $action = 'get', array|int|string $id = [], array $params = []): array|BoxInterface
    {
        return $this->runMethod(__FUNCTION__, $action, $id, $params);
    }

    public function lanWol(string $action = 'get', array|int|string $id = [], array $params = []): BoxInterface
    {
        return $this->runMethod(__FUNCTION__, $action, $id, $params);
    }

    public function login(string $action = 'get', array|int|string $id = [], array $params = []): BoxInterface
    {
        return $this->runMethod(__FUNCTION__, $action, $id, $params);
    }

    public function logout(string $action = 'get', array|int|string $id = [], array $params = []): BoxInterface
    {
        return $this->runMethod(__FUNCTION__, $action, $id, $params);
    }

    public function register(bool $quiet = true, bool $skipSleep = false): string
    {
        $fullName = $this->getFullMethod(__FUNCTION__);

        $this->logger->info('FreeBoxPHP Calling method', ['name' => __FUNCTION__, 'quiet' => $quiet, 'skipSleep' => $skipSleep]);
        return $this->container->get($fullName)->run($quiet, $skipSleep);
    }

    private function runMethod(string $name, string $action = 'get', array|int|string $id = [], array $params = []): array|BoxInterface|string
    {
        $this->logger->info('FreeBoxPHP Calling method', compact('name', 'action', 'id', 'params'));

        return $this->container->get($this->getFullMethod($name))->run($action, $id, $params) ?? $this;
    }

    private function getFullMethod(string $method): string
    {
        return 'madpilot78\\FreeBoxPHP\\Methods\\' . ucfirst($method);
    }
}
