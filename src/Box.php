<?php

declare(strict_types=1);

namespace madpilot78\FreeBoxPHP;

use BadMethodCallException;
use GuzzleHttp\Client as Guzzle;
use League\Container\Container;
use League\Container\ReflectionContainer;
use Psr\Http\Client\ClientInterface;
use Psr\Log\LoggerInterface;
use madpilot78\FreeBoxPHP\Auth\Manager as AuthManager;
use madpilot78\FreeBoxPHP\Auth\ManagerInterface as AuthManagerInterface;
use madpilot78\FreeBoxPHP\Auth\Session as AuthSession;
use madpilot78\FreeBoxPHP\Auth\SessionInterface as AuthSessionInterface;

class Box
{
    private const string METHODS_BASE = 'madpilot78\\FreeBoxPHP\\Methods\\';

    private AuthManagerInterface $authManager;
    private BoxInfoInterface $boxInfo;
    private ?Configuration $config;
    private Container $container;
    private LoggerInterface $logger;
    private ?Guzzle $client;

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
        $this->container->add(Configuration::class, $this->config);
        $this->container->add(LoggerInterface::class, $this->logger);
        $this->container->add(ClientInterface::class, $this->client);
        $this->container->add(HttpClient::class)
            ->addArgument(ClientInterface::class)
            ->addArgument(LoggerInterface::class);
        $this->container->add(BoxInfoInterface::class, $this->boxInfo);
        $this->container->add(AuthManagerInterface::class, $this->authManager);
        $this->container->add(AuthSessionInterface::class, AuthSession::class)
            ->addArgument(AuthManagerInterface::class)
            ->addArgument(BoxInfoInterface::class)
            ->addArgument(Configuration::class)
            ->addArgument(HttpClient::class)
            ->addArgument(LoggerInterface::class);

        $this->logger->debug('FreeBoxPHP Initialization done');
    }

    public function getBoxInfo(): array
    {
        return $this->boxInfo->getInfo();
    }

    public function __call(string $name, array $arguments): mixed
    {
        $fullName = self::METHODS_BASE . ucfirst($name);

        if (class_exists($fullName)) {
            $this->logger->info('FreeBoxPHP Calling method', compact('name', 'arguments'));
            $ret = $this->container->get($fullName)->run(...$arguments);
        } else {
            $this->logger->error('FreeBoxPHP Method not found', compact('name', 'arguments'));
            throw new BadMethodCallException('Method ' . $name . ' not found');
        }

        return $ret ?? $this;
    }
}
