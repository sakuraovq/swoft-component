<?php declare(strict_types=1);


namespace Swoft\Redis\Connection;

use Swoft\Bean\Annotation\Mapping\Bean;
use Swoft\Stdlib\Collection;

/**
 * Class PhpRedisConnection
 *
 * @since 2.0
 *
 * @Bean(scope=Bean::PROTOTYPE)
 */
class PhpRedisConnection extends Connection
{
    /**
     * @throws \ReflectionException
     * @throws \Swoft\Bean\Exception\ContainerException
     * @throws \Swoft\Redis\Exception\RedisException
     */
    public function createClient(): void
    {
        $config = [];
        $option = $this->redisDb->getOption();

        $this->client = $this->redisDb->getConnector()->connect($config, $option);
    }

    /**
     * @throws \ReflectionException
     * @throws \Swoft\Bean\Exception\ContainerException
     * @throws \Swoft\Redis\Exception\RedisException
     */
    public function createClusterClient(): void
    {
        $config = [];
        $option = $this->redisDb->getOption();

        $this->client = $this->redisDb->getConnector()->connectToCluster($config, $option);
    }

    /**
     * Returns the value of the given key.
     *
     * @param  string $key
     *
     * @return string|null
     */
    public function get(string $key): ?string
    {
        $result = $this->command('get', [$key]);

        return $result !== false ? $result : null;
    }

    /**
     * Get the values of all the given keys.
     *
     * @param  array $keys
     *
     * @return array
     */
    public function mget(array $keys): array
    {
        return array_map(function ($value) {
            return $value !== false ? $value : null;
        }, $this->command('mget', [$keys]));
    }

    /**
     * Determine if the given keys exist.
     *
     * @param  array $keys
     *
     * @return int
     * @throws \Swoft\Bean\Exception\PrototypeException
     */
    public function exists(...$keys): int
    {
        $keys = Collection::new($keys)->map(function ($key) {
            return $this->applyPrefix($key);
        })->all();

        return $this->executeRaw(array_merge(['exists'], $keys));
    }

    /**
     * Set the string value in argument as value of the key.
     *
     * @param  string      $key
     * @param  string      $value
     * @param  string|null $expireResolution
     * @param  int|null    $expireTTL
     * @param  string|null $flag
     *
     * @return bool
     */
    public function set(
        string $key,
        string $value,
        string $expireResolution = null,
        int $expireTTL = null,
        string $flag = null
    ): bool {
        return $this->command('set', [
            $key,
            $value,
            $expireResolution ? [$flag, $expireResolution => $expireTTL] : null,
        ]);
    }

    /**
     * Set the given key if it doesn't exist.
     *
     * @param  string $key
     * @param  string $value
     *
     * @return int
     */
    public function setnx(string $key, string $value): int
    {
        return (int)$this->command('setnx', [$key, $value]);
    }

    /**
     * Get the value of the given hash fields.
     *
     * @param  string $key
     * @param  array  $dictionary
     *
     * @return array
     */
    public function hmget(string $key, ...$dictionary): array
    {
        if (count($dictionary) === 1) {
            $dictionary = $dictionary[0];
        }

        return array_values($this->command('hmget', [$key, $dictionary]));
    }

    /**
     * Set the given hash fields to their respective values.
     *
     * @param  string $key
     * @param  array  $dictionary
     *
     * @return int|false
     * @throws \Swoft\Bean\Exception\PrototypeException
     */
    public function hmset(string $key, ...$dictionary)
    {
        if (count($dictionary) === 1) {
            $dictionary = $dictionary[0];
        } else {
            $input = Collection::new($dictionary);

            $dictionary = $input->nth(2)->combine($input->nth(2, 1))->toArray();
        }

        return $this->command('hmset', [$key, $dictionary]);
    }

    /**
     * Set the given hash field if it doesn't exist.
     *
     * @param  string $hash
     * @param  string $key
     * @param  string $value
     *
     * @return int
     */
    public function hsetnx(string $hash, string $key, string $value): int
    {
        return (int)$this->command('hsetnx', [$hash, $key, $value]);
    }

    /**
     * Removes the first count occurrences of the value element from the list.
     *
     * @param  string $key
     * @param  int    $count
     * @param  string $value
     *
     * @return int|false
     */
    public function lrem(string $key, int $count, string $value)
    {
        return $this->command('lrem', [$key, $value, $count]);
    }

    /**
     * Removes and returns the first element of the list stored at key.
     *
     * @param  array $arguments
     *
     * @return array
     */
    public function blpop(...$arguments): array
    {
        $result = $this->command('blpop', $arguments);

        return empty($result) ? [] : $result;
    }

    /**
     * Removes and returns the last element of the list stored at key.
     *
     * @param  array $arguments
     *
     * @return array
     */
    public function brpop(...$arguments): array
    {
        $result = $this->command('brpop', $arguments);

        return empty($result) ? [] : $result;
    }

    /**
     * Removes and returns a random element from the set value at key.
     *
     * @param  string   $key
     * @param  int|null $count
     *
     * @return mixed|false
     */
    public function spop(string $key, int $count = null)
    {
        return $this->command('spop', [$key]);
    }

