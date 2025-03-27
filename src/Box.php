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
    private Configuration $config;
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

        $this->config = $configuration ?? new Configuration();

        if (isset($authToken)) {
            $this->authManager->setAuthToken($authToken);
        }

        $this->logger = $this->config->logger;
        $this->logger->debug('FreeBoxPHP Initializing');

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

        $this->container = $this->getConfiguredContainer();

        $this->logger->debug('FreeBoxPHP Initialization done');
    }

    private function getConfiguredContainer(): Container
    {
        $container = new Container();
        if (isset($this->config->container) && is_a($this->config->container, ContainerInterface::class)) {
            $container->delegate($this->config->container);
        }
        $container->delegate(new ReflectionContainer(true));
        $container->add(Configuration::class, $this->config);
        $container->add(LoggerInterface::class, $this->logger);
        $container->add(CacheInterface::class, $this->cache);
        $container->add(ClientInterface::class, $this->client);
        $container->add(HttpClientInterface::class, HttpClient::class)
            ->addArgument(ClientInterface::class)
            ->addArgument(LoggerInterface::class);
        $container->add(BoxInfoInterface::class, $this->boxInfo);
        $container->add(AuthManagerInterface::class, $this->authManager);
        $container->add(AuthSessionInterface::class, AuthSession::class)
            ->addArgument(AuthManagerInterface::class)
            ->addArgument(BoxInfoInterface::class)
            ->addArgument(Configuration::class)
            ->addArgument(HttpClientInterface::class)
            ->addArgument(LoggerInterface::class)
            ->addArgument(CacheInterface::class);

        return $container;
    }

    /**
     * @return array<string, bool|int|string>
     */
    public function getBoxInfo(): array
    {
        return $this->boxInfo->getInfo();
    }

    /**
     * @param array<string, bool|int|string> $params
     *
     * @return array<string, mixed>|BoxInterface
     */
    public function connectionConfiguration(string $action = 'get', array $params = []): array|BoxInterface
    {
        return $this->runMethod(__FUNCTION__, $action, null, $params);
    }

    /**
     * @param array<string, bool|int|string> $params
     *
     * @return array<string, mixed>|BoxInterface
     */
    public function connectionIPv6Configuration(string $action = 'get', array $params = []): array|BoxInterface
    {
        return $this->runMethod(__FUNCTION__, $action, null, $params);
    }

    /**
     * @return array<string, mixed>|BoxInterface
     */
    public function connectionStatus(): array|BoxInterface
    {
        return $this->runMethod(__FUNCTION__);
    }

    public function discover(): BoxInterface
    {
        return $this->isInterfaceInstance(
            $this->runMethod(__FUNCTION__),
        );
    }

    /**
     * @param array<string, bool|int|string> $params
     *
     * @return array<string, mixed>|BoxInterface
     */
    public function fwRedir(string $action = 'get', null|int|string $id = null, array $params = []): array|BoxInterface
    {
        return $this->runMethod(__FUNCTION__, $action, $id, $params);
    }

    /**
     * @return array<string, mixed>|BoxInterface
     */
    public function lanBrowserInterfaces(): array|BoxInterface
    {
        return $this->runMethod(__FUNCTION__);
    }

    /**
     * @param array<string, string> $params
     *
     * @return array<string, mixed>|BoxInterface
     */
    public function language(string $action = 'get', array $params = []): array|BoxInterface
    {
        return $this->runMethod(__FUNCTION__, $action, null, $params);
    }

    /**
     * @param array<string, string> $params
     */
    public function lanWol(string $id, array $params): BoxInterface
    {
        return $this->isInterfaceInstance(
            $this->runMethod(__FUNCTION__, 'set', $id, $params),
        );
    }

    public function login(): BoxInterface
    {
        return $this->isInterfaceInstance(
            $this->runMethod(__FUNCTION__),
        );
    }

    public function logout(): BoxInterface
    {
        return $this->isInterfaceInstance(
            $this->runMethod(__FUNCTION__),
        );
    }

    public function register(bool $quiet = true, bool $skipSleep = false): string
    {
        $fullName = $this->getFullMethod(__FUNCTION__);

        $this->logger->info('FreeBoxPHP Calling method', ['name' => __FUNCTION__, 'quiet' => $quiet, 'skipSleep' => $skipSleep]);
        return $this->container->get($fullName)->run($quiet, $skipSleep);
    }

    /**
     * @param array<string, bool|int|string> $params
     *
     * @return ($name is 'register' ? string : array<string, mixed>|BoxInterface)
     */
    private function runMethod(string $name, string $action = 'get', null|int|string $id = null, array $params = []): array|BoxInterface|string
    {
        $this->logger->info('FreeBoxPHP Calling method', compact('name', 'action', 'id', 'params'));

        return $this->container->get($this->getFullMethod($name))->run($action, $id, $params) ?? $this;
    }

    private function getFullMethod(string $method): string
    {
        return 'madpilot78\\FreeBoxPHP\\Methods\\' . ucfirst($method);
    }

    /**
     * @param array<string, mixed>|BoxInterface $ent
     */
    private function isInterfaceInstance(array|BoxInterface $ent): BoxInterface
    {
        if (!($ent instanceof BoxInterface)) {
            $err = 'Unexpected object type returned';
            $this->logger->critical($err);
            throw new \RuntimeException($err, 42);
        }

        return $ent;
    }
}
