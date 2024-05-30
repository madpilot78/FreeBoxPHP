<?php

declare(strict_types=1);

namespace madpilot78\FreeBoxPHP;

use GuzzleHttp\Client as Guzzle;
use Psr\Http\Client\ClientInterface;
use BadMethodCallException;
use League\Container\Container;
use League\Container\ReflectionContainer;
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
        $this->container->add(ClientInterface::class, $this->client);
        $this->container->add(HttpClient::class)
            ->addArgument(ClientInterface::class);
        $this->container->add(Configuration::class, $this->config);
        $this->container->add(BoxInfoInterface::class, $this->boxInfo);
        $this->container->add(AuthManagerInterface::class, $this->authManager);
        $this->container->add(AuthSessionInterface::class, AuthSession::class)
            ->addArgument(AuthManagerInterface::class)
            ->addArgument(BoxInfoInterface::class)
            ->addArgument(Configuration::class)
            ->addArgument(HttpClient::class);
    }

    public function getBoxInfo(): array
    {
        return $this->boxInfo->getInfo();
    }

    public function __call(string $name, array $arguments): mixed
    {
        $fullName = self::METHODS_BASE . ucfirst($name);

        if (class_exists($fullName)) {
            $ret = $this->container->get($fullName)->run(...$arguments);
        } else {
            throw new BadMethodCallException('Method ' . $name . ' Not found');
        }

        return $ret ?? $this;
    }
}