    /**
     * Add one or more members to a sorted set or update its score if it already exists.
     *
     * @param  string $key
     * @param  array  $dictionary
     *
     * @return int
     */
    public function zadd(string $key, ...$dictionary): int
    {
        if (is_array(end($dictionary))) {
            foreach (array_pop($dictionary) as $member => $score) {
                $dictionary[] = $score;
                $dictionary[] = $member;
            }
        }

        $key = $this->applyPrefix($key);

        return $this->executeRaw(array_merge(['zadd', $key], $dictionary));
    }

    /**
     * Return elements with score between $min and $max.
     *
     * @param  string $key
     * @param  int    $min
     * @param  int    $max
     * @param  array  $options
     *
     * @return int
     */
    public function zrangebyscore(string $key, int $min, int $max, array $options = []): int
    {
        if (isset($options['limit'])) {
            $options['limit'] = [
                $options['limit']['offset'],
                $options['limit']['count'],
            ];
        }

        return $this->command('zRangeByScore', [$key, $min, $max, $options]);
    }

    /**
     * Return elements with score between $min and $max.
     *
     * @param  string $key
     * @param  int    $min
     * @param  int    $max
     * @param  array  $options
     *
     * @return int
     */
    public function zrevrangebyscore(string $key, int $min, int $max, array $options = []): int
    {
        if (isset($options['limit'])) {
            $options['limit'] = [
                $options['limit']['offset'],
                $options['limit']['count'],
            ];
        }

        return $this->command('zRevRangeByScore', [$key, $min, $max, $options]);
    }

    /**
     * Find the intersection between sets and store in a new set.
     *
     * @param  string $output
     * @param  array  $keys
     * @param  array  $options
     *
     * @return int
     */
    public function zinterstore(string $output, array $keys, array $options = []): int
    {
        return $this->command('zInter', [
            $output,
            $keys,
            $options['weights'] ?? null,
            $options['aggregate'] ?? 'sum',
        ]);
    }

    /**
     * Find the union between sets and store in a new set.
     *
     * @param  string $output
     * @param  array  $keys
     * @param  array  $options
     *
     * @return int
     */
    public function zunionstore(string $output, array $keys, array $options = []): int
    {
        return $this->command('zUnion', [
            $output,
            $keys,
            $options['weights'] ?? null,
            $options['aggregate'] ?? 'sum',
        ]);
    }

    /**
     * Execute commands in a pipeline.
     *
     * @param  callable $callback
     *
     * @return \Redis|array
     */
    public function pipeline(callable $callback = null)
    {
        $pipeline = $this->client()->pipeline();

        return is_null($callback)
            ? $pipeline
            : tap($pipeline, $callback)->exec();
    }

    /**
     * Execute commands in a transaction.
     *
     * @param  callable $callback
     *
     * @return \Redis|array
     */
    public function transaction(callable $callback = null)
    {
        $transaction = $this->client()->multi();

        return is_null($callback)
            ? $transaction
            : tap($transaction, $callback)->exec();
    }

    /**
     * Evaluate a LUA script serverside, from the SHA1 hash of the script instead of the script itself.
     *
     * @param  string $script
     * @param  int    $numkeys
     * @param  mixed  $arguments
     *
     * @return mixed
     */
    public function evalsha($script, $numkeys, ...$arguments)
    {
        return $this->command('evalsha', [
            $this->script('load', $script),
            $arguments,
            $numkeys,
        ]);
    }

    /**
     * Evaluate a script and return its result.
     *
     * @param  string $script
     * @param  int    $numberOfKeys
     * @param  array  $arguments
     *
     * @return mixed
     */
    public function eval($script, $numberOfKeys, ...$arguments)
    {
        return $this->command('eval', [$script, $arguments, $numberOfKeys]);
    }

    /**
     * Subscribe to a set of given channels for messages.
     *
     * @param  array|string $channels
     * @param  \Closure     $callback
     *
     * @return void
     */
    public function subscribe($channels, \Closure $callback)
    {
        $this->client->subscribe((array)$channels, function ($redis, $channel, $message) use ($callback) {
            $callback($message, $channel);
        });
    }

    /**
     * Subscribe to a set of given channels with wildcards.
     *
     * @param  array|string $channels
     * @param  \Closure     $callback
     *
     * @return void
     */
    public function psubscribe($channels, \Closure $callback)
    {
        $this->client->psubscribe((array)$channels, function ($redis, $pattern, $channel, $message) use ($callback) {
            $callback($message, $channel);
        });
    }

    /**
     * Subscribe to a set of given channels for messages.
     *
     * @param  array|string $channels
     * @param  \Closure     $callback
     * @param  string       $method
     *
     * @return void
     */
    public function createSubscription($channels, \Closure $callback, $method = 'subscribe')
    {
        //
    }

    /**
     * Execute a raw command.
     *
     * @param  array $parameters
     *
     * @return mixed
     */
    public function executeRaw(array $parameters)
    {
        return $this->command('rawCommand', $parameters);
    }

    /**
     * Apply prefix to the given key if necessary.
     *
     * @param  string $key
     *
     * @return string
     */
    private function applyPrefix(string $key): string
    {
        $prefix = (string)$this->client->getOption(\Redis::OPT_PREFIX);

        return $prefix . $key;
    }

    /**
     * Pass other method calls down to the underlying client.
     *
     * @param  string $method
     * @param  array  $parameters
     *
     * @return mixed
     */
    public function __call(string $method, array $parameters)
    {
        return parent::__call(strtolower($method), $parameters);
    }
}