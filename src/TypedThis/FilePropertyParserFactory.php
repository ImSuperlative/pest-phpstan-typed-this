<?php

declare(strict_types=1);

namespace ImSuperlative\PhpstanPest\TypedThis;

use ImSuperlative\PhpstanPest\Parser\AssignmentInferenceParser;
use ImSuperlative\PhpstanPest\Parser\PestPropertyTagParser;
use ImSuperlative\PhpstanPest\Parser\PhpDocPropertyParser;
use PhpParser\NodeFinder;
use PhpParser\Parser;

final readonly class FilePropertyParserFactory
{
    public function __construct(
        private PestPropertyTagParser $pestPropertyTagParser,
        private PhpDocPropertyParser $phpDocPropertyParser,
        private AssignmentInferenceParser $assignmentParser,
        private Parser $parser,
        private NodeFinder $nodeFinder,
        private UsesParser $usesParser,
        private bool $parsePestPropertyTags = false,
        private bool $parsePhpDocProperties = false,
        private bool $parseAssignments = true,
    ) {}

    public function create(): FilePropertyParser
    {
        return new FilePropertyParser(
            array_filter([
                $this->parsePestPropertyTags ? $this->pestPropertyTagParser : null,
                $this->parsePhpDocProperties ? $this->phpDocPropertyParser : null,
                $this->parseAssignments ? $this->assignmentParser : null,
            ]),
            $this->parser,
            $this->nodeFinder,
            $this->usesParser,
        );
    }
}
