<?php

namespace Untek\Core\Code\Helpers;

use Untek\Core\Code\Entities\PhpTokenEntity;
use Untek\Core\Collection\Interfaces\Enumerable;
use Untek\Core\Collection\Libs\Collection;

class PhpTokenHelper
{

    /**
     * @param string $code
     * @return Enumerable | PhpTokenEntity[]
     */
    public static function getTokens(string $code): Enumerable
    {
        $collection = new Collection();
        $tokens = token_get_all($code, TOKEN_PARSE);
        foreach ($tokens as &$token) {
            $tokenTypeId = is_array($token) ? $token[0] : null;
            $tokenCode = is_array($token) ? $token[1] : $token;
            $tokenEntity = new PhpTokenEntity();
            $tokenEntity->setId($tokenTypeId);
            $tokenEntity->setName(token_name($tokenTypeId));
            $tokenEntity->setData($tokenCode);
            $collection->add($tokenEntity);
        }
        return $collection;
    }
}
