<?php

namespace Untek\Core\Code\Helpers;

use Laminas\Code\Generator\MethodGenerator;
use Laminas\Code\Generator\ParameterGenerator;
use Laminas\Code\Generator\ValueGenerator;
use Untek\Core\Text\Helpers\StringHelper;
use Untek\Core\Text\Helpers\TextHelper;
use Untek\Tool\Package\Domain\Libs\Deps\PhpUsesParser;

/**
 * Работа с замыканиями
 */
class ClosureHelper
{

    public static function unserialize($serialized): \Closure
    {
        $closure = null;
        eval("\$closure = $serialized;");
        return $closure;
    }

    public static function serialize($concrete): string
    {
        $reflector = new \ReflectionFunction($concrete);
        $dir = dirname($reflector->getFileName());

        $code = file_get_contents($reflector->getFileName());

        $usesParser = new PhpUsesParser();
        $uses = $usesParser->parse($code);

        $tokens = token_get_all($code);
        $newCode = '';
        foreach ($tokens as $token) {
            $tokenId = $token[0];
            if(is_array($token) && $tokenId == 345) {
                $vv = new ValueGenerator();
                $vv->setValue($dir);
                $token[1] = $vv->generate();
            }
            if(is_array($token) && $tokenId == 262) {
                $tokenCode = $token[1];
                if(!empty($uses[$tokenCode])) {
                    $token[1] = '\\' . $uses[$tokenCode];
                }
            }

            if(is_string($token)) {
                $newCode .= $token;
            } else {
                $newCode .= $token[1];
            }
        }

        $codeArr = explode(PHP_EOL, $newCode);

        $sliced = array_slice($codeArr, $reflector->getStartLine(), $reflector->getEndLine() - $reflector->getStartLine() - 1);
        $code = implode(PHP_EOL, $sliced);

        $mg = new MethodGenerator(null, [], []);
        foreach ($reflector->getParameters() as $parameter) {
            $pg = new ParameterGenerator();
            $pg->setName($parameter->getName());
            $pg->setType($parameter && $parameter->getType() ? $parameter->getType()->getName() : 'string');
            $mg->setParameter($pg);
        }
        $mg->setBody($code);
        $closureCode = $mg->generate();
        $closureCode = trim($closureCode);
        $closureCode = mb_substr($closureCode, 6);
        $closureCode = trim($closureCode);
        return $closureCode;
    }
}
