<?php
/**
 * This file is part of Swoft.
 *
 * @link     https://swoft.org
 * @document https://doc.swoft.org
 * @contact  group@swoft.org
 * @license  https://github.com/swoft-cloud/swoft/blob/master/LICENSE
 */
namespace Swoft\Rpc\Packer\Json;

use Swoft\Bean\Annotation\Bean;
use Swoft\Helper\JsonHelper;
use Swoft\Rpc\Packer\EofTrait;
use Swoft\Rpc\Packer\PackerInterface;

/**
 * Class JsonPacker
 * @Bean
 */
class JsonPacker implements PackerInterface
{
    use EofTrait;

    /**
     * Pack data
     *
     * @param mixed $data
     * @return string
     * @throws \InvalidArgumentException
     */
    public function pack($data): string
    {
        return JsonHelper::encode($data, JSON_UNESCAPED_UNICODE) . $this->getEof();
    }

    /**
     * Unpack data
     *
     * @param mixed $data
     * @return mixed
     * @throws \InvalidArgumentException
     */
    public function unpack($data)
    {
        return JsonHelper::decode($data, true);
    }
}
