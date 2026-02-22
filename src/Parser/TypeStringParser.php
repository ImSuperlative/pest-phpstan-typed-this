<?php

declare(strict_types=1);

namespace ImSuperlative\PestPhpstanTypedThis\Parser;

use PHPStan\PhpDocParser\Ast\Type\TypeNode;
use PHPStan\PhpDocParser\Lexer\Lexer;
use PHPStan\PhpDocParser\Parser\ConstExprParser;
use PHPStan\PhpDocParser\Parser\ParserException;
use PHPStan\PhpDocParser\Parser\TokenIterator;
use PHPStan\PhpDocParser\Parser\TypeParser;
use PHPStan\PhpDocParser\ParserConfig;

final class TypeStringParser
{
    private Lexer $lexer;

    private TypeParser $typeParser;

    public function __construct()
    {
        $config = new ParserConfig([]);

        $this->lexer = new Lexer($config);
        $this->typeParser = new TypeParser($config, new ConstExprParser($config));
    }

    public function getLexer(): Lexer
    {
        return $this->lexer;
    }

    public function getTypeParser(): TypeParser
    {
        return $this->typeParser;
    }

    public function parseTypeString(string $typeString): ?TypeNode
    {
        $tokens = new TokenIterator($this->lexer->tokenize($typeString));

        try {
            return $this->typeParser->parse($tokens);
        } catch (ParserException) {
            return null;
        }
    }
}
